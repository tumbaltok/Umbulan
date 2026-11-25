<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    /**
     * Daftar notifikasi in-app / push notification untuk pengguna APK Native.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:50',
            'unread_only' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = $request->boolean('unread_only')
            ? $user->unreadNotifications()
            : $user->notifications();

        $notifications = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'unread_count' => $user->unreadNotifications()->count(),
                'items' => collect($notifications->items())->map(fn ($n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->data['title'] ?? 'Notifikasi',
                    'body' => $n->data['body'] ?? '',
                    'meta' => $n->data['meta'] ?? [],
                    'read_at' => $n->read_at?->toIso8601String(),
                    'created_at' => $n->created_at?->toIso8601String(),
                ])->values(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ],
        ]);
    }

    /**
     * Menandai satu notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi ditandai sudah dibaca.',
            'data' => [
                'id' => $notification->id,
                'read_at' => $notification->read_at?->toIso8601String(),
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * Menandai seluruh notifikasi milik pengguna sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Semua notifikasi ditandai sudah dibaca.',
            'data' => [
                'unread_count' => 0,
            ],
        ]);
    }
}
