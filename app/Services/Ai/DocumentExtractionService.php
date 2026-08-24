<?php

namespace App\Services\Ai;

class DocumentExtractionService
{
    public function __construct(protected AnthropicClient $client)
    {
    }

    /**
     * Extract structured identity-document data from an uploaded ID card or passport image.
     *
     * @return array{document_type: string, full_name: ?string, id_number: ?string, nationality: ?string, expiry_date: ?string}
     */
    public function extract(string $absolutePath, string $mimeType): array
    {
        $base64 = base64_encode(file_get_contents($absolutePath));

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
                    'expiry_date' => ['type' => 'string', 'description' => 'The document expiry date, formatted as YYYY-MM-DD.'],
                ],
                'required' => ['document_type'],
            ],
        ];

        $response = $this->client->send(
            messages: [[
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image',
                        'source' => [
                            'type' => 'base64',
                            'media_type' => $mimeType,
                            'data' => $base64,
                        ],
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Extract the identity document data from this image using the extract_identity_document tool. If a field is not visible or not applicable, omit it.',
                    ],
                ],
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
            'expiry_date' => $input['expiry_date'] ?? null,
        ];
    }
}
