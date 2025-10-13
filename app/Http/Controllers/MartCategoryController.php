<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Storage;

class MartCategoryController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view("martCategories.index");
    }

    public function edit($id)
    {
        return view('martCategories.edit')->with('id', $id);
    }

    public function create()
    {
        return view('martCategories.create');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $spreadsheet = IOFactory::load($request->file('file'));
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
        
        $collection = $firestore->collection('mart_categories');
        $imported = 0;
        $errors = [];
        
        foreach ($rows as $index => $row) {
            $data = array_combine($headers, $row);
            $rowNumber = $index + 2; // +2 because we removed header row and arrays are 0-indexed
            
            // Validate required fields
            if (empty($data['title'])) {
                $errors[] = "Row $rowNumber: Title is required";
                continue;
            }
            
            // Process photo - handle media module integration (MOST ADVANCED)
            $photoUrl = $this->resolveMediaImage($data['photo'] ?? '', $firestore);
            
            // Process review attributes - handle both comma-separated IDs and names
            $reviewAttributes = [];
            if (!empty($data['review_attributes'])) {
                $reviewAttributeInputs = array_filter(array_map('trim', explode(',', $data['review_attributes'])));
                foreach ($reviewAttributeInputs as $input) {
                    $attributeId = $this->resolveReviewAttributeId($input, $firestore);
                    if ($attributeId) {
                        $reviewAttributes[] = $attributeId;
                    } else {
                        $errors[] = "Row $rowNumber: Review attribute '$input' not found";
                    }
                }
            }
            
            // Create document with auto-generated ID - matching create form structure
            $docRef = $collection->add([
                'title' => trim($data['title']),
                'description' => trim($data['description'] ?? ''),
                'photo' => $photoUrl,
                'section' => $data['section'] ?? 'General',
                'category_order' => intval($data['category_order'] ?? 1),
                'section_order' => intval($data['category_order'] ?? 1), // Same as category_order
                'publish' => strtolower($data['publish'] ?? 'false') === 'true',
                'show_in_homepage' => strtolower($data['show_in_homepage'] ?? 'false') === 'true',
                'mart_id' => $data['mart_id'] ?? '', // Use provided mart_id or empty string
                'has_subcategories' => false,
                'subcategories_count' => 0,
                'review_attributes' => $reviewAttributes,
                'migratedBy' => 'bulk_import',
            ]);
            
            // Set the internal 'id' field to match the Firestore document ID
            $docRef->set(['id' => $docRef->id()], ['merge' => true]);
            
            $imported++;
        }
        if ($imported === 0) {
            return back()->withErrors(['file' => 'No valid rows were found to import.']);
        }
        
        $message = "Mart Categories imported successfully! ($imported rows)";
        if (!empty($errors)) {
            $message .= "\n\nWarnings:\n" . implode("\n", $errors);
        }
        
        return back()->with('success', $message);
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/mart_categories_import_template.xlsx');

        // Create template directory if it doesn't exist
        $templateDir = dirname($filePath);
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateTemplate($filePath);
        }
        
        return response()->download($filePath, 'mart_categories_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="mart_categories_import_template.xlsx"'
        ]);
    }

    /**
     * Generate Excel template for mart categories import
     */
    private function generateTemplate($filePath)
    {
        try {
            // Create new spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            
            // Remove default worksheet and create a new one
            $spreadsheet->removeSheetByIndex(0);
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('Mart Categories Import');
            
            // Set headers with proper formatting - matching create form fields
            // Field order matches the create form: title, description, photo, section, order, publish, show_in_homepage, mart_id, review_attributes
            $headers = [
                'A1' => 'title',                    // Mart Category Name (required)
                'B1' => 'description',              // Mart Category Description
                'C1' => 'photo',                    // Mart Category Image (media name/slug/URL)
                'D1' => 'section',                  // Section (e.g., Essentials & Daily Needs)
                'E1' => 'category_order',           // Display order within section
                'F1' => 'publish',                  // Publish status (true/false)
                'G1' => 'show_in_homepage',         // Show in homepage (true/false)
                'H1' => 'mart_id',                  // Mart ID (leave empty for general categories)
                'I1' => 'review_attributes'         // Review attributes (comma-separated)
            ];

            // Set header values with bold formatting
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
            }

            // Add sample data rows with helpful examples showing advanced media integration
            $sampleData = [
                // Row 2 - Example category with media slug
                'A2' => 'Sample Category 1',
                'B2' => 'This is a sample category description',
                'C2' => 'sample-media-slug',
                'D2' => 'Essentials & Daily Needs',
                'E2' => '1',
                'F2' => 'true',
                'G2' => 'true',
                'H2' => '', // mart_id - leave empty for general categories
                'I2' => 'quality,value,service',
                // Row 3 - Another example category with direct URL
                'A3' => 'Sample Category 2',
                'B3' => 'Another sample category description',
                'C3' => 'https://firebasestorage.googleapis.com/example-image.jpg',
                'D3' => 'Health & Wellness',
                'E3' => '2',
                'F3' => 'false',
                'G3' => 'false',
                'H3' => '', // mart_id - leave empty for general categories
                'I3' => 'freshness,organic'
            ];

            foreach ($sampleData as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Set column widths manually for better compatibility
            $sheet->getColumnDimension('A')->setWidth(20); // title
            $sheet->getColumnDimension('B')->setWidth(25); // description
            $sheet->getColumnDimension('C')->setWidth(20); // photo
            $sheet->getColumnDimension('D')->setWidth(25); // section
            $sheet->getColumnDimension('E')->setWidth(15); // category_order
            $sheet->getColumnDimension('F')->setWidth(10); // publish
            $sheet->getColumnDimension('G')->setWidth(15); // show_in_homepage
            $sheet->getColumnDimension('H')->setWidth(15); // mart_id
            $sheet->getColumnDimension('I')->setWidth(25); // review_attributes

            // Add borders to header row
            $sheet->getStyle('A1:I1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Create writer with proper options
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->setIncludeCharts(false);
            
            // Ensure directory exists
            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Save the file
            $writer->save($filePath);
            
            // Verify file was created and has content
            if (!file_exists($filePath) || filesize($filePath) < 1000) {
                throw new \Exception('Generated file is too small or corrupted');
            }

        } catch (\Exception $e) {
            // Clean up any partial file
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            throw new \Exception('Failed to generate template: ' . $e->getMessage());
        }
    }

    /**
     * Resolve media image - lookup by multiple fields in media collection (MOST ADVANCED)
     * Supports: image_name, name, slug, image_path
     */
    private function resolveMediaImage($imageInput, $firestore)
    {
        if (empty($imageInput)) {
            return '';
        }

        try {
            // If input is already a full image_path URL, validate and normalize path
            if (filter_var($imageInput, FILTER_VALIDATE_URL) && strpos($imageInput, 'firebasestorage.googleapis.com') !== false) {
                // Fix /media/ paths to /images/ paths
                if (strpos($imageInput, '/media/') !== false) {
                    $imageInput = str_replace('/media/', '/images/', $imageInput);
                    \Log::info('Fixed media path in bulk import: ' . $imageInput);
                }
                
                // Add .jpg extension if missing
                if (!preg_match('/\.(jpg|jpeg|png|gif)$/i', $imageInput)) {
                    $imageInput .= '.jpg';
                    \Log::info('Added .jpg extension to URL: ' . $imageInput);
                }
                
                return $imageInput;
            }

            // Try lookup by image_name
            $mediaData = $this->queryMediaByField($firestore, 'image_name', $imageInput);
            if ($mediaData && isset($mediaData['image_path'])) {
                return $mediaData['image_path'];
            }

            // Try lookup by name
            $mediaData = $this->queryMediaByField($firestore, 'name', $imageInput);
            if ($mediaData && isset($mediaData['image_path'])) {
                return $mediaData['image_path'];
            }

            // Try lookup by slug
            $mediaData = $this->queryMediaByField($firestore, 'slug', $imageInput);
            if ($mediaData && isset($mediaData['image_path'])) {
                return $mediaData['image_path'];
            }

            // Try lookup by image_path (partial match for URLs)
            if (strpos($imageInput, 'http') === 0) {
                $mediaData = $this->queryMediaByField($firestore, 'image_path', $imageInput);
                if ($mediaData && isset($mediaData['image_path'])) {
                    return $mediaData['image_path'];
                }
            }

        } catch (\Exception $e) {
            // Log error but continue without image
            \Log::warning('Media lookup failed for: ' . $imageInput . ' - ' . $e->getMessage());
        }

        // If no media found, normalize the input path before returning
        if (strpos($imageInput, '/media/') !== false) {
            $imageInput = str_replace('/media/', '/images/', $imageInput);
            \Log::info('Fixed media path in fallback: ' . $imageInput);
        }
        
        // Add .jpg extension if missing and it looks like a filename
        if (!preg_match('/\.(jpg|jpeg|png|gif)$/i', $imageInput) && !filter_var($imageInput, FILTER_VALIDATE_URL)) {
            $imageInput .= '.jpg';
            \Log::info('Added .jpg extension to filename: ' . $imageInput);
        }
        
        return $imageInput;
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

    /**
     * Resolve review attribute ID from input (can be ID or name)
     */
    private function resolveReviewAttributeId($input, $firestore)
    {
        if (empty($input)) {
            return null;
        }

        // First try as direct ID
        try {
            $attributeDoc = $firestore->collection('review_attributes')->document($input)->snapshot();
            if ($attributeDoc->exists()) {
                return $input; // Return the ID as-is
            }
        } catch (\Exception $e) {
            // Continue to name lookup
        }

        // If not found as ID, try name lookup
        try {
            $attributes = $firestore->collection('review_attributes')
                ->where('title', '==', trim($input))
                ->limit(1)
                ->documents();

            foreach ($attributes as $attribute) {
                return $attribute->id();
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return null;
    }
}



