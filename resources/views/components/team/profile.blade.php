@props(['member'])

@php
    $rating = (float) ($member['rating'] ?? 5);
    $fullStars = (int) floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $statClients = !empty($member['stat_clients']) ? ((int) $member['stat_clients']).'+' : '--';
    $statTours = !empty($member['stat_tours']) ? ((int) $member['stat_tours']).'+' : '--';
    $statAwards = !empty($member['stat_awards']) ? ((int) $member['stat_awards']).'+' : '--';
@endphp

<div class="team-profile">
    <div class="team-profile-container">
        <div class="container-site">
            <div class="team-profile-grid">
                {{-- Sidebar --}}
                <aside class="profile-sidebar">
                    <div class="sidebar-card profile-card">
                        <div class="profile-image-wrapper">
                            @if (!empty($member['image']))
                                <img
                                    src="{{ $member['image'] }}"
                                    @if (!empty($member['imageSrcset'])) srcset="{{ $member['imageSrcset'] }}" @endif
                                    alt="{{ $member['name'] ?? 'Thành viên' }}"
                                    class="profile-image"
                                    loading="eager"
                                >
                            @else
                                <div class="profile-image profile-image--placeholder" aria-hidden="true">
                                    <x-icon name="user" class="size-16" />
                                </div>
                            @endif
                            @if (!empty($member['is_verified']))
                                <div class="status-badge">
                                    <x-icon name="check-badge" class="size-3.5" />
                                    Đã xác minh
                                </div>
                            @endif
                        </div>
                        <div class="profile-info">
                            <h1 class="profile-name">{{ $member['name'] ?? 'Thành viên' }}</h1>
                            <p class="profile-title">{{ $member['role'] ?? '' }}</p>

                            <div class="profile-rating" aria-label="Đánh giá {{ $rating }}/5">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $fullStars)
                                        <x-icon name="star" class="size-4 star-full" />
                                    @elseif ($halfStar && $i === $fullStars + 1)
                                        <x-icon name="star" class="size-4 star-half" />
                                    @else
                                        <x-icon name="star" class="size-4 star-empty" />
                                    @endif
                                @endfor
                                <span>({{ number_format($rating, 1) }}/5)</span>
                            </div>

                            <div class="profile-actions">
                                <a href="{{ locale_route('contact') }}" class="btn-primary">Liên hệ</a>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-card info-card">
                        <h3 class="card-title">Thông tin cá nhân</h3>
                        <ul class="info-list">
                            <li>
                                <div class="icon-wrap"><x-icon name="map-pin" class="size-5" /></div>
                                <div class="info-content">
                                    <span class="label">Khu vực</span>
                                    <span class="value">{{ $member['area'] ?: 'Việt Nam' }}</span>
                                </div>
                            </li>
                            <li>
                                <div class="icon-wrap"><x-icon name="briefcase" class="size-5" /></div>
                                <div class="info-content">
                                    <span class="label">Kinh nghiệm</span>
                                    <span class="value">
                                        @if (!empty($member['years_experience']))
                                            {{ $member['years_experience'] }}+ Năm
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                            </li>
                            <li>
                                <div class="icon-wrap"><x-icon name="globe" class="size-5" /></div>
                                <div class="info-content">
                                    <span class="label">Ngôn ngữ</span>
                                    <span class="value">{{ $member['languages'] ?: '—' }}</span>
                                </div>
                            </li>
                            @if (!empty($member['email']))
                                <li>
                                    <div class="icon-wrap"><x-icon name="mail" class="size-5" /></div>
                                    <div class="info-content">
                                        <span class="label">Email</span>
                                        <span class="value text-break">
                                            <a href="mailto:{{ $member['email'] }}">{{ $member['email'] }}</a>
                                        </span>
                                    </div>
                                </li>
                            @endif
                            @if (!empty($member['phone']))
                                <li>
                                    <div class="icon-wrap"><x-icon name="phone" class="size-5" /></div>
                                    <div class="info-content">
                                        <span class="label">Điện thoại</span>
                                        <span class="value">
                                            <a href="tel:{{ preg_replace('/\s+/', '', $member['phone']) }}">{{ $member['phone'] }}</a>
                                        </span>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </aside>

                {{-- Main --}}
                <div class="profile-content">
                    <div class="content-box about-section">
                        <h2 class="section-title"><x-icon name="user" class="size-5" /> Giới thiệu</h2>
                        <div class="content-text">
                            @if (!empty($member['bio_html']))
                                {!! $member['bio_html'] !!}
                            @elseif (!empty($member['short_bio']))
                                <p>{{ $member['short_bio'] }}</p>
                            @else
                                <p class="text-muted">Đang cập nhật giới thiệu…</p>
                            @endif
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="icon-box"><x-icon name="users" class="size-6" /></div>
                            <div class="stat-info">
                                <span class="number">{{ $statClients }}</span>
                                <span class="label">Khách đồng hành</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="icon-box"><x-icon name="route" class="size-6" /></div>
                            <div class="stat-info">
                                <span class="number">{{ $statTours }}</span>
                                <span class="label">Tour dẫn dắt</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="icon-box"><x-icon name="trophy" class="size-6" /></div>
                            <div class="stat-info">
                                <span class="number">{{ $statAwards }}</span>
                                <span class="label">Giải thưởng</span>
                            </div>
                        </div>
                    </div>

                    <div class="content-box achievement-section">
                        <h2 class="section-title"><x-icon name="medal" class="size-5" /> Thành tích nổi bật</h2>
                        <ul class="achievement-list">
                            @forelse ($member['achievements'] ?? [] as $achievement)
                                <li class="achievement-item">
                                    <div class="achievement-icon"><x-icon name="star" class="size-4" /></div>
                                    <div class="achievement-content">{!! $achievement['content'] !!}</div>
                                </li>
                            @empty
                                <p class="text-muted">Đang cập nhật thành tích…</p>
                            @endforelse
                        </ul>
                    </div>

                    <div class="content-box skills-section-main">
                        <h2 class="section-title"><x-icon name="bolt" class="size-5" /> Kỹ năng chuyên môn</h2>
                        <div class="skills-grid">
                            @forelse ($member['skills'] ?? [] as $skill)
                                <div class="skill-item-main">
                                    <div class="skill-header">
                                        <span class="skill-name">{{ $skill['skill'] }}</span>
                                        <span class="skill-percent">{{ $skill['percent'] }}%</span>
                                    </div>
                                    <div class="progress-bar-main">
                                        <div class="fill" style="width: {{ max(0, min(100, (int) $skill['percent'])) }}%" data-percent="{{ $skill['percent'] }}"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">Đang cập nhật kỹ năng…</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="content-box degree-section">
                        <h2 class="section-title"><x-icon name="graduation-cap" class="size-5" /> Bằng cấp &amp; Chứng chỉ</h2>
                        <div class="degree-list">
                            @forelse ($member['degrees'] ?? [] as $degree)
                                <div class="degree-item">
                                    <div class="degree-icon"><x-icon name="certificate" class="size-6" /></div>
                                    <div class="degree-info">
                                        <h3 class="degree-title">{{ $degree['title'] }}</h3>
                                        @if (!empty($degree['school']))
                                            <p class="degree-school">{{ $degree['school'] }}</p>
                                        @endif
                                        @if (!empty($degree['items']))
                                            <div class="degree-details">
                                                @foreach ($degree['items'] as $line)
                                                    <div class="detail-line">{!! $line !!}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">Đang cập nhật bằng cấp…</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="content-box experience-section">
                        <h2 class="section-title"><x-icon name="briefcase" class="size-5" /> Kinh nghiệm làm việc</h2>
                        <div class="timeline-content">
                            @forelse ($member['experiences'] ?? [] as $exp)
                                <div class="timeline-item">
                                    <div class="timeline-header">
                                        <h3 class="timeline-title">{{ $exp['title'] }}</h3>
                                        @if (!empty($exp['company']))
                                            <span class="timeline-company">
                                                <x-icon name="building" class="size-3.5" />
                                                {{ $exp['company'] }}
                                            </span>
                                        @endif
                                    </div>
                                    @if (!empty($exp['items']))
                                        <ul class="timeline-details">
                                            @foreach ($exp['items'] as $line)
                                                <li>{!! $line !!}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted">Đang cập nhật kinh nghiệm…</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="content-box gallery-section">
                        <h2 class="section-title"><x-icon name="photo" class="size-5" /> Hình ảnh hoạt động</h2>
                        @if (!empty($member['activity_images']))
                            <div class="gallery-grid">
                                @foreach ($member['activity_images'] as $img)
                                    <a href="{{ $img['url'] }}" class="gallery-item" target="_blank" rel="noopener" title="Hình ảnh hoạt động">
                                        <img src="{{ $img['thumb'] ?? $img['url'] }}" alt="Hình ảnh hoạt động" loading="lazy">
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">Chưa có hình ảnh hoạt động.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
