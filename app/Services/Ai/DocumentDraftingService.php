<?php

namespace App\Services\Ai;

use App\Models\GeneratedDocument;

class DocumentDraftingService
{
    public function __construct(protected AnthropicClient $client)
    {
    }

    public function draft(GeneratedDocument $document): string
    {
        $categoryLabels = [
            'contracts' => 'a construction contract',
            'quotes' => 'a price quote',
            'letters' => 'a formal business letter',
            'correspondence' => 'client/supplier correspondence',
            'minutes' => 'meeting/handover minutes',
        ];

        $context = array_filter([
            'Document type' => $document->name,
            'Category' => $categoryLabels[$document->category] ?? $document->category,
            'Template' => $document->template?->name,
            'Related project' => $document->project?->name,
            'Project location' => $document->project?->address,
            'Related party' => $document->related_party,
            'Value' => $document->value ? $document->value.' OMR' : null,
            'Additional details' => $document->details,
        ]);

        $contextText = collect($context)
            ->map(fn ($value, $key) => "{$key}: {$value}")
            ->implode("\n");

        $response = $this->client->send(
            messages: [[
                'role' => 'user',
                'content' => "Draft the full body text of {$categoryLabels[$document->category]} using the details below. Write in professional Arabic, formatted as clean Markdown with clear sections/headings where appropriate. Only output the document body — no preamble, no explanation.\n\n{$contextText}",
            ]],
            system: 'You are a professional business-document writer for a construction/contracting company operating in Oman. You draft clear, formal Arabic documents (contracts, quotes, letters, correspondence, meeting minutes) ready for a manager to review and send.',
            maxTokens: 2048,
        );

        return $this->client->textFrom($response) ?? '';
    }
}
