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
            'expiry_date' => $this->normalizeDate($input['expiry_date'] ?? null),
        ];
    }

    /**
     * Classifies a general business document (a commercial registration
     * certificate, a license, a tax card, …) and extracts its key dates —
     * used when a company imports an existing document into the document
     * generator instead of drafting one from a template.
     *
     * @param  array<int, array{path: string, mime: string}>  $images
     * @return array{document_title: ?string, issue_date: ?string, expiry_date: ?string}
     */
    public function extractDocumentInfo(array $images): array
    {
        $tool = [
            'name' => 'extract_document_info',
            'description' => 'Classify a business document and extract its issue and expiry dates.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'document_title' => [
                        'type' => 'string',
                        'description' => 'The Arabic name for what kind of document this is. These Omani government/business documents are common — use the exact label (verbatim, not paraphrased) whenever the document matches it: '
                            .'"سجل تجاري" — a Commercial Registration Certificate: its main content is "Registration Details"/"بيانات السجل التجاري" (Registration Number, Registration Name, Legal Type) plus company-level info like Share Capital and Business Sector. '
                            .'"الترخيص" — a License Certificate (e.g. "Economic Activities License Certificate" / "شهادة ترخيص الأنشطة الاقتصادية"): its main content is a "License Details"/"بيانات الترخيص" section with its own License Number and License Name — note it also nests basic commercial-registration info for context, but that nested info is NOT what makes the document a license. '
                            .'"شهادة الانتساب" — a Membership/Affiliation Certificate (e.g. chamber of commerce membership). '
                            .'"عقد الإيجار" — a rental/lease contract. '
                            .'If the document clearly does not match any of these, use a short Arabic label matching its own printed title instead.',
                    ],
                    'issue_date' => [
                        'type' => 'string',
                        'description' => 'The issue date of THIS document itself (تاريخ الإصدار), formatted as YYYY-MM-DD. Some documents nest a second entity\'s info with its own separate dates (a License Certificate nests the underlying company\'s commercial-registration dates alongside the license\'s own dates) — always use the date belonging to the document\'s PRIMARY subject, matching what you chose for document_title: for "الترخيص" use the license\'s own "Issuing Date" from the "License Details" section, NOT the nested company\'s "Registration Date"; for "سجل تجاري" use the "Registration Date" from "Registration Details"; for other types use whichever date is that document\'s own main issue date. Do not use a company\'s "Establishment Date" (تاريخ التأسيس) as the issue date of a certificate about that company — that is a different date describing when the company itself was founded, not when this document was issued.',
                    ],
                    'expiry_date' => [
                        'type' => 'string',
                        'description' => 'The expiry date of THIS document itself (تاريخ الانتهاء أو الصلاحية), formatted as YYYY-MM-DD — the same "primary subject" rule as issue_date applies: for "الترخيص" use the license\'s own Expiry Date under "License Details", not a nested company\'s registration expiry date.',
                    ],
                ],
                'required' => [],
            ],
        ];

        $content = array_map(fn (array $image) => $this->contentBlock($image), $images);

        $content[] = [
            'type' => 'text',
            'text' => 'Classify this document and extract its issue and expiry dates using the extract_document_info tool. Read the whole document first to identify its primary subject before picking dates — some of these documents show more than one date pair. If a field is genuinely not present anywhere on the document, omit it.',
        ];

        $response = $this->client->send(
            messages: [[
                'role' => 'user',
                'content' => $content,
            ]],
            system: 'You are a precise document-data extraction assistant for a construction company\'s records. Only report what is actually printed on the document.',
            tools: [$tool],
            toolChoice: ['type' => 'tool', 'name' => 'extract_document_info'],
            maxTokens: 512,
        );

        $input = $this->client->toolInputFrom($response, 'extract_document_info') ?? [];

        return [
            'document_title' => $input['document_title'] ?? null,
            'issue_date' => $this->normalizeDate($input['issue_date'] ?? null),
            'expiry_date' => $this->normalizeDate($input['expiry_date'] ?? null),
        ];
    }

    /**
     * The model doesn't always zero-pad the date it's asked for (e.g.
     * "2026-3-9" instead of "2026-03-09"), which a date picker field can
     * fail to parse — normalize in code instead of trusting formatting
     * compliance.
     */
    protected function normalizeDate(?string $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param  array{path: string, mime: string}  $image
     */
    protected function contentBlock(array $image): array
    {
        $base64 = base64_encode(file_get_contents($image['path']));

        if ($image['mime'] === 'application/pdf') {
            return [
                'type' => 'document',
                'source' => [
                    'type' => 'base64',
                    'media_type' => 'application/pdf',
                    'data' => $base64,
                ],
            ];
        }

        return [
            'type' => 'image',
            'source' => [
                'type' => 'base64',
                'media_type' => $image['mime'],
                'data' => $base64,
            ],
        ];
    }
}
