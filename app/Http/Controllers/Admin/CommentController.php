<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function list(Request $request): View
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

        $comments = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.comment.list', compact('comments'));
    }

    public function approve(Request $request): RedirectResponse
    {
        $comment = Comment::query()->findOrFail($request->integer('id'));
        $comment->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Đã duyệt bình luận.');
    }

    public function reject(Request $request): RedirectResponse
    {
        $comment = Comment::query()->findOrFail($request->integer('id'));
        $comment->update([
            'status' => 'rejected',
            'approved_at' => null,
        ]);

        return back()->with('success', 'Đã từ chối bình luận.');
    }
}
