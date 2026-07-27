<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Http\Requests\ContactMessageRequest;
use App\Http\Requests\CustomTourRequest;
use App\Http\Requests\QuickInquiryRequest;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\CustomTourRequest as CustomTourRequestModel;
use App\Models\QuickInquiryLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function storeQuickInquiry(QuickInquiryRequest $request): RedirectResponse
    {
        QuickInquiryLead::query()->create([
            ...$request->safe()->except('website'),
            'source_page_url' => $request->headers->get('referer'),
            'locale' => app()->getLocale(),
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'utm' => $this->utmPayload($request),
        ]);

        return back()->with('success', 'quick_inquiry');
    }

    public function storeCustomTour(CustomTourRequest $request): RedirectResponse
    {
        $data = $request->validated();

        CustomTourRequestModel::query()->create([
            'adults_count' => $data['adults'],
            'children_count' => $data['children'] ?? 0,
            'infants_count' => $data['infants'] ?? 0,
            'duration_text' => $data['duration_text'],
            'arrival_date' => $data['arrival_date'],
            'countries_to_visit' => $data['countries'],
            'accommodation_preference' => $data['accommodation'],
            'budget_amount' => $data['budget_amount'] ?? null,
            'budget_currency' => 'VND',
            'budget_unit' => $this->normalizeBudgetUnit($data['budget_unit'] ?? null),
            'gender' => $this->normalizeGender($data['gender']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'nationality' => $data['nationality'],
            'city' => $data['city'],
            'additional_notes' => $data['additional_notes'] ?? null,
            'locale' => app()->getLocale(),
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'utm' => $this->utmPayload($request),
        ]);

        return back()->with('success', 'custom_tour');
    }

    public function storeContact(ContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::query()->create([
            ...$request->safe()->except('website'),
            'locale' => app()->getLocale(),
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'utm' => $this->utmPayload($request),
        ]);

        return back()->with('success', 'contact');
    }

    public function storeComment(CommentRequest $request): RedirectResponse
    {
        Comment::query()->create([
            ...$request->safe()->except('website'),
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return back()->with('success', 'comment');
    }

    /** @return array<string, string|null> */
    protected function utmPayload(Request $request): array
    {
        return [
            'source' => $request->input('utm_source'),
            'medium' => $request->input('utm_medium'),
            'campaign' => $request->input('utm_campaign'),
            'term' => $request->input('utm_term'),
            'content' => $request->input('utm_content'),
        ];
    }

    protected function normalizeBudgetUnit(?string $unit): ?string
    {
        return match ($unit) {
            'Mỗi người', 'per_person', 'per-person' => 'per-person',
            'Cả nhóm', 'per_group', 'per-group' => 'per-group',
            default => $unit,
        };
    }

    protected function normalizeGender(string $gender): string
    {
        return match ($gender) {
            'Ông', 'Mr', 'mr' => 'mr',
            'Bà', 'Mrs', 'Ms', 'mrs' => 'mrs',
            default => 'mr',
        };
    }
}
