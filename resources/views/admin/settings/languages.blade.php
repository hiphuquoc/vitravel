@extends('layouts.admin')
@section('title', 'Ngôn ngữ')
@section('content')
<div class="adminContentPage">
    <div class="adminContentPage_content">
        @include('admin.components.pageHeader', [
            'title' => 'Ngôn ngữ',
            'desc' => 'Danh sách từ bảng languages. Cập nhật qua config/language.php rồi chạy seeder hoặc migration seed.',
            'icon' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        ])
        <div class="companyManagementPage_section">
            <div class="companyManagementPage_section_body">
                @if ($languages->isNotEmpty())
                    <div class="adminContentPage_tableWrap" style="overflow-x:auto;">
                        <table class="adminContentPage_table" style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th style="text-align:left;padding:0.5rem;">Code</th>
                                    <th style="text-align:left;padding:0.5rem;">Name</th>
                                    <th style="text-align:left;padding:0.5rem;">Native</th>
                                    <th style="text-align:left;padding:0.5rem;">Flag</th>
                                    <th style="text-align:left;padding:0.5rem;">Active</th>
                                    <th style="text-align:left;padding:0.5rem;">Default</th>
                                    <th style="text-align:left;padding:0.5rem;">Sort</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($languages as $lang)
                                    <tr>
                                        <td style="padding:0.5rem;"><strong>{{ strtoupper($lang->code) }}</strong></td>
                                        <td style="padding:0.5rem;">{{ $lang->name }}</td>
                                        <td style="padding:0.5rem;">{{ $lang->name_native }}</td>
                                        <td style="padding:0.5rem;">
                                            @if ($lang->flag)
                                                <img src="{{ asset(ltrim($lang->flag, '/')) }}" alt="" width="24" height="24" style="border-radius:50%;">
                                            @endif
                                        </td>
                                        <td style="padding:0.5rem;">{{ $lang->is_active ? '✓' : '—' }}</td>
                                        <td style="padding:0.5rem;">{{ $lang->is_default ? '✓' : '—' }}</td>
                                        <td style="padding:0.5rem;">{{ $lang->sort }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="adminContentPage_empty">
                        Chưa có ngôn ngữ. Chạy: <code>php artisan db:seed --class=LanguageSeeder</code>
                        hoặc <code>php artisan migrate</code>.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
