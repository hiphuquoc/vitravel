<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\CompanyProfile;
use App\Support\ProjectContext;

/**
 * Thương hiệu / project code / bối cảnh AI cho prompt — theo ProjectContext admin hiện tại.
 */
final class AiProjectBrand
{
    /**
     * @return array{brand: string, project_code: string, project_brief: string}
     */
    public static function vars(): array
    {
        $contact = CompanyProfile::contact();
        $brand = trim((string) ($contact['name'] ?? ''));
        $project = ProjectContext::get();

        if ($brand === '') {
            $brand = trim((string) ($project?->name ?? ''));
        }

        $code = ProjectContext::code() ?? '';

        if ($brand === '') {
            $brand = $code !== '' ? $code : 'ViTravel';
        }

        return [
            'brand' => $brand,
            'project_code' => $code,
            'project_brief' => SeoPromptRules::clipProjectBrief(self::briefRaw()),
        ];
    }

    public static function briefRaw(): string
    {
        $project = ProjectContext::get();
        $config = is_array($project?->config) ? $project->config : [];

        return trim((string) ($config['ai_brief'] ?? ''));
    }
}
