<?php

namespace App\Services;

use App\Models\CompanyProfile;
use Illuminate\Support\Str;

/**
 * Build JSON-LD payloads (Google Search Central–oriented).
 */
class SchemaService
{
    /**
     * Sitewide @graph: Organization + WebSite (emit once in layout).
     *
     * @return array<string, mixed>
     */
    public function siteGraph(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $this->organizationNode(),
                $this->websiteNode(),
            ],
        ];
    }

    /**
     * Standalone Organization document (also usable alone).
     *
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        return array_merge(
            ['@context' => 'https://schema.org'],
            $this->organizationNode(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function website(): array
    {
        return array_merge(
            ['@context' => 'https://schema.org'],
            $this->websiteNode(),
        );
    }

    /**
     * Organization node without @context (for @graph).
     *
     * @return array<string, mixed>
     */
    protected function organizationNode(): array
    {
        $site = $this->siteConfig();
        $contact = CompanyProfile::contact();
        $address = is_array($contact['address'] ?? null) && $contact['address'] !== []
            ? $contact['address']
            : (is_array($site['address'] ?? null) ? $site['address'] : []);
        $schemaExtra = is_array($contact['schema'] ?? null) ? $contact['schema'] : [];

        $org = [
            // Dual type: tool tìm "Organization" và "TravelAgency" đều nhận
            '@type' => ['Organization', 'TravelAgency'],
            '@id' => $this->organizationId(),
            'name' => $site['name'] ?? 'ViTravel',
            'legalName' => $contact['legal_name'] ?? ($site['name'] ?? 'ViTravel'),
            'url' => url('/'),
            'slogan' => $this->plainSlogan($contact['slogan'] ?? ($site['tagline'] ?? null)),
            'telephone' => $contact['phone'] ?? ($site['telephone'] ?? null),
            'email' => $contact['email'] ?? ($site['email'] ?? null),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $address['street'] ?? null,
                'addressLocality' => $address['locality'] ?? null,
                'addressRegion' => $address['region'] ?? null,
                'postalCode' => $address['postal'] ?? null,
                'addressCountry' => $address['country'] ?? 'VN',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => $schemaExtra['contact_type'] ?? 'customer service',
                'telephone' => $contact['phone'] ?? ($site['telephone'] ?? null),
                'email' => $contact['email'] ?? ($site['email'] ?? null),
                'availableLanguage' => $schemaExtra['available_language'] ?? ['Vietnamese', 'English'],
            ],
        ];

        if (filled($contact['license'] ?? null)) {
            $org['identifier'] = $contact['license'];
        }

        $logo = $schemaExtra['logo'] ?? ($site['default_og_image'] ?? null);
        if (filled($logo)) {
            $logoUrl = $this->absoluteUrl((string) $logo);
            $org['logo'] = [
                '@type' => 'ImageObject',
                'url' => $logoUrl,
            ];
            $org['image'] = $logoUrl;
        }

        $sameAs = array_values(array_filter($contact['same_as'] ?? ($site['same_as'] ?? [])));
        if ($sameAs !== []) {
            $org['sameAs'] = $sameAs;
        }

        return $this->filterNull($org);
    }

    /**
     * WebSite node without @context (for @graph).
     *
     * @return array<string, mixed>
     */
    protected function websiteNode(): array
    {
        $site = $this->siteConfig();

        return $this->filterNull([
            '@type' => 'WebSite',
            '@id' => $this->websiteId(),
            'name' => $site['name'] ?? site_brand(),
            'url' => url('/'),
            'description' => $site['default_description'] ?? $site['tagline'] ?? null,
            'publisher' => ['@id' => $this->organizationId()],
            'inLanguage' => app()->getLocale() === 'en' ? 'en' : 'vi',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('search').'?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ]);
    }

    protected function organizationId(): string
    {
        return rtrim(url('/'), '/').'#organization';
    }

    protected function websiteId(): string
    {
        return rtrim(url('/'), '/').'#website';
    }

    /**
     * @param  array<int, array{label: string, url?: string|null}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbList(array $items): array
    {
        $elements = [];
        $position = 1;

        $elements[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Trang chủ',
            'item' => route('home'),
        ];

        foreach (array_values($items) as $item) {
            if (empty($item['label'])) {
                continue;
            }

            $entry = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $item['label'],
            ];

            if (! empty($item['url'])) {
                $entry['item'] = $item['url'];
            }

            $elements[] = $entry;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * @param  array<int, array{q: string, a: string}>  $faqs
     * @return array<string, mixed>
     */
    public function faqPage(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_values(array_map(fn (array $faq) => [
                '@type' => 'Question',
                'name' => apply_site_brand($faq['q'] ?? ''),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => apply_site_brand($faq['a'] ?? ''),
                ],
            ], $faqs)),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public function touristTrip(array $item, string $url, bool $isCruise = false): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'TouristTrip',
            'name' => $item['title'] ?? null,
            'description' => $item['highlightsIntro'] ?? ($item['description'] ?? null),
            'url' => $this->absoluteUrl($url),
            'touristType' => $item['styles'] ?? null,
            'provider' => ['@id' => $this->organizationId()],
        ];

        $image = $item['image'] ?? $item['imageDetail'] ?? null;
        if (filled($image)) {
            $schema['image'] = $this->absoluteUrl((string) $image);
        }

        $itineraryCount = is_countable($item['itinerary'] ?? null) ? count($item['itinerary']) : 0;
        if ($itineraryCount > 0) {
            $schema['itinerary'] = [
                '@type' => 'ItemList',
                'numberOfItems' => $itineraryCount,
            ];
        }

        $rating = $item['rating'] ?? null;
        $reviewCount = (int) ($item['reviewCount'] ?? 0);
        if (filled($rating) && $reviewCount > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $rating,
                'reviewCount' => $reviewCount,
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        if ($isCruise) {
            $schema['additionalType'] = 'https://schema.org/Cruise';
        }

        return $this->filterNull($schema);
    }

    /**
     * @param  array<string, mixed>  $article
     * @return array<string, mixed>
     */
    public function article(array $article, string $url): array
    {
        $site = $this->siteConfig();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article['title'] ?? null,
            'description' => $article['excerpt'] ?? null,
            'author' => [
                '@type' => 'Person',
                'name' => $article['author'] ?? ($site['name'] ?? 'ViTravel'),
            ],
            'datePublished' => $this->isoDate($article['publishedAt'] ?? null),
            'dateModified' => $this->isoDate($article['updatedAt'] ?? ($article['publishedAt'] ?? null)),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->absoluteUrl($url),
            ],
            'publisher' => ['@id' => $this->organizationId()],
            'inLanguage' => app()->getLocale() === 'en' ? 'en' : 'vi',
        ];

        $image = $article['imageDetail'] ?? $article['image'] ?? null;
        if (filled($image)) {
            $schema['image'] = [$this->absoluteUrl((string) $image)];
        }

        return $this->filterNull($schema);
    }

    /**
     * @param  array<int, array{city: string, address: string, phone?: string|null}>  $offices
     * @return array<int, array<string, mixed>>
     */
    public function localBusinesses(array $offices): array
    {
        $site = $this->siteConfig();
        $out = [];

        foreach ($offices as $office) {
            $out[] = $this->filterNull([
                '@context' => 'https://schema.org',
                '@type' => 'TravelAgency',
                'name' => ($site['name'] ?? 'ViTravel').' — '.($office['city'] ?? ''),
                'parentOrganization' => ['@id' => $this->organizationId()],
                'telephone' => $office['phone'] ?? null,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $office['address'] ?? null,
                    'addressLocality' => $office['city'] ?? null,
                    'addressCountry' => 'VN',
                ],
            ]);
        }

        return $out;
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function itemList(array $items, ?string $name = null): array
    {
        $elements = [];
        foreach (array_values($items) as $i => $item) {
            $elements[] = $this->filterNull([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'] ?? null,
                'url' => isset($item['url']) ? $this->absoluteUrl($item['url']) : null,
            ]);
        }

        return $this->filterNull([
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'numberOfItems' => count($elements),
            'itemListElement' => $elements,
        ]);
    }

    /**
     * SEO meta + branding — ưu tiên CompanyProfile (DB).
     *
     * @return array<string, mixed>
     */
    protected function siteConfig(): array
    {
        $site = config('seo.site') ?? [];
        $site = is_array($site) ? $site : [];
        $contact = CompanyProfile::contact();
        $brand = filled($contact['name'] ?? null)
            ? (string) $contact['name']
            : (string) ($site['name'] ?? 'ViTravel');

        // CompanyProfile thắng config SEO_SITE_* (tránh WebSite/OG còn «ViTravel» trên domain dự án khác).
        return array_merge($site, [
            'name' => $brand,
            'title_suffix' => $brand,
            'tagline' => filled($contact['tagline'] ?? null)
                ? (string) $contact['tagline']
                : ($site['tagline'] ?? null),
            'telephone' => filled($contact['phone'] ?? null)
                ? (string) $contact['phone']
                : ($site['telephone'] ?? null),
            'email' => filled($contact['email'] ?? null)
                ? (string) $contact['email']
                : ($site['email'] ?? null),
            'address' => is_array($contact['address'] ?? null) && $contact['address'] !== []
                ? $contact['address']
                : (is_array($site['address'] ?? null) ? $site['address'] : []),
            'same_as' => is_array($contact['same_as'] ?? null) && $contact['same_as'] !== []
                ? $contact['same_as']
                : (is_array($site['same_as'] ?? null) ? $site['same_as'] : []),
            'default_og_image' => $contact['schema']['logo']
                ?? ($site['default_og_image'] ?? null),
            'twitter_site' => $site['twitter_site'] ?? null,
            'default_description' => function_exists('seo_default_description')
                ? seo_default_description()
                : apply_site_brand((string) ($site['default_description'] ?? $brand)),
        ]);
    }

    protected function plainSlogan(?string $slogan): ?string
    {
        if (! filled($slogan)) {
            return null;
        }

        return trim(str_replace(['“', '”', '"'], '', $slogan));
    }

    protected function absoluteUrl(string $url): string
    {
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url($url);
    }

    protected function isoDate(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse((string) $value)->toAtomString();
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function filterNull(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->filterNull($value);
                if ($data[$key] === []) {
                    unset($data[$key]);
                }
            } elseif ($value === null || $value === '') {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
