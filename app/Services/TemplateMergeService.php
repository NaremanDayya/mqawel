<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * Fills a Word (.docx) template's merge fields with user-provided data.
 * Templates commonly write these fields as {{field_name}} — the natural,
 * human-written convention most people reach for — so that's tried first;
 * PhpWord's own default ${field_name} syntax is supported as a fallback.
 *
 * PDF templates aren't supported here — reliably detecting/filling fields
 * in a PDF requires it to already be a real fillable form (AcroForm),
 * which there's no pure-PHP library for; a flat/scanned PDF has no fill
 * points to detect at all.
 */
class TemplateMergeService
{
    protected const DELIMITER_STYLES = [
        ['{{', '}}'],
        ['${', '}'],
    ];

    public function isFillableWordTemplate(DocumentTemplate $template): bool
    {
        if (blank($template->file)) {
            return false;
        }

        return strtolower(pathinfo($template->file, PATHINFO_EXTENSION)) === 'docx'
            && Storage::disk('public')->exists($template->file);
    }

    /**
     * @return array<int, string>
     */
    public function getPlaceholders(DocumentTemplate $template): array
    {
        return $this->detect($template)[1];
    }

    /**
     * Merges the given values into the template's placeholders and saves
     * the filled document as a new file, returning its storage path.
     *
     * @param  array<string, string>  $values
     */
    public function merge(DocumentTemplate $template, array $values): string
    {
        [$processor, $placeholders] = $this->detect($template);

        foreach ($placeholders as $placeholder) {
            $processor->setValue($placeholder, $values[$placeholder] ?? '');
        }

        $newPath = 'documents/'.Str::uuid().'.docx';
        $processor->saveAs(Storage::disk('public')->path($newPath));

        return $newPath;
    }

    /**
     * @return array{0: ?TemplateProcessor, 1: array<int, string>}
     */
    protected function detect(DocumentTemplate $template): array
    {
        if (! $this->isFillableWordTemplate($template)) {
            return [null, []];
        }

        foreach (self::DELIMITER_STYLES as [$open, $close]) {
            try {
                $processor = new TemplateProcessor(Storage::disk('public')->path($template->file));
                $processor->setMacroChars($open, $close);
                $placeholders = $processor->getVariables();

                if (! empty($placeholders)) {
                    return [$processor, $placeholders];
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return [null, []];
    }
}
