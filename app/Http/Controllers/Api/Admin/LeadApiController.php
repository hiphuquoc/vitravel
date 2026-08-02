<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\CustomTourRequest;
use App\Models\QuickInquiryLead;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LeadApiController extends Controller
{
    public function quickInquiries(Request $request): JsonResponse
    {
        $query = QuickInquiryLead::query()->with('relatedPackage.translations');
        $this->applyLeadFilters($query, $request);
        $paginator = $query->orderByDesc('id')->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(fn (QuickInquiryLead $l) => [
                'id' => $l->id,
                'name' => $l->name,
                'email' => $l->email,
                'phone' => $l->phone,
                'status' => $l->status,
                'message' => $l->message,
                'package' => $l->relatedPackage ? [
                    'id' => $l->relatedPackage->id,
                    'title' => $l->relatedPackage->translation()?->title,
                ] : null,
                'created_at' => $l->created_at?->toIso8601String(),
                'contacted_at' => $l->contacted_at?->toIso8601String(),
            ]),
            'meta' => $this->meta($paginator),
            'statuses' => $this->statuses(),
        ]);
    }

    public function customTours(Request $request): JsonResponse
    {
        $query = CustomTourRequest::query();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search): void {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        $paginator = $query->orderByDesc('id')->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(fn (CustomTourRequest $l) => [
                'id' => $l->id,
                'name' => trim(($l->first_name ?? '').' '.($l->last_name ?? '')),
                'email' => $l->email,
                'phone' => $l->phone,
                'status' => $l->status,
                'destination' => is_array($l->countries_to_visit)
                    ? implode(', ', $l->countries_to_visit)
                    : null,
                'message' => $l->additional_notes,
                'created_at' => $l->created_at?->toIso8601String(),
                'contacted_at' => $l->contacted_at?->toIso8601String(),
            ]),
            'meta' => $this->meta($paginator),
            'statuses' => $this->statuses(),
        ]);
    }

    public function contacts(Request $request): JsonResponse
    {
        $query = ContactMessage::query();
        $this->applyLeadFilters($query, $request);
        $paginator = $query->orderByDesc('id')->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(fn (ContactMessage $l) => [
                'id' => $l->id,
                'name' => $l->name,
                'email' => $l->email,
                'phone' => $l->phone,
                'status' => $l->status,
                'subject' => null,
                'message' => $l->message,
                'created_at' => $l->created_at?->toIso8601String(),
                'contacted_at' => $l->contacted_at?->toIso8601String(),
            ]),
            'meta' => $this->meta($paginator),
            'statuses' => $this->statuses(),
        ]);
    }

    public function updateQuickInquiryStatus(Request $request, int $id): JsonResponse
    {
        return $this->updateStatus($request, QuickInquiryLead::query()->findOrFail($id));
    }

    public function updateCustomTourStatus(Request $request, int $id): JsonResponse
    {
        return $this->updateStatus($request, CustomTourRequest::query()->findOrFail($id));
    }

    public function updateContactStatus(Request $request, int $id): JsonResponse
    {
        return $this->updateStatus($request, ContactMessage::query()->findOrFail($id));
    }

    private function updateStatus(Request $request, QuickInquiryLead|CustomTourRequest|ContactMessage $lead): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:new,contacted,quoted,closed,spam',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $lead->update([
            'status' => $validated['status'],
            'contacted_at' => in_array($validated['status'], ['contacted', 'quoted', 'closed'], true)
                ? now()
                : $lead->contacted_at,
        ]);

        return ApiResponse::success(['id' => $lead->id, 'status' => $lead->status], 'Đã cập nhật trạng thái');
    }

    private function applyLeadFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
    }

    /** @return list<array{value: string, label: string}> */
    private function statuses(): array
    {
        return [
            ['value' => 'new', 'label' => 'Mới'],
            ['value' => 'contacted', 'label' => 'Đã liên hệ'],
            ['value' => 'quoted', 'label' => 'Đã báo giá'],
            ['value' => 'closed', 'label' => 'Đóng'],
            ['value' => 'spam', 'label' => 'Spam'],
        ];
    }

    /** @return array<string, int> */
    private function meta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
