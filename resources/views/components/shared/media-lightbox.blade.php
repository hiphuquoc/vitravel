{{-- Lightbox full-view dùng chung video + ảnh (home showcase & trang thư viện). --}}
<template x-teleport="body">
    <div
        class="vt-videos-lightbox"
        x-show="active !== null"
        x-cloak
        x-transition.opacity.duration.200ms
        @keydown.escape.window="close()"
        @keydown.arrow-left.window="if (active !== null) prev()"
        @keydown.arrow-right.window="if (active !== null) next()"
        role="dialog"
        aria-modal="true"
        :aria-label="activeItem?.title || 'Xem đầy đủ'"
    >
        <div class="vt-videos-lightbox__backdrop" @click="close()"></div>
        <div class="vt-videos-lightbox__panel">
            <button type="button" class="vt-videos-lightbox__close" @click="close()" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
            <button type="button" class="vt-videos-lightbox__nav vt-videos-lightbox__nav--prev" @click="prev()" aria-label="Trước" x-show="playlist.length > 1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button type="button" class="vt-videos-lightbox__nav vt-videos-lightbox__nav--next" @click="next()" aria-label="Tiếp" x-show="playlist.length > 1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><polyline points="9 18 15 12 9 6"/></svg>
            </button>

            <div class="vt-videos-lightbox__stage">
                {{-- Video embed (YouTube / Vimeo…) --}}
                <template x-if="active !== null && activeItem?.type !== 'image' && activeItem?.embedUrl && activeItem?.provider !== 'file'">
                    <iframe
                        class="vt-videos-lightbox__frame"
                        :src="activeItem.embedUrl"
                        :title="activeItem.title"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </template>
                {{-- Video file --}}
                <template x-if="active !== null && activeItem?.type !== 'image' && activeItem?.provider === 'file' && activeItem?.embedUrl">
                    <video class="vt-videos-lightbox__frame" :src="activeItem.embedUrl" controls autoplay playsinline></video>
                </template>
                {{-- Ảnh --}}
                <template x-if="active !== null && (activeItem?.type === 'image' || (!activeItem?.embedUrl && activeItem?.src))">
                    <figure class="vt-videos-lightbox__photo">
                        <template x-if="activeItem?.src">
                            <img
                                class="vt-videos-lightbox__frame vt-videos-lightbox__frame--photo"
                                :src="activeItem.src"
                                :srcset="activeItem.srcset || null"
                                :alt="activeItem.title || ''"
                                sizes="(max-width: 1024px) 94vw, 1100px"
                            >
                        </template>
                        <template x-if="!activeItem?.src">
                            <div class="vt-videos-lightbox__photo-ph" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                                    <circle cx="8.5" cy="10.5" r="1.5"/>
                                    <path d="M21 16l-5-5-4 4-2-2-5 5"/>
                                </svg>
                            </div>
                        </template>
                    </figure>
                </template>
            </div>

            <div class="vt-videos-lightbox__info" x-show="activeItem">
                <p class="vt-videos-lightbox__index" x-text="activeLabel"></p>
                <h3 class="vt-videos-lightbox__title" x-text="activeItem?.title"></h3>
                <p class="vt-videos-lightbox__desc" x-show="activeItem?.description || activeItem?.caption" x-text="activeItem?.description || activeItem?.caption"></p>
            </div>
        </div>
    </div>
</template>
