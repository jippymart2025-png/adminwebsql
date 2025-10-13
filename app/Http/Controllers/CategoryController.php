<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
    
	  public function index()
    {
        return view("categories.index");
        
    }

     public function edit($id)
    {
    	return view('categories.edit')->with('id', $id);
    }

    public function create()
    {
        return view('categories.create');
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
        
        $collection = $firestore->collection('vendor_categories');
        $imported = 0;
        foreach ($rows as $row) {
            $data = array_combine($headers, $row);
            if (empty($data['title']) || empty($data['photo'])) {
                continue; // Skip incomplete rows
            }
            
            // Create document with auto-generated ID
            $docRef = $collection->add([
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'photo' => $data['photo'],
                'publish' => strtolower($data['publish'] ?? '') === 'true',
                'show_in_homepage' => strtolower($data['show_in_homepage'] ?? '') === 'true',
                'restaurant_id' => $data['restaurant_id'] ?? '',
                'review_attributes' => array_filter(array_map('trim', explode(',', $data['review_attributes'] ?? ''))),
                'migratedBy' => 'migrate:categories',
            ]);
            
            // Set the internal 'id' field to match the Firestore document ID
            $docRef->set(['id' => $docRef->id()], ['merge' => true]);
            
            $imported++;
        }
        if ($imported === 0) {
            return back()->withErrors(['file' => 'No valid rows were found to import.']);
        }
        return back()->with('success', "Categories imported successfully! ($imported rows)");
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/categories_import_template.xlsx');
        $templateDir = dirname($filePath);
        
        // Create template directory if it doesn't exist
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }
        
        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateTemplate($filePath);
        }

        return response()->download($filePath, 'categories_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="categories_import_template.xlsx"'
        ]);
    }

    /**
     * Generate Excel template for category import
     */
    private function generateTemplate($filePath)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $headers = [
                'A1' => 'title',
                'B1' => 'description',
                'C1' => 'photo',
                'D1' => 'publish',
                'E1' => 'show_in_homepage',
                'F1' => 'restaurant_id',
                'G1' => 'review_attributes'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
            }
            
            // Add sample data
            $sampleData = [
                'Fast Food',
                'Quick and delicious meals',
                'https://example.com/images/fast-food.jpg',
                'true',
                'true',
                '',
                'Taste,Quality,Service'
            ];
            
            $sheet->fromArray([$sampleData], null, 'A2');
            
            // Auto-size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate categories template: ' . $e->getMessage());
            abort(500, 'Failed to generate template');
        }
    }
}


