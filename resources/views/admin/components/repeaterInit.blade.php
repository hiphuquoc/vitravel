@once
@push('scriptCustom')
<script>
(function () {
    function loadRepeaterPlugin(callback) {
        if (typeof window.jQuery !== 'undefined' && typeof jQuery.fn.repeater !== 'undefined') {
            callback();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/jquery.repeater@1.2.1/jquery.repeater.min.js';
        script.onload = callback;
        document.head.appendChild(script);
    }

    function initSortable($repeaterList) {
        if (typeof jQuery.fn.sortable === 'undefined') {
            return;
        }

        if ($repeaterList.hasClass('ui-sortable')) {
            $repeaterList.sortable('destroy');
        }

        $repeaterList.sortable({
            handle: '.adminFormRepeater_item_drag',
            items: '[data-repeater-item]',
            cursor: 'move',
            opacity: 0.7,
            tolerance: 'pointer',
            placeholder: 'adminFormRepeater_item adminFormRepeater_item--placeholder',
            start: function (_e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function () {
                updateRepeaterOrdering($repeaterList);
            },
        });
    }

    function updateRepeaterOrdering($repeaterList) {
        $repeaterList.find('[data-repeater-item]').each(function (index) {
            const $orderingInput = jQuery(this).find('.adminFormRepeater_item_ordering');
            if ($orderingInput.length) {
                $orderingInput.val(index);
            }
        });
    }

    /**
     * jquery.repeater appends [] to checkbox names (treats them like multi-value).
     * PHP then receives is_active as ["1"] which fails Laravel's boolean rule.
     */
    function fixRepeaterCheckboxNames($root) {
        $root.find('input[type="checkbox"][name$="[]"]').each(function () {
            this.name = this.name.replace(/\[\]$/, '');
        });
    }

    function afterRepeaterIndexes($repeaterList) {
        fixRepeaterCheckboxNames($repeaterList);
        initSortable($repeaterList);
        updateRepeaterOrdering($repeaterList);
    }

    function initRepeaters() {
        if (typeof jQuery.fn.repeater === 'undefined') {
            return;
        }

        jQuery('.adminFormSection--repeater').each(function () {
            const $section = jQuery(this);
            if ($section.data('repeater-initialized')) {
                return;
            }

            const $repeaterList = $section.find('[data-repeater-list]').first();
            const $createButton = $section.find('.adminFormSection_header_action').first();
            const $hiddenCreateButton = $section.find('[data-repeater-create]').not('.adminFormSection_header_action').first();

            if (! $repeaterList.length || ! $hiddenCreateButton.length) {
                return;
            }

            $section.find('.adminFormSection_body').first().repeater({
                initEmpty: false,
                show: function () {
                    jQuery(this).slideDown(300);
                    afterRepeaterIndexes($repeaterList);
                },
                hide: function (deleteElement) {
                    jQuery(this).slideUp(300, deleteElement);
                    afterRepeaterIndexes($repeaterList);
                },
                ready: function (setIndexes) {
                    setIndexes();
                    afterRepeaterIndexes($repeaterList);
                },
            });

            // setIndexes() also runs at plugin construct-time (before ready).
            fixRepeaterCheckboxNames($repeaterList);

            $createButton.off('click.repeater').on('click.repeater', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $hiddenCreateButton.trigger('click');
            });

            $section.data('repeater-initialized', true);
        });
    }

    window.initAdminRepeaters = function () {
        loadRepeaterPlugin(function () {
            setTimeout(initRepeaters, 50);
        });
    };

    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(window.initAdminRepeaters);
    } else {
        document.addEventListener('DOMContentLoaded', window.initAdminRepeaters);
    }
})();
</script>
@endpush
@endonce
