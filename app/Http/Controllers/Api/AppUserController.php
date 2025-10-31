<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppUser;
use Illuminate\Support\Facades\Storage;

class AppUserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'countryCode' => 'nullable|string|max:10',
            'phoneNumber' => 'nullable|string|max:30',
            'active' => 'nullable',
            'role' => 'nullable|string|max:50',
            'zoneId' => 'nullable|string|max:255',
            'photo' => 'nullable|string', // base64 data URL (optional)
            'fileName' => 'nullable|string',
        ]);

        $profileUrl = null;
        if (!empty($validated['photo'])) {
            $data = $validated['photo'];
            $data = preg_replace('#^data:image/\w+;base64,#i', '', $data);
            $binary = base64_decode($data, true);
            if ($binary !== false) {
                $name = $validated['fileName'] ?? ('user_' . time() . '.jpg');
                $path = 'users/' . $name;
                Storage::disk('public')->put($path, $binary);
                $profileUrl = asset('storage/' . $path);
            }
        }

        $user = AppUser::updateOrCreate(
            ['email' => $validated['email']],
            [
                'firstName' => $validated['firstName'],
                'lastName' => $validated['lastName'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'countryCode' => $validated['countryCode'] ?? null,
                'phoneNumber' => $validated['phoneNumber'] ?? null,
                'profilePictureURL' => $profileUrl,
                'provider' => 'email',
                'role' => $validated['role'] ?? 'customer',
                'active' => !empty($validated['active']) ? 'true' : 'false',
                'isActive' => !empty($validated['active']) ? 1 : 0,
                'zoneId' => $validated['zoneId'] ?? null,
                'appIdentifier' => 'web',
                'createdAt' => now()->format('Y-m-d H:i:s'),
            ]
        );

        return response()->json(['status' => true, 'data' => ['id' => (string) ($user->firebase_id ?: $user->id)]], 201);
    }
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 10);
        $page = (int) $request->query('page', 1);
        $status = $request->query('status'); // 'active' | 'inactive' | null
        $zoneId = $request->query('zoneId');
        $search = trim((string) $request->query('search', ''));

        $query = AppUser::query();

        // Only customers by default unless role is specified
        $role = $request->query('role', 'customer');
        if (!empty($role)) {
            $query->where('role', $role);
        }

        // Date range filter (expects Y-m-d or full datetime strings)
        $from = $request->query('from');
        $to = $request->query('to');
        if (!empty($from)) {
            $query->where('createdAt', '>=', $from);
        }
        if (!empty($to)) {
            $query->where('createdAt', '<=', $to);
        }

        // Status filter
        if ($status === 'active') {
            $query->where(function ($q) {
                $q->where('active', '1')->orWhere('active', 'true')->orWhere('isActive', 1);
            });
        } elseif ($status === 'inactive') {
            $query->where(function ($q) {
                $q->where('active', '0')->orWhere('active', 'false')->orWhereNull('active')->orWhere('isActive', 0);
            });
        }

        // Zone filter
        if (!empty($zoneId)) {
            $query->where('zoneId', $zoneId);
        }

        // Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('firstName', 'like', "%$search%")
                  ->orWhere('lastName', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phoneNumber', 'like', "%$search%")
                  ->orWhere('createdAt', 'like', "%$search%");
            });
        }

        $total = (clone $query)->count();
        $rows = $query->orderByDesc('id')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $items = $rows->map(function ($u) {
            $fullName = trim(($u->firstName ?? '') . ' ' . ($u->lastName ?? ''));
            return [
                'id' => (string) ($u->firebase_id ?: $u->id),
                'firstName' => $u->firstName,
                'lastName' => $u->lastName,
                'fullName' => $fullName,
                'email' => (string) ($u->email ?? ''),
                'phoneNumber' => (string) ($u->phoneNumber ?? ''),
                'zoneId' => (string) ($u->zoneId ?? ''),
                'createdAt' => (string) ($u->createdAt ?? ''),
                'active' => in_array((string) $u->active, ['1','true'], true) || (bool) ($u->isActive ?? 0),
                'profilePictureURL' => $u->profilePictureURL,
            ];
        })->all();

        return response()->json([
            'status' => true,
            'data' => $items,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'has_more' => ($page * $limit) < $total,
            ],
        ]);
    }

    public function destroy(string $id)
    {
        $user = AppUser::where('firebase_id', $id)->orWhere('id', $id)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['status' => true]);
    }

    public function setActive(Request $request, string $id)
    {
        $isActive = filter_var($request->input('active', false), FILTER_VALIDATE_BOOLEAN);
        $user = AppUser::where('firebase_id', $id)->orWhere('id', $id)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }
        $user->active = $isActive ? 'true' : 'false';
        $user->isActive = $isActive ? 1 : 0;
        $user->save();
        return response()->json(['status' => true]);
    }
}


