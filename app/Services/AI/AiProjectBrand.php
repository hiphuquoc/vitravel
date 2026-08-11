<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\CompanyProfile;
use App\Support\ProjectContext;

/**
 * Thương hiệu / project code cho prompt AI — theo ProjectContext admin hiện tại.
 */
final class AiProjectBrand
{
    /**
     * @return array{brand: string, project_code: string}
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
        ];
    }
}
