@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => $title,
            'desc' => 'Người đại diện tại nước ngoài trên trang Về chúng tôi.',
            'icon' => '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'actionUrl' => route('admin.referencePersons.view'),
            'actionText' => 'Thêm đại diện',
        ])
        <div class="companyManagementPage_section">
            <div class="companyManagementPage_section_body">
                @if ($persons->isNotEmpty())
                    <div class="adminContentPage_grid">
                        @foreach ($persons as $person)
                            <div class="adminContentPage_card">
                                <div class="adminContentPage_card_body">
                                    <h3 class="adminContentPage_card_title">{{ $person->name }}</h3>
                                    <p class="adminContentPage_card_desc">{{ $person->country?->name }} · {{ $person->email }} · #{{ $person->sort }}</p>
                                    <div style="margin-top:0.75rem;display:flex;gap:0.5rem;">
                                        <a href="{{ route('admin.referencePersons.view', ['id' => $person->id]) }}" class="adminFormActions_button">Sửa</a>
                                        <a href="{{ route('admin.referencePersons.delete', ['id' => $person->id]) }}" class="adminFormActions_button adminFormActions_button--secondary" onclick="return confirm('Xóa?')">Xóa</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="adminContentPage_empty">Chưa có đại diện.</div>
                @endif
                @if ($persons->hasPages())
                    <div class="adminContentPage_pagination">{{ $persons->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
