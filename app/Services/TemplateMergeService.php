<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * Fills a Word (.docx) template's ${placeholder} merge fields with
 * user-provided data. PDF templates aren't supported here — reliably
 * detecting/filling fields in a PDF requires it to already be a real
 * fillable form (AcroForm), which there's no pure-PHP library for; a
 * flat/scanned PDF has no fill points to detect at all.
 */
class TemplateMergeService
{
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
        if (! $this->isFillableWordTemplate($template)) {
            return [];
        }

        try {
            $processor = new TemplateProcessor(Storage::disk('public')->path($template->file));

            return $processor->getVariables();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Merges the given values into the template's placeholders and saves
     * the filled document as a new file, returning its storage path.
     *
     * @param  array<string, string>  $values
     */
    public function merge(DocumentTemplate $template, array $values): string
    {
        $processor = new TemplateProcessor(Storage::disk('public')->path($template->file));

        foreach ($processor->getVariables() as $variable) {
            $processor->setValue($variable, $values[$variable] ?? '');
        }

        $newPath = 'documents/'.Str::uuid().'.docx';
        $processor->saveAs(Storage::disk('public')->path($newPath));

        return $newPath;
    }
}
