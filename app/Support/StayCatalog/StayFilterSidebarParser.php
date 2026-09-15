<?php

declare(strict_types=1);

namespace App\Support\StayCatalog;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Parse sidebar filter Booking từ HTML.
 * Bám data-testid / data-filters-group / data-filters-item / input[name] — không class hash.
 */
final class StayFilterSidebarParser
{
    /**
     * @return array{
     *   dest: array{ss: ?string, dest_id: ?string, dest_type: ?string, label: ?string},
     *   filters: list<array<string, mixed>>,
     *   groups: list<string>
     * }
     */
    public function parse(string $html, string $listUrl = ''): array
    {
        $dest = StayIdentity::fromUrl($listUrl);
        $out = [
            'dest' => [
                'ss' => $dest['ss'],
                'dest_id' => $dest['dest_id'],
                'dest_type' => $dest['dest_type'],
                'label' => $dest['ss'],
            ],
            'filters' => [],
            'groups' => [],
        ];
        $html = trim($html);
        if ($html === '') {
            return $out;
        }

        $dom = new DOMDocument;
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $xpath = new DOMXPath($dom);
        $seenNflt = [];
        $groups = [];

        foreach ($xpath->query('//*[@data-filters-group]') ?: [] as $groupNode) {
            if (! $groupNode instanceof DOMElement) {
                continue;
            }
            $group = trim($groupNode->getAttribute('data-filters-group'));
            if ($group === '') {
                continue;
            }
            $groups[$group] = true;
            foreach ($xpath->query('.//*[@data-filters-item]', $groupNode) ?: [] as $itemNode) {
                if (! $itemNode instanceof DOMElement) {
                    continue;
                }
                $parsed = $this->parseItem($xpath, $itemNode, $group, $listUrl);
                if ($parsed === null) {
                    continue;
                }
                $key = $parsed['nflt'];
                if (isset($seenNflt[$key])) {
                    continue;
                }
                $seenNflt[$key] = true;
                $out['filters'][] = StayFilterPolicy::decorate($parsed);
            }
        }

        $out['filters'] = StayFilterPolicy::dedupeStayTypePages($out['filters']);
        $out['groups'] = array_keys($groups);

        return $out;
    }

    /**
     * @param  list<array<string, mixed>>  $rawFilters  pack Chrome (chưa policy)
     * @return list<array<string, mixed>>
     */
    public function decoratePack(array $rawFilters, string $listUrl = ''): array
    {
        $items = [];
        $seen = [];
        foreach ($rawFilters as $raw) {
            if (! is_array($raw)) {
                continue;
            }
            $group = (string) ($raw['group'] ?? $raw['key'] ?? '');
            $nflt = (string) ($raw['nflt'] ?? $raw['name'] ?? '');
            if ($nflt === '' && isset($raw['id']) && $group !== '') {
                $nflt = $group.'='.$raw['id'];
            }
            if ($nflt === '') {
                continue;
            }
            if (isset($seen[$nflt])) {
                continue;
            }
            $seen[$nflt] = true;
            $filterUrl = (string) ($raw['filter_url'] ?? $raw['url'] ?? '');
            if ($filterUrl === '' && $listUrl !== '') {
                $filterUrl = StayIdentity::withNflt($listUrl, $nflt);
            }
            $items[] = StayFilterPolicy::decorate([
                'group' => $group,
                'nflt' => $nflt,
                'name' => (string) ($raw['name'] ?? $nflt),
                'label' => (string) ($raw['label'] ?? $nflt),
                'count' => (int) ($raw['count'] ?? 0),
                'filter_url' => $filterUrl,
                'expanded' => (bool) ($raw['expanded'] ?? false),
            ]);
        }

        return StayFilterPolicy::dedupeStayTypePages($items);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseItem(DOMXPath $xpath, DOMElement $itemNode, string $group, string $listUrl): ?array
    {
        $nflt = '';
        $label = '';
        $count = 0;
        foreach ($xpath->query('.//input[@name]', $itemNode) ?: [] as $input) {
            if (! $input instanceof DOMElement) {
                continue;
            }
            $nflt = trim($input->getAttribute('name') ?: $input->getAttribute('value'));
            $aria = trim($input->getAttribute('aria-label'));
            if ($aria !== '') {
                if (preg_match('/^(.*?):\s*([\d.]+)\s*(chỗ nghỉ|properties|stays|chỗ)/iu', $aria, $m)) {
                    $label = trim($m[1]);
                    $count = (int) str_replace('.', '', $m[2]);
                } else {
                    $label = $aria;
                }
            }
            break;
        }
        if ($nflt === '') {
            $raw = trim($itemNode->getAttribute('data-filters-item'));
            if (str_contains($raw, ':')) {
                $nflt = trim(explode(':', $raw, 2)[1]);
            }
        }
        if ($nflt === '') {
            return null;
        }
        if ($label === '') {
            $label = StayText::collapse($itemNode->textContent ?? '');
            $label = preg_replace('/\s*\d[\d.]*\s*(chỗ nghỉ|properties).*$/iu', '', $label) ?? $label;
            $label = StayText::collapse($label);
        }

        return [
            'group' => $group,
            'nflt' => $nflt,
            'name' => $nflt,
            'label' => $label !== '' ? $label : $nflt,
            'count' => $count,
            'filter_url' => $listUrl !== '' ? StayIdentity::withNflt($listUrl, $nflt) : '',
            'expanded' => true,
        ];
    }
}
