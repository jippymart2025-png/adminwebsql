<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Google\Cloud\Firestore\FirestoreClient;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("brands.index");
    }

    public function edit($id)
    {
        return view('brands.edit')->with('id', $id);
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            $collection = $firestore->collection('brands');

            // Generate slug if not provided
            $slug = $request->slug;
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->name)));
            }

            // Handle logo upload
            $logoUrl = '';
            if ($request->hasFile('logo')) {
                $logoUrl = $this->uploadLogo($request->file('logo'));
            }

            $brandData = [
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description ?? '',
                'status' => (bool) $request->status,
                'logo_url' => $logoUrl,
                'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                'updated_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
            ];

            // Create document with auto-generated ID
            $docRef = $collection->add($brandData);

            // Set the internal 'id' field to match the Firestore document ID
            $docRef->set(['id' => $docRef->id()], ['merge' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating brand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            $document = $firestore->collection('brands')->document($id);
            $snapshot = $document->snapshot();

            if (!$snapshot->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found'
                ], 404);
            }

            $currentData = $snapshot->data();

            // Generate slug if not provided
            $slug = $request->slug;
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->name)));
            }

            // Handle logo upload
            $logoUrl = $currentData['logo_url'] ?? '';
            if ($request->hasFile('logo')) {
                $logoUrl = $this->uploadLogo($request->file('logo'));
            }

            $updateData = [
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description ?? '',
                'status' => (bool) $request->status,
                'logo_url' => $logoUrl,
                'updated_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
            ];

            $document->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating brand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            // Check if brand is being used by any items
            $itemsQuery = $firestore->collection('mart_items')
                ->where('brand_id', '==', $id)
                ->limit(1);
            
            $items = $itemsQuery->documents();
            if (!$items->isEmpty()) {
                return redirect()->route('brands')->with('error', 'Cannot delete brand. It is being used by one or more items.');
            }

            // Delete the brand document
            $firestore->collection('brands')->document($id)->delete();

            return redirect()->route('brands')->with('success', 'Brand deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('brands')->with('error', 'Error deleting brand: ' . $e->getMessage());
        }
    }

    public function getData(Request $request)
    {
        try {
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            $collection = $firestore->collection('brands');
            $query = $collection->orderBy('created_at', 'DESC');

            // Apply search filter
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                // Note: Firestore doesn't support full-text search, so we'll filter in PHP
            }

            $documents = $query->documents();
            $data = [];

            foreach ($documents as $document) {
                if ($document->exists()) {
                    $docData = $document->data();
                    
                    // Apply search filter in PHP (since Firestore doesn't support full-text search)
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $searchValue = strtolower($request->search['value']);
                        $searchableFields = [
                            strtolower($docData['name'] ?? ''),
                            strtolower($docData['slug'] ?? ''),
                            strtolower($docData['description'] ?? '')
                        ];
                        
                        if (!str_contains(implode(' ', $searchableFields), $searchValue)) {
                            continue;
                        }
                    }

                    $data[] = [
                        'id' => $document->id(),
                        'name' => $docData['name'] ?? '',
                        'slug' => $docData['slug'] ?? '',
                        'logo_url' => $docData['logo_url'] ?? '',
                        'description' => $docData['description'] ?? '',
                        'status' => $docData['status'] ?? false,
                        'created_at' => isset($docData['created_at']) ? $docData['created_at']->format('Y-m-d H:i:s') : '',
                    ];
                }
            }

            // Apply pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            $totalRecords = count($data);
            $filteredData = array_slice($data, $start, $length);

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $filteredData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error fetching data: ' . $e->getMessage()
            ]);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        
        if ($extension === 'csv') {
            // Handle CSV files
            $rows = [];
            if (($handle = fopen($file->getPathname(), 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            // Handle Excel files (prioritized)
            try {
                $spreadsheet = IOFactory::load($file);
                $rows = $spreadsheet->getActiveSheet()->toArray();
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'ZipArchive') !== false) {
                    return back()->withErrors(['file' => 'Excel import requires ZipArchive extension. Please use CSV format or enable ZipArchive in your PHP configuration.']);
                }
                return back()->withErrors(['file' => 'Error reading Excel file: ' . $e->getMessage()]);
            }
        }

        if (empty($rows) || count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file is empty or missing data.']);
        }

        $headers = array_map('trim', array_shift($rows));
        
        // Validate headers
        $requiredHeaders = ['name'];
        $missingHeaders = array_diff($requiredHeaders, $headers);
        
        if (!empty($missingHeaders)) {
            return back()->withErrors(['file' => 'Missing required columns: ' . implode(', ', $missingHeaders) . 
                '. Please use the template provided by the "Download Template" button.']);
        }

        // Initialize Firestore client using helper function (uses REST transport)
        $firestore = firestore();

        $collection = $firestore->collection('brands');
        $imported = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed
            $data = array_combine($headers, $row);

            // Skip completely empty rows
            if ($this->isEmptyRow($row)) {
                continue;
            }

            try {
                // Validate required fields
                if (empty($data['name'])) {
                    $errors[] = "Row $rowNumber: Missing required field (name)";
                    continue;
                }

                // Generate slug if not provided
                $slug = $data['slug'] ?? '';
                if (empty($slug)) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
                }

                // Prepare brand data
                $brandData = [
                    'name' => trim($data['name']),
                    'slug' => $slug,
                    'description' => trim($data['description'] ?? ''),
                    'status' => strtolower($data['status'] ?? 'true') === 'true',
                    'logo_url' => trim($data['logo_url'] ?? ''),
                    'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                    'updated_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                ];

                // Check if brand already exists by name
                $existingBrands = $collection->where('name', '=', trim($data['name']))->documents();
                $action = 'created';
                
                if (!$existingBrands->isEmpty()) {
                    // Update existing brand
                    $existingDoc = $existingBrands->rows()[0];
                    $existingDoc->reference()->set($brandData, ['merge' => true]);
                    $updated++;
                } else {
                    // Create new brand
                    $docRef = $collection->add($brandData);
                    // Set the internal 'id' field to match the Firestore document ID
                    $docRef->set(['id' => $docRef->id()], ['merge' => true]);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = "Row $rowNumber: " . $e->getMessage();
            }
        }

        if ($imported === 0 && $updated === 0) {
            return back()->withErrors(['file' => 'No valid rows were found to import.']);
        }

        $message = "Brands processed successfully! Created: $imported, Updated: $updated";
        if (!empty($errors)) {
            $message .= " Errors: " . count($errors) . " rows failed.";
        }

        return back()->with('success', $message);
    }

    public function downloadTemplate(Request $request)
    {
        $format = $request->get('format', 'excel'); // Default to Excel format
        
        if ($format === 'csv') {
            return $this->downloadCsvTemplate();
        } else {
            return $this->downloadExcelTemplate();
        }
    }
    
    private function downloadCsvTemplate()
    {
        $filePath = storage_path('app/templates/brands_import_template.csv');
        $templateDir = dirname($filePath);
        
        // Create template directory if it doesn't exist
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }
        
        // Generate CSV template
        $csvContent = "name,slug,description,status,logo_url\n";
        $csvContent .= "Nike,nike,Sportswear and footwear brand,true,https://example.com/nike-logo.png\n";
        $csvContent .= "Adidas,adidas,German sportswear brand,true,https://example.com/adidas-logo.png\n";
        
        file_put_contents($filePath, $csvContent);

        return response()->download($filePath, 'brands_import_template.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="brands_import_template.csv"'
        ]);
    }
    
    private function downloadExcelTemplate()
    {
        $filePath = storage_path('app/templates/brands_import_template.xlsx');
        $templateDir = dirname($filePath);
        
        // Create template directory if it doesn't exist
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }
        
        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateTemplate($filePath);
        }

        return response()->download($filePath, 'brands_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="brands_import_template.xlsx"'
        ]);
    }

    /**
     * Generate Excel template for brand import
     */
    private function generateTemplate($filePath)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers with proper styling
            $headers = [
                'A1' => 'name',
                'B1' => 'slug', 
                'C1' => 'description',
                'D1' => 'status',
                'E1' => 'logo_url'
            ];
            
            // Set header values and styling
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
            }
            
            // Add sample data with multiple examples
            $sampleData = [
                ['Nike', 'nike', 'Sportswear and footwear brand', 'true', 'https://example.com/nike-logo.png'],
                ['Adidas', 'adidas', 'German sportswear brand', 'true', 'https://example.com/adidas-logo.png'],
                ['Puma', 'puma', 'Sports and lifestyle brand', 'false', 'https://example.com/puma-logo.png']
            ];
            
            $row = 2;
            foreach ($sampleData as $data) {
                $col = 'A';
                foreach ($data as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }
            
            // Add instructions in a separate section
            $instructionRow = $row + 2;
            $sheet->setCellValue('A' . $instructionRow, 'Instructions:');
            $sheet->getStyle('A' . $instructionRow)->getFont()->setBold(true);
            
            $instructions = [
                'name' => 'Required: Brand name (e.g., Nike, Adidas)',
                'slug' => 'Optional: URL-friendly version (auto-generated if empty)',
                'description' => 'Optional: Brand description',
                'status' => 'Required: true/false (active/inactive)',
                'logo_url' => 'Optional: Full URL to brand logo image'
            ];
            
            $row = $instructionRow + 1;
            foreach ($instructions as $field => $instruction) {
                $sheet->setCellValue('A' . $row, $field . ': ' . $instruction);
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'E') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Set column widths for better readability
            $sheet->getColumnDimension('A')->setWidth(20); // name
            $sheet->getColumnDimension('B')->setWidth(15); // slug
            $sheet->getColumnDimension('C')->setWidth(40); // description
            $sheet->getColumnDimension('D')->setWidth(10); // status
            $sheet->getColumnDimension('E')->setWidth(50); // logo_url
            
            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate template: ' . $e->getMessage());
        }
    }

    /**
     * Upload logo to storage
     */
    private function uploadLogo($file)
    {
        try {
            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store file in public storage
            $path = $file->storeAs('brands/logos', $filename, 'public');
            
            // Return full URL
            return asset('storage/' . $path);
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload logo: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if a row is completely empty
     */
    private function isEmptyRow($row)
    {
        return empty(array_filter($row, function($value) {
            return !is_null($value) && $value !== '';
        }));
    }
}
