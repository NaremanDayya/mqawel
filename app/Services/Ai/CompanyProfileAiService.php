<?php

namespace App\Services\Ai;

use App\Models\Company;

class CompanyProfileAiService
{
    public function __construct(protected AnthropicClient $client)
    {
    }

    /**
     * Draft/refresh the company's "نبذة عن الشركة" paragraph from its
     * structured profile facts. Returned text is only ever shown to the
     * admin for review — never saved automatically.
     */
    public function draftAbout(Company $company, array $featuredProjectNames = []): string
    {
        $facts = array_filter([
            'Company name' => $company->name,
            'Activity / specialty' => $company->activity,
            'Location' => $company->address,
            'Founded year' => $company->founded_year,
            'Services offered' => $company->services ? implode(', ', $company->services) : null,
            'Featured completed projects' => $featuredProjectNames ? implode(', ', $featuredProjectNames) : null,
        ]);

        $factsText = collect($facts)
            ->map(fn ($value, $key) => "{$key}: {$value}")
            ->implode("\n");

        $response = $this->client->send(
            messages: [[
                'role' => 'user',
                'content' => "Write a short, professional \"about us\" paragraph in Arabic (3-4 sentences) for this construction/contracting company's public profile, based only on the facts below. Do not invent facts not listed. Output only the paragraph — no preamble.\n\n{$factsText}",
            ]],
            system: 'You are a professional business copywriter for construction/contracting companies in Oman, writing concise Arabic company profile bios.',
            maxTokens: 512,
        );

        return trim($this->client->textFrom($response) ?? '');
    }
}
