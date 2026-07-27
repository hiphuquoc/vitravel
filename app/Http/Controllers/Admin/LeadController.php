<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\CustomTourRequest;
use App\Models\QuickInquiryLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function quickInquiries(Request $request): View
    {
        $query = QuickInquiryLead::query()->with('relatedPackage.translations');

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

        $leads = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.lead.quick-inquiries', compact('leads'));
    }

    public function customTours(Request $request): View
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

        $leads = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.lead.custom-tours', compact('leads'));
    }

    public function contacts(Request $request): View
    {
        $query = ContactMessage::query();

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

        $leads = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.lead.contacts', compact('leads'));
    }

    public function updateQuickInquiryStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:quick_inquiry_leads,id',
            'status' => 'required|string|in:new,contacted,quoted,closed,spam',
        ]);

        $lead = QuickInquiryLead::query()->findOrFail($validated['id']);
        $lead->update([
            'status' => $validated['status'],
            'contacted_at' => in_array($validated['status'], ['contacted', 'quoted', 'closed'], true) ? now() : $lead->contacted_at,
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái yêu cầu.');
    }

    public function updateCustomTourStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:custom_tour_requests,id',
            'status' => 'required|string|in:new,contacted,quoted,closed,spam',
        ]);

        $lead = CustomTourRequest::query()->findOrFail($validated['id']);
        $lead->update([
            'status' => $validated['status'],
            'contacted_at' => in_array($validated['status'], ['contacted', 'quoted', 'closed'], true) ? now() : $lead->contacted_at,
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái tour riêng.');
    }

    public function updateContactStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:contact_messages,id',
            'status' => 'required|string|in:new,contacted,quoted,closed,spam',
        ]);

        $lead = ContactMessage::query()->findOrFail($validated['id']);
        $lead->update([
            'status' => $validated['status'],
            'contacted_at' => in_array($validated['status'], ['contacted', 'quoted', 'closed'], true) ? now() : $lead->contacted_at,
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái liên hệ.');
    }
}
