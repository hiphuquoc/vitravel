@props([
    'inclusions' => [],
    'exclusions' => [],
    'notes' => [],
    'sectionId' => 'bao-gom',
    'embedded' => false,
])

@if (count($inclusions) || count($exclusions) || count($notes))
    @if ($embedded)
        <div class="detail-cover">
    @else
    <section
        id="{{ $sectionId }}"
        class="detail-section"
        aria-label="Bao gồm, không bao gồm và lưu ý"
    >
        <div class="detail-cover">
    @endif
            @if (count($inclusions))
                <div class="detail-cover__box detail-cover__box--in">
                    <h2 class="detail-cover__head">
                        <span class="detail-cover__badge detail-cover__badge--in" aria-hidden="true">
                            <x-icon name="check" class="size-3.5" />
                        </span>
                        Bao gồm
                    </h2>
                    <ul class="detail-cover__list">
                        @foreach ($inclusions as $inc)
                            <li class="detail-cover__row">
                                <span class="detail-cover__mark detail-cover__mark--in" aria-hidden="true">
                                    <x-icon name="check" class="size-3.5" />
                                </span>
                                <span class="detail-cover__text">{{ $inc }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (count($exclusions))
                <div class="detail-cover__box detail-cover__box--out">
                    <h2 class="detail-cover__head">
                        <span class="detail-cover__badge detail-cover__badge--out" aria-hidden="true">
                            <x-icon name="x-mark" class="size-3.5" />
                        </span>
                        Không bao gồm
                    </h2>
                    <ul class="detail-cover__list">
                        @foreach ($exclusions as $exc)
                            <li class="detail-cover__row">
                                <span class="detail-cover__mark detail-cover__mark--out" aria-hidden="true">
                                    <x-icon name="x-mark" class="size-3.5" />
                                </span>
                                <span class="detail-cover__text">{{ $exc }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (count($notes))
                <div class="detail-cover__box detail-cover__box--note">
                    <h2 class="detail-cover__head">
                        <span class="detail-cover__badge detail-cover__badge--note" aria-hidden="true">
                            <x-icon name="flag" class="size-3.5" />
                        </span>
                        Lưu ý
                    </h2>
                    <ul class="detail-cover__list">
                        @foreach ($notes as $note)
                            <li class="detail-cover__row">
                                <span class="detail-cover__mark detail-cover__mark--note" aria-hidden="true">
                                    <span class="detail-cover__dot"></span>
                                </span>
                                <span class="detail-cover__text">{{ $note }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @if (! $embedded)
    </section>
    @endif
@endif
