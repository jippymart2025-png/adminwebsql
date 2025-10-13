<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Cache;
use Google\Cloud\Firestore\FieldPath;

class FirebaseUserController extends Controller
{
    protected $firestore;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));

        $this->firestore = $factory->createFirestore()->database();
    }

    public function index(Request $request)
    {
        $role = $request->query('role', null);
        $page = (int) $request->query('page', 1); // kept for backward compatibility in meta
        $limit = (int) $request->query('limit', 10);
        $cursor = $request->query('cursor'); // Firestore document ID to start after
        $withCounts = (int) $request->query('with_counts', 0) === 1;

        if (!$role) {
            return response()->json([
                'status' => false,
                'message' => 'Role is required (driver, vendor, user)',
            ], 400);
        }

        $cacheKey = "firebase_users_page_role_{$role}_cursor_" . ($cursor ?: 'start') . "_limit_{$limit}";

        $data = Cache::remember($cacheKey, 30, function () use ($role, $cursor, $limit) {
            $query = $this->firestore
                ->collection('users')
                ->where('role', '==', $role)
                ->orderBy(FieldPath::documentId());

            if (!empty($cursor)) {
                $query = $query->startAfter([$cursor]);
            }

            $query = $query->limit($limit);

            $documents = $query->documents();

            $users = [];
            $nextCursor = null;

            foreach ($documents as $document) {
                $docData = $document->data();
                $docData['id'] = $document->id();
                $users[] = $docData;
                $nextCursor = $document->id();
            }

            return [
                'users' => $users,
                'next_cursor' => $nextCursor,
            ];
        });

        // Counts: compute on-demand or return cached values to keep response fast
        $countsCacheKey = "firebase_users_counts_role_{$role}";
        $counts = Cache::get($countsCacheKey);
        if ($withCounts) {
            $counts = Cache::remember($countsCacheKey, 120, function () use ($role) {
                $base = $this->firestore->collection('users')->where('role', '==', $role);

                // total
                $total = 0;
                foreach ($base->select(['__name__'])->documents() as $doc) {
                    $total++;
                }

                // active
                $active = 0;
                foreach ($base->where('active', '==', true)->select(['__name__'])->documents() as $doc) {
                    $active++;
                }

                // document verified
                $documentVerified = 0;
                foreach ($base->where('isDocumentVerify', '==', true)->select(['__name__'])->documents() as $doc) {
                    $documentVerified++;
                }

                $inactive = max($total - $active, 0);

                return [
                    'total' => $total,
                    'active' => $active,
                    'inactive' => $inactive,
                    'document_verified' => $documentVerified,
                ];
            });
        }

        return response()->json([
            'status' => true,
            'message' => 'Users fetched successfully',
            'meta' => [
                'role' => $role,
                'limit' => $limit,
                'count' => count($data['users']),
                'page' => $page,
                'cursor' => $cursor,
                'next_cursor' => $data['next_cursor'],
                'with_counts' => $withCounts,
            ],
            'counts' => $counts,
            'data' => $data['users'],
        ]);
    }
}
