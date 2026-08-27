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
                    'job_title' => ['type' => 'string', 'description' => 'The text printed under the "المهنة" (occupation) label on an Omani resident/ID card, usually the very last line of Arabic text near the bottom of the card, below the person\'s name. Unlike every other field on the card, its value is NOT beside the "المهنة" label — it is printed on the line directly underneath that label. Transcribe whatever Arabic text appears on that line exactly as printed, even if it is a long phrase, or a sponsorship/relationship note (e.g. "إلحاق بالأقارب لـ ...") rather than a conventional job title — any text on that line counts and must not be skipped. Only leave this out if that line is genuinely blank or the card has no "المهنة" label at all (e.g. a passport).'],
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
            'text' => (count($images) > 1
                ? 'These images are the front and back of the same identity document. Extract the combined document data using the extract_identity_document tool.'
                : 'Extract the identity document data from this image using the extract_identity_document tool.')
                .' Read every field carefully, including job_title (المهنة) — on an Omani ID card its value is on the line below the label, not beside it, so check that line specifically before deciding the field is blank. If a field is genuinely not present on the document, omit it.',
        ];

        $response = $this->client->send(
            messages: [[
                'role' => 'user',
                'content' => $content,
            ]],
            system: 'You are a precise document-data extraction assistant for a construction company\'s HR records. Only report what is actually visible in the image, but read the whole document carefully before leaving an optional field out — do not skip a field just because its value looks unusual.',
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
