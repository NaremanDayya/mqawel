<?php

namespace App\Services\Ai;

class DocumentExtractionService
{
    public function __construct(protected AnthropicClient $client)
    {
    }

    /**
     * Extract structured identity-document data from one or more uploaded
     * identity document images (e.g. a passport's single page, or an ID
     * card's front and back) — all images are sent together so data can be
     * combined across sides (e.g. a resident/ID card's occupation field is
     * often only on the back).
     *
     * @param  array<int, array{path: string, mime: string}>  $images
     * @return array{document_type: string, full_name: ?string, id_number: ?string, nationality: ?string, job_title: ?string, expiry_date: ?string}
     */
    public function extract(array $images): array
    {
        $tool = [
            'name' => 'extract_identity_document',
            'description' => 'Extract structured data from an identity document image (national ID card or passport).',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'document_type' => [
                        'type' => 'string',
                        'enum' => ['national_id', 'passport', 'other'],
                        'description' => 'The kind of document shown in the image.',
                    ],
                    'full_name' => ['type' => 'string', 'description' => 'The document holder\'s full name, as printed.'],
                    'id_number' => ['type' => 'string', 'description' => 'The person\'s identifying number: for an Omani national ID or resident card, use the "Civil Number" (الرقم المدني) specifically, not any other number printed on the card. For a passport, use the passport number.'],
                    'nationality' => ['type' => 'string', 'description' => 'The document holder\'s nationality.'],
                    'job_title' => ['type' => 'string', 'description' => 'The document holder\'s occupation/profession (المهنة), if printed on the card. On an Omani resident card, the "المهنة" label sits on its own line, and the actual value is printed on the line directly below it (not beside the label like the other fields) — read that line below, not just next to the label.'],
                    'expiry_date' => ['type' => 'string', 'description' => 'The document expiry date, formatted as YYYY-MM-DD.'],
                ],
                'required' => ['document_type'],
            ],
        ];

        $content = array_map(fn (array $image) => [
            'type' => 'image',
            'source' => [
                'type' => 'base64',
                'media_type' => $image['mime'],
                'data' => base64_encode(file_get_contents($image['path'])),
            ],
        ], $images);

        $content[] = [
            'type' => 'text',
            'text' => count($images) > 1
                ? 'These images are the front and back of the same identity document. Extract the combined document data using the extract_identity_document tool. If a field is not visible or not applicable, omit it.'
                : 'Extract the identity document data from this image using the extract_identity_document tool. If a field is not visible or not applicable, omit it.',
        ];

        $response = $this->client->send(
            messages: [[
                'role' => 'user',
                'content' => $content,
            ]],
            system: 'You are a precise document-data extraction assistant for a construction company\'s HR records. Only report what is actually visible in the image.',
            tools: [$tool],
            toolChoice: ['type' => 'tool', 'name' => 'extract_identity_document'],
            maxTokens: 512,
        );

        $input = $this->client->toolInputFrom($response, 'extract_identity_document') ?? [];

        return [
            'document_type' => $input['document_type'] ?? 'other',
            'full_name' => $input['full_name'] ?? null,
            'id_number' => $input['id_number'] ?? null,
            'nationality' => $input['nationality'] ?? null,
            'job_title' => $input['job_title'] ?? null,
            'expiry_date' => $input['expiry_date'] ?? null,
        ];
    }
}
