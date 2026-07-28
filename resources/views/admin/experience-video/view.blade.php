@extends('layouts.admin')

@section('title', $title)

@section('content')
<form id="formAction" action="{{ route('admin.videos.save') }}" method="POST" enctype="multipart/form-data" class="adminFormPage_form" onsubmit="showFullLoading()">
    @csrf
    <input type="hidden" name="id" value="{{ $video?->id }}">
    <input type="hidden" name="language" value="{{ $locale }}">

    <div class="adminFormPage">
        <div class="adminFormPage_content">
            @include('admin.components.pageHeader', [
                'title' => $title,
                'desc' => 'Upload file video hoặc gắn YouTube/Vimeo + ảnh đại diện + nội dung đa ngôn ngữ.',
                'icon' => '<polygon points="5 3 19 12 5 21 5 3"/>',
                'backUrl' => route('admin.videos.list'),
                'backText' => 'Quay lại',
            ])

            <div class="adminFormPage_languageSwitcher">
                @include('admin.components.formLanguageSwitcher', [
                    'item' => $video ?? new \App\Models\ExperienceVideo(),
                    'language' => $locale,
                    'routeName' => 'admin.videos.view',
                    'routeParams' => ['id' => $video?->id],
                ])
            </div>

            @if ($errors->any())
                <div class="adminFormPage_errors">
                    <div class="adminFormPage_errors_content">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                </div>
            @endif

            <div class="adminFormPage_body">
                <div class="adminFormPage_main">
                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Nguồn video</h2>
                                <p class="adminFormSection_description">Ưu tiên file upload. Nếu không có file, dùng YouTube / Vimeo / link MP4.</p>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            <div class="adminFormVideoUpload" style="margin-bottom:1.25rem;">
                                <label class="adminFormField_label" for="video_file_input">Upload file video</label>
                                <input
                                    type="file"
                                    id="video_file_input"
                                    name="video_file"
                                    accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.m4v"
                                    class="adminFormField_input"
                                    style="padding:0.55rem;"
                                >
                                <p class="adminFormField_hint" style="margin-top:0.5rem;">
                                    MP4 / WebM / MOV — tối đa {{ $videoMaxLabel }}. Nếu PHP báo lỗi dung lượng, tăng <code>upload_max_filesize</code> &amp; <code>post_max_size</code>.
                                </p>
                                @if ($video?->videoFile)
                                    <div style="margin-top:0.85rem;padding:0.85rem 1rem;border:1px solid #ddd9c2;border-radius:10px;background:#f8f6ec;">
                                        <p style="margin:0 0 0.5rem;font-size:0.875rem;font-weight:600;">
                                            File hiện tại: {{ $video->videoFile->filename }}
                                            @if ($video->videoFile->size_bytes)
                                                <span style="font-weight:500;color:#817d6e;">({{ number_format($video->videoFile->size_bytes / 1048576, 1) }} MB)</span>
                                            @endif
                                        </p>
                                        <video src="{{ $video->videoFileUrl() }}" controls playsinline style="width:100%;max-height:220px;border-radius:8px;background:#111;"></video>
                                        <label style="display:flex;align-items:center;gap:0.5rem;margin-top:0.65rem;font-size:0.875rem;cursor:pointer;">
                                            <input type="checkbox" name="remove_video_file" value="1">
                                            Xóa file video đã upload
                                        </label>
                                    </div>
                                @endif
                                @error('video_file')
                                    <div class="adminFormField_error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="adminFormGrid adminFormGrid--2cols">
                                @include('admin.components.formField', [
                                    'label' => 'YouTube ID / URL (tuỳ chọn)',
                                    'name' => 'youtube_id',
                                    'value' => old('youtube_id', $video?->youtube_id),
                                    'placeholder' => 'VD: dQw4w9WgXcQ hoặc https://youtu.be/…',
                                    'helpText' => 'Dùng khi không upload file.',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'URL khác (Vimeo / MP4 ngoài)',
                                    'name' => 'video_url',
                                    'value' => old('video_url', $video?->video_url),
                                    'placeholder' => 'https://vimeo.com/… hoặc link .mp4',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Thời lượng',
                                    'name' => 'duration',
                                    'value' => old('duration', $video?->duration),
                                    'placeholder' => '12:40',
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Nhãn ngắn (tile)',
                                    'name' => 'tag',
                                    'value' => old('tag', $video?->tag),
                                    'placeholder' => 'VD: Sa Pa · Mùa lúa',
                                    'tooltip' => 'Hiển thị cạnh số thứ tự trên dải video trang chủ.',
                                ])
                            </div>
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Nội dung ({{ strtoupper($locale) }})</h2>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            @include('admin.components.formField', [
                                'label' => 'Tiêu đề',
                                'name' => 'title',
                                'required' => true,
                                'value' => old('title', $translation?->title),
                                'placeholder' => 'VD: Một đêm trên du thuyền vịnh Lan Hạ',
                            ])
                            @include('admin.components.formField', [
                                'label' => 'Mô tả ngắn',
                                'name' => 'description',
                                'type' => 'textarea',
                                'rows' => 3,
                                'value' => old('description', $translation?->description),
                                'placeholder' => 'Mô tả hiển thị trong lightbox…',
                            ])
                        </div>
                    </div>

                    <div class="adminFormSection">
                        <div class="adminFormSection_header">
                            <div class="adminFormSection_header_info">
                                <h2 class="adminFormSection_title">Cấu hình</h2>
                            </div>
                        </div>
                        <div class="adminFormSection_body">
                            <div class="adminFormGrid adminFormGrid--2cols">
                                @php
                                    $countryOptions = ['' => '— Không gắn —'];
                                    foreach ($countries as $c) {
                                        $countryOptions[$c->id] = $c->name ?: ('#'.$c->id);
                                    }
                                @endphp
                                @include('admin.components.formField', [
                                    'label' => 'Quốc gia',
                                    'name' => 'country_id',
                                    'type' => 'select',
                                    'value' => old('country_id', $video?->country_id),
                                    'options' => $countryOptions,
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Thứ tự',
                                    'name' => 'sort',
                                    'type' => 'number',
                                    'value' => old('sort', $video?->sort ?? 0),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Trạng thái',
                                    'name' => 'status',
                                    'type' => 'select',
                                    'value' => old('status', $video?->status ?? 'published'),
                                    'options' => ['published' => 'Published', 'draft' => 'Draft'],
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Ngày xuất bản',
                                    'name' => 'published_at',
                                    'type' => 'date',
                                    'value' => old('published_at', optional($video?->published_at)->format('Y-m-d')),
                                ])
                                @include('admin.components.formField', [
                                    'label' => 'Trang chủ',
                                    'name' => 'show_on_home',
                                    'type' => 'checkbox',
                                    'value' => old('show_on_home', $video?->show_on_home ?? true),
                                    'checkboxLabel' => 'Hiển thị trên dải video trang chủ',
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="adminFormPage_sidebar">
                    <div class="adminFormSidebar">
                        <div class="adminFormSidebar_sticky">
                            @include('admin.components.formActions', [
                                'backRoute' => 'admin.videos.list',
                            ])
                            @include('admin.components.formImageUpload', [
                                'name' => 'image',
                                'label' => 'Ảnh đại diện',
                                'currentImage' => $video?->thumbnail?->id
                                    ? $video->thumbnailUrl('card')
                                    : null,
                                'removeName' => 'remove_image',
                                'aspectRatio' => '16/9',
                                'maxKb' => $uploadMaxKb,
                                'hint' => 'Dùng làm thumbnail trên trang chủ. Để trống sẽ lấy ảnh YouTube nếu có ID. Tối đa '.$uploadMaxLabel.'.',
                                'tooltip' => 'Ảnh đại diện / thumbnail của video.',
                            ])
                            @if ($video)
                                <div class="mt-3">
                                    <a href="{{ route('admin.videos.delete', ['id' => $video->id]) }}"
                                       class="adminFormActions_button adminFormActions_button--secondary"
                                       style="color:#b91c1c;width:100%;justify-content:center;"
                                       onclick="return confirm('Xóa video này?')">
                                        Xóa video
                                    </a>
                                </div>
                                @if ($video->embedUrl())
                                    <div class="mt-4" style="aspect-ratio:16/9;border-radius:8px;overflow:hidden;background:#111;">
                                        @if ($video->provider() === 'file')
                                            <video src="{{ $video->embedUrl() }}" controls playsinline style="width:100%;height:100%;object-fit:contain;"></video>
                                        @else
                                            <iframe src="{{ str_replace('autoplay=1', 'autoplay=0', $video->embedUrl()) }}" title="Preview" style="width:100%;height:100%;border:0;" allowfullscreen loading="lazy"></iframe>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
