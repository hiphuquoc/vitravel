<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Comment::query()->with(['article.translations']);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }
        $paginator = $query->orderByDesc('id')->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(fn (Comment $c) => [
                'id' => $c->id,
                'full_name' => $c->full_name,
                'email' => $c->email,
                'content' => $c->content,
                'status' => $c->status,
                'article' => $c->article ? [
                    'id' => $c->article->id,
                    'title' => $c->article->translation()?->title,
                ] : null,
                'created_at' => $c->created_at?->toIso8601String(),
                'approved_at' => $c->approved_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function approve(int $id): JsonResponse
    {
        $comment = Comment::query()->findOrFail($id);
        $comment->update(['status' => 'approved', 'approved_at' => now()]);

        return ApiResponse::success(['id' => $comment->id, 'status' => $comment->status], 'Đã duyệt');
    }

    public function reject(int $id): JsonResponse
    {
        $comment = Comment::query()->findOrFail($id);
        $comment->update(['status' => 'rejected', 'approved_at' => null]);

        return ApiResponse::success(['id' => $comment->id, 'status' => $comment->status], 'Đã từ chối');
    }
}
