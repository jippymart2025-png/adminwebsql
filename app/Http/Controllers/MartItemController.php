<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Google\Cloud\Firestore\FirestoreClient;

/**
 * MartItemController
 *
 * Handles CRUD operations for mart items.
 *
 * Default Fields for New Items:
 * - reviewCount: "0" (string) - Number of reviews
 * - reviewSum: "0" (string) - Sum of review ratings
 * - These fields are automatically set to "0" for all new items
 */
class MartItemController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id='')
    {
        return view("martItems.index")->with('id',$id);
    }

    public function edit($id)
    {
        return view('martItems.edit')->with('id',$id);
    }

    public function create($id='')
    {
        return view('martItems.create')->with('id',$id);
    }

    public function createItem()
    {
        return view('martItems.create');
    }

    /**
     * Find vendor ID by name (for mart vendors only)
     */
    private function findVendorByName($vendorName, $firestore)
    {
        try {
            $vendors = $firestore->collection('vendors')
                ->where('title', '==', trim($vendorName))
                ->where('vType', '==', 'mart')
                ->limit(1)
                ->documents();

            foreach ($vendors as $vendor) {
                return $vendor->id();
            }
        } catch (\Exception $e) {
            // Log error if needed
        }
        return null;
    }

    /**
     * Find category ID by name
     */
    private function findCategoryByName($categoryName, $firestore)
    {
        try {
            $categories = $firestore->collection('mart_categories')
                ->where('title', '==', trim($categoryName))
                ->limit(1)
                ->documents();

            foreach ($categories as $category) {
                return $category->id();
            }
        } catch (\Exception $e) {
            // Log error if needed
        }
        return null;
    }

    /**
     * Find subcategory ID by name
     */
    private function findSubcategoryByName($subcategoryName, $firestore)
    {
        try {
            $subcategories = $firestore->collection('mart_subcategories')
                ->where('title', '==', trim($subcategoryName))
                ->limit(1)
                ->documents();

            foreach ($subcategories as $subcategory) {
                return $subcategory->id();
            }
        } catch (\Exception $e) {
            // Log error if needed
        }
        return null;
    }

    /**
     * Resolve subcategory ID - try direct ID first, then name lookup
     */
    private function resolveSubcategoryID($subcategoryInput, $firestore)
    {
        if (empty($subcategoryInput)) {
            return '';
        }

        // First try as direct ID
        try {
            $subcategoryDoc = $firestore->collection('mart_subcategories')->document($subcategoryInput)->snapshot();
            if ($subcategoryDoc->exists()) {
                return $subcategoryInput; // Return the ID as-is
            }
        } catch (\Exception $e) {
            // Continue to name lookup
        }

        // If not found as ID, try name lookup
        return $this->findSubcategoryByName($subcategoryInput, $firestore) ?: '';
    }

    /**
     * Get section from subcategory
     */
    private function getSectionFromSubcategory($subcategoryID, $firestore)
    {
        if (empty($subcategoryID)) {
            return 'General';
        }

        try {
            $subcategoryDoc = $firestore->collection('mart_subcategories')->document($subcategoryID)->snapshot();
            if ($subcategoryDoc->exists()) {
                $subcategoryData = $subcategoryDoc->data();
                if (isset($subcategoryData['parent_category_id'])) {
                    $categoryDoc = $firestore->collection('mart_categories')->document($subcategoryData['parent_category_id'])->snapshot();
                    if ($categoryDoc->exists()) {
                        $categoryData = $categoryDoc->data();
                        return $categoryData['section'] ?? 'General';
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error if needed
        }
        return 'General';
    }

    /**
     * Resolve vendor ID - try direct ID first, then name lookup (for mart vendors only)
     */
    private function resolveVendorID($vendorInput, $firestore)
    {
        // First try as direct ID
        try {
            $vendorDoc = $firestore->collection('vendors')->document($vendorInput)->snapshot();
            if ($vendorDoc->exists()) {
                $vendorData = $vendorDoc->data();
                // Verify it's a mart vendor
                if (isset($vendorData['vType']) && $vendorData['vType'] === 'mart') {
                    return $vendorInput; // Return the ID as-is
                }
            }
        } catch (\Exception $e) {
            // Continue to name lookup
        }

        // If not found as ID, try name lookup
        return $this->findVendorByName($vendorInput, $firestore);
    }

    /**
     * Resolve category ID - try direct ID first, then name lookup
     */
    private function resolveCategoryID($categoryInput, $firestore)
    {
        // First try as direct ID
        try {
            $categoryDoc = $firestore->collection('mart_categories')->document($categoryInput)->snapshot();
            if ($categoryDoc->exists()) {
                return $categoryInput; // Return the ID as-is
            }
        } catch (\Exception $e) {
            // Continue to name lookup
        }

        // If not found as ID, try name lookup
        return $this->findCategoryByName($categoryInput, $firestore);
    }

    /**
     * Resolve media image - lookup by multiple fields in media collection
     * Supports: image_name, name, slug, image_path
     */
    private function resolveMediaImage($imageInput, $firestore)
    {
        if (empty($imageInput)) {
            return null;
        }

        try {
            // If input is already a full image_path URL, return it directly
            if (filter_var($imageInput, FILTER_VALIDATE_URL) && strpos($imageInput, 'firebasestorage.googleapis.com') !== false) {
                return [
                    'image_path' => $imageInput,
                    'image_name' => basename(parse_url($imageInput, PHP_URL_PATH)),
                    'name' => 'Direct URL',
                    'slug' => 'direct-url'
                ];
            }

            // Try lookup by image_name
            $mediaData = $this->queryMediaByField($firestore, 'image_name', $imageInput);
            if ($mediaData) {
                return $mediaData;
            }

            // Try lookup by name
            $mediaData = $this->queryMediaByField($firestore, 'name', $imageInput);
            if ($mediaData) {
                return $mediaData;
            }

            // Try lookup by slug
            $mediaData = $this->queryMediaByField($firestore, 'slug', $imageInput);
            if ($mediaData) {
                return $mediaData;
            }

            // Try lookup by image_path (partial match for URLs)
            if (strpos($imageInput, 'http') === 0) {
                $mediaData = $this->queryMediaByField($firestore, 'image_path', $imageInput);
                if ($mediaData) {
                    return $mediaData;
                }
            }

        } catch (\Exception $e) {
            // Log error but continue without image
            \Log::warning('Media lookup failed for: ' . $imageInput . ' - ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Helper method to query media collection by specific field
     */
    private function queryMediaByField($firestore, $field, $value)
    {
        try {
            $mediaQuery = $firestore->collection('media')
                ->where($field, '==', $value)
                ->limit(1);

            $mediaDocs = $mediaQuery->documents();

            foreach ($mediaDocs as $mediaDoc) {
                if ($mediaDoc->exists()) {
                    return $mediaDoc->data();
                }
            }
        } catch (\Exception $e) {
            // Continue to next field
        }

        return null;
    }

    public function import(Request $request)
    {
        // Debug: Log the request details
        \Log::info('Import request received', [
            'has_file' => $request->hasFile('file'),
            'file_name' => $request->file('file') ? $request->file('file')->getClientOriginalName() : 'No file',
            'file_size' => $request->file('file') ? $request->file('file')->getSize() : 'No file',
            'file_mime' => $request->file('file') ? $request->file('file')->getMimeType() : 'No file',
            'file_extension' => $request->file('file') ? $request->file('file')->getClientOriginalExtension() : 'No file',
            'all_files' => $request->allFiles(),
            'request_all' => $request->all(),
            'request_headers' => $request->headers->all(),
        ]);

        // More flexible file validation
        $file = $request->file('file');
        if (!$file) {
            \Log::error('No file received in request');
            return back()->withErrors(['file' => 'Please select a file to import.']);
        }

        // Check if file is actually uploaded
        if (!$file->isValid()) {
            \Log::error('File upload failed', [
                'error' => $file->getError(),
                'error_message' => $file->getErrorMessage()
            ]);
            return back()->withErrors(['file' => 'File upload failed: ' . $file->getErrorMessage()]);
        }

        // Check file extension manually (more reliable than MIME type)
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['xlsx', 'xls'];

        \Log::info('File validation details', [
            'extension' => $extension,
            'allowed_extensions' => $allowedExtensions,
            'is_allowed' => in_array($extension, $allowedExtensions)
        ]);

        if (!in_array($extension, $allowedExtensions)) {
            \Log::error('Invalid file extension', ['extension' => $extension]);
            return back()->withErrors(['file' => 'The file must be an Excel file (.xlsx or .xls).']);
        }

        // Check file size
        $fileSize = $file->getSize();
        $maxSize = 10 * 1024 * 1024; // 10MB

        \Log::info('File size validation', [
            'file_size' => $fileSize,
            'max_size' => $maxSize,
            'is_valid_size' => $fileSize <= $maxSize
        ]);

        if ($fileSize > $maxSize) {
            \Log::error('File too large', ['file_size' => $fileSize, 'max_size' => $maxSize]);
            return back()->withErrors(['file' => 'The file size must not exceed 10MB.']);
        }

        \Log::info('File validation passed successfully');

        try {
            // Try to load the file with PhpSpreadsheet
        $spreadsheet = IOFactory::load($request->file('file'));
            \Log::info('File loaded successfully with PhpSpreadsheet');
        } catch (\Exception $e) {
            \Log::error('Failed to load file with PhpSpreadsheet', [
                'error' => $e->getMessage(),
                'file_path' => $request->file('file')->getPathname(),
                'file_size' => $request->file('file')->getSize()
            ]);
            return back()->withErrors(['file' => 'Failed to read Excel file. Please ensure it\'s a valid Excel file and not corrupted.']);
        }
        $rows = $spreadsheet->getActiveSheet()->toArray();

        if (empty($rows) || count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file is empty or missing data.']);
        }

        $headers = array_map('trim', array_shift($rows));

        // Initialize Firestore client
        $firestore = new FirestoreClient([
            'projectId' => config('firestore.project_id'),
            'keyFilePath' => config('firestore.credentials'),
        ]);

        $collection = $firestore->collection('mart_items');
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed
            $data = array_combine($headers, $row);

            // Skip empty rows
            if (empty($data['name'])) {
                continue;
            }

            try {
                // Validate required fields - support both ID and name fields
                $vendorInput = $data['vendorID'] ?? $data['vendorName'] ?? '';
                $categoryInput = $data['categoryID'] ?? $data['categoryName'] ?? '';

                if (empty($data['name']) || empty($data['price']) || empty($vendorInput) || empty($categoryInput)) {
                    $errors[] = "Row $rowNumber: Missing required fields (name, price, vendorID/vendorName, categoryID/categoryName)";
                    continue;
                }

                // Resolve vendor ID (supports both ID and name)
                $resolvedVendorID = $this->resolveVendorID($vendorInput, $firestore);
                if (!$resolvedVendorID) {
                    $errors[] = "Row $rowNumber: Vendor '{$vendorInput}' not found (neither as ID nor name) or is not a mart vendor";
                    continue;
                }

                // Resolve category ID (supports both ID and name)
                $resolvedCategoryID = $this->resolveCategoryID($categoryInput, $firestore);
                if (!$resolvedCategoryID) {
                    $errors[] = "Row $rowNumber: Category '{$categoryInput}' not found (neither as ID nor name)";
                    continue;
                }

                // Resolve subcategory ID (optional)
                $subcategoryInput = $data['subcategoryID'] ?? $data['subcategoryName'] ?? '';
                $resolvedSubcategoryID = $this->resolveSubcategoryID($subcategoryInput, $firestore);

                // Get section from subcategory
                $section = $data['section'] ?? $this->getSectionFromSubcategory($resolvedSubcategoryID, $firestore);

                \Log::info('Section resolution - Input: ' . ($data['section'] ?? 'null') . ', Resolved: ' . $section);

                // Handle subcategoryID as array (matching sample document structure)
                $subcategoryIDArray = [];
                if (!empty($resolvedSubcategoryID)) {
                    $subcategoryIDArray = [$resolvedSubcategoryID];
                }

                // Get category, subcategory, and vendor titles for storage
                $categoryTitle = '';
                $subcategoryTitle = '';
                $vendorTitle = '';

                // Get category title
                if ($resolvedCategoryID) {
                    try {
                        $categoryDoc = $firestore->collection('mart_categories')->document($resolvedCategoryID)->snapshot();
                        if ($categoryDoc->exists()) {
                            $categoryData = $categoryDoc->data();
                            $categoryTitle = $categoryData['title'] ?? '';
                        }
                    } catch (\Exception $e) {
                        // Continue without title if lookup fails
                    }
                }

                // Get subcategory title
                if (!empty($resolvedSubcategoryID)) {
                    try {
                        $subcategoryDoc = $firestore->collection('mart_subcategories')->document($resolvedSubcategoryID)->snapshot();
                        if ($subcategoryDoc->exists()) {
                            $subcategoryData = $subcategoryDoc->data();
                            $subcategoryTitle = $subcategoryData['title'] ?? '';
                        }
                    } catch (\Exception $e) {
                        // Continue without title if lookup fails
                    }
                }

                // Get vendor title
                if ($resolvedVendorID) {
                    try {
                        $vendorDoc = $firestore->collection('users')->document($resolvedVendorID)->snapshot();
                        if ($vendorDoc->exists()) {
                            $vendorData = $vendorDoc->data();
                            $vendorTitle = $vendorData['title'] ?? '';
                            \Log::info('Vendor title resolved: ' . $vendorTitle . ' for vendor ID: ' . $resolvedVendorID);
                        } else {
                            \Log::warning('Vendor document not found for ID: ' . $resolvedVendorID);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Vendor title lookup failed for ID: ' . $resolvedVendorID . ' - ' . $e->getMessage());
                    }
                }

                // Resolve media/image from media collection
                $resolvedPhoto = '';
                $resolvedPhotos = [];
                $imageInput = $data['photo'] ?? $data['image_name'] ?? '';

                \Log::info('Photo resolution - Row: ' . $rowNumber . ', Input: ' . $imageInput);
                \Log::info('Photo resolution - Available data keys: ' . implode(', ', array_keys($data)));
                \Log::info('Photo resolution - Data[photo]: ' . ($data['photo'] ?? 'null'));
                \Log::info('Photo resolution - Data[image_name]: ' . ($data['image_name'] ?? 'null'));

                if (!empty($imageInput)) {
                    $mediaData = $this->resolveMediaImage($imageInput, $firestore);
                    if ($mediaData) {
                        $resolvedPhoto = $mediaData['image_path'] ?? '';
                        $resolvedPhotos = [$resolvedPhoto];
                        \Log::info('Photo resolved successfully: ' . $resolvedPhoto);
                    } else {
                        \Log::warning('Photo resolution failed for input: ' . $imageInput);
                    }
                } else {
                    \Log::info('No photo input provided');
                }

                // Prepare mart item data matching the sample document structure
                \Log::info('Creating itemData - resolvedPhoto: ' . $resolvedPhoto);
                \Log::info('Creating itemData - resolvedPhotos: ' . json_encode($resolvedPhotos));

                $itemData = [
                    'name' => trim($data['name']),
                    'price' => (string) $data['price'], // String format to match sample
                    'disPrice' => !empty($data['disPrice']) ? (string) $data['disPrice'] : (string) $data['price'], // String format to match sample
                    'description' => trim($data['description'] ?? ''),
                    'vendorID' => $resolvedVendorID,
                    'vendorTitle' => $vendorTitle, // Add vendor title
                    'categoryID' => $resolvedCategoryID,
                    'categoryTitle' => $categoryTitle, // Add category title
                    'subcategoryID' => $subcategoryIDArray, // Array format matching sample
                    'subcategoryTitle' => $subcategoryTitle, // Add subcategory title
                    'section' => $section,
                    'photo' => $resolvedPhoto,
                    'photos' => $resolvedPhotos,
                    'publish' => strtolower($data['publish'] ?? 'true') === 'true',
                    'isAvailable' => strtolower($data['isAvailable'] ?? 'true') === 'true',
                    'nonveg' => strtolower($data['nonveg'] ?? 'false') === 'true',
                    'veg' => strtolower($data['nonveg'] ?? 'false') === 'true' ? false : true,
                    'takeawayOption' => strtolower($data['takeawayOption'] ?? 'false') === 'true',

                    // Enhanced Filter Fields - boolean format to match sample
                    'isSpotlight' => strtolower($data['isSpotlight'] ?? 'false') === 'true',
                    'isStealOfMoment' => strtolower($data['isStealOfMoment'] ?? 'false') === 'true',
                    'isFeature' => strtolower($data['isFeature'] ?? 'false') === 'true',
                    'isTrending' => strtolower($data['isTrending'] ?? 'false') === 'true',
                    'isNew' => strtolower($data['isNew'] ?? 'false') === 'true',
                    'isBestSeller' => strtolower($data['isBestSeller'] ?? 'false') === 'true',
                    'isSeasonal' => strtolower($data['isSeasonal'] ?? 'false') === 'true',

                    // Options configuration - boolean format to match sample
                    'has_options' => strtolower($data['has_options'] ?? 'false') === 'true',
                    'options_enabled' => strtolower($data['options_enabled'] ?? 'false') === 'true',
                    'options_toggle' => strtolower($data['options_toggle'] ?? 'false') === 'true',
                    'options_count' => !empty($data['options_count']) ? (int) $data['options_count'] : 0,
                    'options' => [],

                    // Nutritional information - number format to match sample
                    'quantity' => !empty($data['quantity']) ? (int) $data['quantity'] : -1,
                    'calories' => !empty($data['calories']) ? (int) $data['calories'] : 0,
                    'grams' => !empty($data['grams']) ? (int) $data['grams'] : 0,
                    'proteins' => !empty($data['proteins']) ? (int) $data['proteins'] : 0,
                    'fats' => !empty($data['fats']) ? (int) $data['fats'] : 0,

                    // Review fields - string format to match sample
                    'reviewCount' => '0', // String format to match sample
                    'reviewSum' => '0', // String format to match sample

                    // Additional fields matching sample structure
                    'addOnsTitle' => [], // Array format to match sample
                    'addOnsPrice' => [], // Array format to match sample
                    'product_specification' => (object) [], // Empty map/object to match sample
                    'item_attribute' => null,

                    // Timestamps - match exact field names from sample
                    'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                    'updated_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                ];

                // Create document with auto-generated ID
                $docRef = $collection->add($itemData);

                // Set the internal 'id' field to match the Firestore document ID
                $docRef->set(['id' => $docRef->id()], ['merge' => true]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row $rowNumber: " . $e->getMessage();
            }
        }

        if ($imported === 0) {
            return back()->withErrors(['file' => 'No valid rows were found to import.']);
        }

        $message = "Mart items imported successfully! ($imported rows)";
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', $errors);
        }

        return back()->with('success', $message);
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/mart_items_import_template.xlsx');

        // Create template directory if it doesn't exist
        $templateDir = dirname($filePath);
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateTemplate($filePath);
        }

        return response()->download($filePath, 'mart_items_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="mart_items_import_template.xlsx"'
        ]);
    }

    /**
     * Generate Excel template for mart items import
     */
    private function generateTemplate($filePath)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            // Note: Photo field (O1) accepts: image_name, name, slug, or full image_path URL
            $headers = [
                'A1' => 'name',
                'B1' => 'price',
                'C1' => 'disPrice',
                'D1' => 'description',
                'E1' => 'vendorID',
                'F1' => 'vendorName',
                'G1' => 'categoryID',
                'H1' => 'categoryName',
                'I1' => 'subcategoryID',
                'J1' => 'subcategoryName',
                'K1' => 'section',
                'L1' => 'publish',
                'M1' => 'nonveg',
                'N1' => 'isAvailable',
                'O1' => 'photo',
                'P1' => 'quantity',
                'Q1' => 'calories',
                'R1' => 'grams',
                'S1' => 'proteins',
                'T1' => 'fats',
                'U1' => 'isSpotlight',
                'V1' => 'isStealOfMoment',
                'W1' => 'isFeature',
                'X1' => 'isTrending',
                'Y1' => 'isNew',
                'Z1' => 'isBestSeller',
                'AA1' => 'isSeasonal',
                'AB1' => 'takeawayOption',
                'AC1' => 'has_options',
                'AD1' => 'options_enabled',
                'AE1' => 'options_toggle',
                'AF1' => 'options_count'
            ];

            // Set header values
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Add sample data row
            $sampleData = [
                'A2' => 'Sample Tomato',
                'B2' => '80',
                'C2' => '70',
                'D2' => 'Fresh red tomatoes for cooking',
                'E2' => '4ir2OLhuMEc2yg9L1YxX',
                'F2' => 'Sample Mart',
                'G2' => '68b16f87cac4e',
                'H2' => 'Vegetables',
                'I2' => '68b1a74123fe7',
                'J2' => 'Fresh Vegetables',
                'K2' => 'Grocery & Kitchen',
                'L2' => 'true',
                'M2' => 'false',
                'N2' => 'true',
                'O2' => 'Karam Dosa',
                'P2' => '-1',
                'Q2' => '0',
                'R2' => '0',
                'S2' => '0',
                'T2' => '0',
                'U2' => 'false',
                'V2' => 'false',
                'W2' => 'false',
                'X2' => 'false',
                'Y2' => 'false',
                'Z2' => 'false',
                'AA2' => 'false',
                'AB2' => 'false',
                'AC2' => 'false',
                'AD2' => 'false',
                'AE2' => 'false',
                'AF2' => '0'
            ];

            foreach ($sampleData as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Style headers
            $headerRange = 'A1:AA1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');

            // Auto-size columns
            foreach (range('A', 'AA') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

        } catch (\Exception $e) {
            // Log the error and create CSV fallback
            error_log("Template generation error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->generateCSVTemplate($filePath);
        }
    }

    /**
     * Generate CSV template as fallback
     */
    private function generateCSVTemplate($filePath)
    {
        $csvPath = str_replace('.xlsx', '.csv', $filePath);

        $headers = [
            'name', 'price', 'disPrice', 'description', 'vendorID', 'vendorName',
            'categoryID', 'categoryName', 'subcategoryID', 'subcategoryName', 'section',
            'publish', 'nonveg', 'isAvailable', 'photo', 'quantity', 'calories',
            'grams', 'proteins', 'fats', 'isSpotlight', 'isStealOfMoment', 'isFeature',
            'isTrending', 'isNew', 'isBestSeller', 'isSeasonal'
        ];

        $sampleData = [
            'Sample Tomato', '80', '70', 'Fresh red tomatoes for cooking',
            '4ir2OLhuMEc2yg9L1YxX', 'Sample Mart', '68b16f87cac4e', 'Vegetables',
            '68b1a74123fe7', 'Fresh Vegetables', 'Grocery & Kitchen', 'true', 'false',
            'true', '', '-1', '0', '0', '0', '0', 'false', 'false', 'false',
            'false', 'false', 'false', 'false'
        ];

        $file = fopen($csvPath, 'w');
        fputcsv($file, $headers);
        fputcsv($file, $sampleData);
        fclose($file);
    }

    /**
     * Inline update for mart item prices - ensures data consistency
     */
    public function inlineUpdate(Request $request, $id)
    {
        try {
            // Initialize Firestore client
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            $collection = $firestore->collection('mart_items');
            $document = $collection->document($id);
            $snapshot = $document->snapshot();

            if (!$snapshot->exists()) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }

            $currentData = $snapshot->data();
            $field = $request->input('field');
            $value = $request->input('value');

            // Validate field
            if (!in_array($field, ['price', 'disPrice'])) {
                return response()->json(['success' => false, 'message' => 'Invalid field'], 400);
            }

            // Validate value
            if (!is_numeric($value) || $value < 0) {
                return response()->json(['success' => false, 'message' => 'Invalid price value'], 400);
            }

            // Prepare update data with proper data types (matching edit page)
            $updateData = [];

            if ($field === 'price') {
                $updateData[] = ['path' => 'price', 'value' => (float) $value]; // Number format to match edit form

                // If discount price is higher than new price, reset it
                if (isset($currentData['disPrice']) && !empty($currentData['disPrice']) && (float)$currentData['disPrice'] > (float)$value) {
                    $updateData[] = ['path' => 'disPrice', 'value' => 0]; // Number format to match edit form
                }
            } elseif ($field === 'disPrice') {
                // If setting discount price to 0 or empty, set to 0
                if ($value == 0 || empty($value)) {
                    $updateData[] = ['path' => 'disPrice', 'value' => 0]; // Number format to match edit form
                } else {
                    $updateData[] = ['path' => 'disPrice', 'value' => (float) $value]; // Number format to match edit form

                    // Validate discount price is not higher than original price
                    if ((float)$value > (float)$currentData['price']) {
                        return response()->json(['success' => false, 'message' => 'Discount price cannot be higher than original price'], 400);
                    }
                }
            }

            // Update the document with proper Firestore format
            $document->update($updateData);

            // Prepare response message
            $message = 'Price updated successfully';
            $hasDiscountReset = false;

            // Check if discount was reset
            foreach ($updateData as $update) {
                if ($update['path'] === 'disPrice' && $update['value'] === 0) {
                    $hasDiscountReset = true;
                    break;
                }
            }

            if ($field === 'price' && $hasDiscountReset) {
                $message .= ' (discount price was reset as it was higher than the new price)';
            }

            // Convert updateData back to associative array for response
            $responseData = [];
            foreach ($updateData as $update) {
                $responseData[$update['path']] = $update['value'];
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

}


