@props([
    'title' => 'Thư viện hình ảnh',
])

<template x-teleport="body">
    <div>
        {{-- 1. GIAI ĐOẠN 1: DRAWER GRID FULLHEIGHT (RESPONSIVE CHUẨN ĐẸP TỪ MOBILE ĐẾN DESKTOP) --}}
        <div
            class="vt-gallery-drawer"
            x-show="drawerOpen && viewerActive === null"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            role="dialog"
            aria-modal="true"
            aria-label="Thư viện hình ảnh"
            style="position: fixed; inset: 0; width: 100vw; height: 100vh; height: 100dvh; z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(10px); padding: 0 clamp(0.5rem, 1.5vw, 1.5rem);"
            @keydown.escape.window="if (drawerOpen && viewerActive === null) close()"
        >
            <div
                class="vt-gallery-drawer__panel"
                style="width: 100%; max-width: 96rem; margin-left: auto; margin-right: auto; height: calc(100% - clamp(0.5rem, 1.5vh, 1.25rem)); background: #fff; display: flex; flex-direction: column; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6); overflow: hidden; border-radius: 1rem 1rem 0 0;"
                @click.stop
            >
                {{-- Header Drawer Responsive --}}
                <header style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; background: #fff; z-index: 10; flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: 0.65rem; min-width: 0; flex: 1;">
                        <h3 style="margin: 0; font-size: clamp(1rem, 2.5vw, 1.25rem); font-weight: 700; color: #1e293b; letter-spacing: -0.01em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $title }}
                        </h3>
                        <span
                            style="display: inline-flex; align-items: center; padding: 0.15rem 0.55rem; border-radius: 999px; background: rgba(15, 118, 110, 0.1); border: 1px solid rgba(15, 118, 110, 0.25); color: #0f766e; font-size: 0.78rem; font-weight: 700; flex-shrink: 0;"
                        >
                            <span x-text="items.length"></span> ảnh
                        </span>
                    </div>

                    <button
                        type="button"
                        @click="close()"
                        aria-label="Đóng thư viện"
                        style="display: flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border-radius: 999px; border: 1px solid #cbd5e1; background: #f8fafc; color: #334155; cursor: pointer; transition: all 0.2s; flex-shrink: 0; margin-left: 0.5rem;"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="18" height="18">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </header>

                {{-- Body Drawer: Responsive Grid Skeleton (1 cột mobile -> 2 cột tablet -> 4 cột desktop) --}}
                <div
                    class="vt-scrollbar"
                    style="flex: 1 1 auto; height: 100%; min-height: 0; overflow-y: auto; overflow-x: hidden; padding: clamp(0.75rem, 2vw, 1.5rem); background: #f8fafc;"
                >
                    <template x-if="drawerOpen">
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 18rem), 1fr)); gap: 1rem; padding-bottom: 2.5rem;">
                            <template x-for="(item, idx) in items" :key="idx">
                                <div
                                    @click="openViewer(idx)"
                                    style="position: relative; aspect-ratio: 16 / 10; border-radius: 0.55rem; overflow: hidden; background: #e2e8f0; cursor: pointer; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s;"
                                    class="group hover:shadow-xl hover:-translate-y-0.5"
                                >
                                    {{-- Skeleton placeholder --}}
                                    <div
                                        style="position: absolute; inset: 0; background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%); background-size: 200% 100%; animation: vtSkeleton 1.5s infinite;"
                                    ></div>
                                    
                                    {{-- Image Lazy Load --}}
                                    <img
                                        :src="item.src"
                                        :alt="item.title || ''"
                                        loading="lazy"
                                        decoding="async"
                                        referrerpolicy="no-referrer"
                                        style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
                                        class="group-hover:scale-105"
                                        @load="$event.target.previousElementSibling.style.display = 'none'"
                                    />

                                    {{-- Hover Overlay & Title --}}
                                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(15,23,42,0.65) 0%, transparent 45%); opacity: 0; transition: opacity 0.2s; display: flex; align-items: flex-end; padding: 0.75rem;" class="group-hover:opacity-100">
                                        <span style="color: #fff; font-size: 0.825rem; font-weight: 600; text-shadow: 0 1px 3px rgba(0,0,0,0.8); line-height: 1.2;" x-text="item.title || 'Xem ảnh phóng to'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- 2. GIAI ĐOẠN 2: CHẾ ĐỘ XEM FULLSCREEN SLIDER ĐẲNG CẤP VỚI KHUNG RESPONSIVE TUYỆT HẢO --}}
        <div
            class="vt-fullscreen-viewer"
            x-show="viewerActive !== null"
            x-cloak
            x-transition.opacity.duration.250ms
            @keydown.escape.window="closeViewer()"
            @keydown.arrow-left.window="if (viewerActive !== null) prev()"
            @keydown.arrow-right.window="if (viewerActive !== null) next()"
            role="dialog"
            aria-modal="true"
            style="position: fixed; inset: 0; z-index: 10000; display: flex; flex-direction: column; background: #07090e; color: #fff;"
        >
            {{-- Top Bar: Responsive, Tiêu đề không bị tràn đè nút Close --}}
            <header style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem clamp(0.75rem, 2vw, 1.5rem); background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.08); z-index: 10; flex-shrink: 0; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0; flex: 1;">
                    <button
                        type="button"
                        @click="closeViewer()"
                        style="display: flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; border-radius: 0.45rem; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.08); color: #e2e8f0; font-size: 0.825rem; font-weight: 500; cursor: pointer; transition: all 0.2s; flex-shrink: 0;"
                        onmouseover="this.style.background='rgba(255,255,255,0.16)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.08)'"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><polyline points="15 18 9 12 15 6"/></svg>
                        <span class="hidden sm:inline">Tất cả ảnh</span>
                        <span class="sm:hidden">Lưới</span>
                    </button>
                    <span style="font-size: 0.9rem; font-weight: 600; color: #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" x-text="activeItem?.title || '{{ $title }}'"></span>
                </div>

                <div style="display: flex; align-items: center; gap: 0.65rem; flex-shrink: 0;">
                    <span style="font-size: 0.825rem; font-weight: 700; color: #38bdf8; background: rgba(56, 189, 248, 0.12); padding: 0.2rem 0.55rem; border-radius: 99px; border: 1px solid rgba(56, 189, 248, 0.2);" x-text="activeLabel"></span>
                    <button
                        type="button"
                        @click="close()"
                        aria-label="Đóng toàn bộ"
                        style="display: flex; align-items: center; justify-content: center; width: 2.15rem; height: 2.15rem; border-radius: 999px; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.08); color: #fff; cursor: pointer; transition: all 0.2s;"
                        onmouseover="this.style.background='rgba(239, 68, 68, 0.6)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.08)'"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="16" height="16">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            </header>

            {{-- Main Stage: Ảnh lớn ở giữa & Nút chuyển Prev / Next co giãn chuẩn mobile --}}
            <div style="position: relative; flex: 1 1 auto; min-height: 0; display: flex; align-items: center; justify-content: center; padding: 0.5rem clamp(0.5rem, 4vw, 4.5rem);">
                {{-- Nút Prev --}}
                <button
                    type="button"
                    @click="prev()"
                    aria-label="Ảnh trước"
                    style="position: absolute; left: clamp(0.4rem, 1.5vw, 1.25rem); top: 50%; transform: translateY(-50%); display: flex; align-items: center; justify-content: center; width: clamp(2.35rem, 5vw, 3.25rem); height: clamp(2.35rem, 5vw, 3.25rem); border-radius: 999px; border: 1px solid rgba(255,255,255,0.15); background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); color: #fff; cursor: pointer; transition: all 0.2s; z-index: 5;"
                    onmouseover="this.style.background='rgba(56, 189, 248, 0.85)'; this.style.transform='translateY(-50%) scale(1.06)'"
                    onmouseout="this.style.background='rgba(15, 23, 42, 0.7)'; this.style.transform='translateY(-50%) scale(1)'"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20"><polyline points="15 18 9 12 15 6"/></svg>
                </button>

                {{-- Khung Ảnh lớn --}}
                <template x-if="viewerActive !== null && activeItem?.src">
                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; user-select: none; padding: 0.25rem;">
                        <img
                            :src="activeItem.full || activeItem.src"
                            :alt="activeItem.title || ''"
                            style="max-width: 100%; max-height: calc(100vh - 12rem); object-fit: contain; border-radius: 0.5rem; box-shadow: 0 25px 50px rgba(0,0,0,0.9);"
                            decoding="async"
                            referrerpolicy="no-referrer"
                        />
                    </div>
                </template>

                {{-- Nút Next --}}
                <button
                    type="button"
                    @click="next()"
                    aria-label="Ảnh sau"
                    style="position: absolute; right: clamp(0.4rem, 1.5vw, 1.25rem); top: 50%; transform: translateY(-50%); display: flex; align-items: center; justify-content: center; width: clamp(2.35rem, 5vw, 3.25rem); height: clamp(2.35rem, 5vw, 3.25rem); border-radius: 999px; border: 1px solid rgba(255,255,255,0.15); background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); color: #fff; cursor: pointer; transition: all 0.2s; z-index: 5;"
                    onmouseover="this.style.background='rgba(56, 189, 248, 0.85)'; this.style.transform='translateY(-50%) scale(1.06)'"
                    onmouseout="this.style.background='rgba(15, 23, 42, 0.7)'; this.style.transform='translateY(-50%) scale(1)'"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>

            {{-- Bottom Thumbnail Strip: Responsive, Tự động căn giữa theo ảnh đang xem --}}
            <footer
                style="height: clamp(3.85rem, 8vh, 4.75rem); flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: rgba(11, 15, 25, 0.95); border-top: 1px solid rgba(255,255,255,0.08); padding: 0 0.5rem;"
            >
                <div style="display: flex; align-items: center; justify-content: center; gap: clamp(0.25rem, 1vw, 0.5rem); max-width: 100%; overflow-x: auto;" class="vt-scrollbar">
                    <template x-for="thumb in visibleThumbnails" :key="'thumb-' + thumb.index">
                        <button
                            type="button"
                            @click="selectViewer(thumb.index)"
                            :style="thumb.isActive ? 'border-color: #38bdf8; transform: scale(1.08); box-shadow: 0 0 10px rgba(56, 189, 248, 0.6); opacity: 1;' : 'border-color: rgba(255,255,255,0.15); opacity: 0.45;'"
                            style="position: relative; width: clamp(3.25rem, 7vw, 4.2rem); height: clamp(2.2rem, 5vh, 2.75rem); flex: 0 0 auto; border-radius: 0.35rem; border-width: 1.5px; border-style: solid; overflow: hidden; background: #1e293b; padding: 0; cursor: pointer; transition: all 0.2s ease;"
                            onmouseover="if (this.style.opacity !== '1') this.style.opacity='0.85'"
                            onmouseout="if (this.style.borderColor !== 'rgb(56, 189, 248)') this.style.opacity='0.45'"
                        >
                            <img
                                :src="thumb.item.src"
                                :alt="thumb.item.title || ''"
                                loading="lazy"
                                decoding="async"
                                referrerpolicy="no-referrer"
                                style="width: 100%; height: 100%; object-fit: cover;"
                            />
                            <span
                                style="position: absolute; bottom: 1px; right: 2px; font-size: 0.55rem; font-weight: 700; color: #fff; background: rgba(0,0,0,0.65); padding: 0 2px; border-radius: 2px; line-height: 1.2;"
                                x-text="thumb.index + 1"
                            ></span>
                        </button>
                    </template>
                </div>
            </footer>
        </div>
    </div>
</template>

<style>
@keyframes vtSkeleton {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>
