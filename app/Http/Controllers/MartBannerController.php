<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;

/**
 * MartBannerController
 * 
 * Handles CRUD operations for mart banner items.
 * 
 * Fields:
 * - title: Banner title
 * - description: Banner description
 * - text: Additional text content
 * - photo: Banner image
 * - position: Banner position (top, middle, bottom)
 * - redirect_type: Type of redirect (store, product, external_link)
 * - storeId: Store ID for store redirects
 * - productId: Product ID for product redirects
 * - external_link: External URL for external redirects
 * - is_publish: Publication status
 * - set_order: Display order
 * - created_at: Creation timestamp
 * - updated_at: Last update timestamp
 */
class MartBannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of mart banner items
     */
    public function index()
    {
        return view('martBanners.index');
    }

    /**
     * Show the form for creating a new mart banner item
     */
    public function create()
    {
        return view('martBanners.create');
    }

    /**
     * Show the form for editing the specified mart banner item
     */
    public function edit($id)
    {
        return view('martBanners.edit')->with('id', $id);
    }

    /**
     * Store a newly created mart banner item in Firestore
     */
    public function store(Request $request)
    {
        try {
            // Initialize Firestore client
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            $data = $request->all();
            
            // Prepare banner data
            $bannerData = [
                'title' => $data['title'] ?? '',
                'description' => $data['description'] ?? '',
                'text' => $data['text'] ?? '',
                'photo' => $data['photo'] ?? '',
                'position' => $data['position'] ?? 'top',
                'redirect_type' => $data['redirect_type'] ?? 'external_link',
                'storeId' => $data['storeId'] ?? null,
                'productId' => $data['productId'] ?? null,
                'external_link' => $data['external_link'] ?? null,
                'is_publish' => $data['is_publish'] ?? false,
                'set_order' => intval($data['set_order'] ?? 0),
                'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                'updated_at' => new \Google\Cloud\Core\Timestamp(new \DateTime())
            ];

            // Add to Firestore
            $docRef = $firestore->collection('mart_banners')->add($bannerData);

            return response()->json([
                'success' => true,
                'message' => 'Mart banner item created successfully',
                'id' => $docRef->id()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating mart banner item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified mart banner item in Firestore
     */
    public function update(Request $request, $id)
    {
        try {
            // Initialize Firestore client
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            $data = $request->all();
            
            // Prepare banner data
            $bannerData = [
                'title' => $data['title'] ?? '',
                'description' => $data['description'] ?? '',
                'text' => $data['text'] ?? '',
                'photo' => $data['photo'] ?? '',
                'position' => $data['position'] ?? 'top',
                'redirect_type' => $data['redirect_type'] ?? 'external_link',
                'storeId' => $data['storeId'] ?? null,
                'productId' => $data['productId'] ?? null,
                'external_link' => $data['external_link'] ?? null,
                'is_publish' => $data['is_publish'] ?? false,
                'set_order' => intval($data['set_order'] ?? 0),
                'updated_at' => new \Google\Cloud\Core\Timestamp(new \DateTime())
            ];

            // Update in Firestore
            $firestore->collection('mart_banners')->document($id)->set($bannerData, ['merge' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Mart banner item updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating mart banner item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified mart banner item from Firestore
     */
    public function destroy($id)
    {
        try {
            // Initialize Firestore client
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            // Delete from Firestore
            $firestore->collection('mart_banners')->document($id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mart banner item deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting mart banner item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle publish status of mart banner item
     */
    public function togglePublish($id)
    {
        try {
            // Initialize Firestore client
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            // Get current document
            $doc = $firestore->collection('mart_banners')->document($id)->snapshot();
            
            if (!$doc->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mart banner item not found'
                ], 404);
            }

            $currentData = $doc->data();
            $newPublishStatus = !($currentData['is_publish'] ?? false);

            // Update publish status
            $firestore->collection('mart_banners')->document($id)->update([
                ['path' => 'is_publish', 'value' => $newPublishStatus],
                ['path' => 'updated_at', 'value' => new \Google\Cloud\Core\Timestamp(new \DateTime())]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Publish status updated successfully',
                'is_publish' => $newPublishStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating publish status: ' . $e->getMessage()
            ], 500);
        }
    }
}
