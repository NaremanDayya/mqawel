<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiRequestException;
use App\Services\Ai\AssistantService;
use App\Services\Ai\CompanyProfileAiService;
use App\Services\Ai\DocumentExtractionService;
use App\Services\CompanyProfileInsights;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class AiController extends Controller
{
    public function chat(Request $request, AssistantService $assistant)
    {
        $data = $request->validate([
            'messages' => ['required', 'array', 'min:1'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required'],
        ]);

        try {
            $reply = $assistant->respond($request->user()->company_id, $data['messages']);
        } catch (AiRequestException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        $messages = $data['messages'];
        $messages[] = ['role' => 'assistant', 'content' => $reply];

        return response()->json(['data' => ['reply' => $reply, 'messages' => $messages]]);
    }

    public function workerScan(Request $request, DocumentExtractionService $service)
    {
        $data = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:2'],
            'images.*' => ['image', 'max:10240'],
        ]);

        try {
            $result = $service->extract($this->imagePayload($data['images']));
        } catch (AiRequestException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json(['data' => $result]);
    }

    public function fileScan(Request $request, DocumentExtractionService $service)
    {
        $data = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        try {
            $result = $service->extractDocumentInfo($this->imagePayload($data['images']));
        } catch (AiRequestException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json(['data' => $result]);
    }

    public function improveCompanyAbout(Request $request, CompanyProfileAiService $service, CompanyProfileInsights $insights)
    {
        $company = $request->user()->company;

        try {
            $about = $service->draftAbout($company, $insights->featuredProjects($company)->pluck('name')->all());
        } catch (AiRequestException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json(['data' => ['about' => $about]]);
    }

    /**
     * @param  array<int, UploadedFile>  $images
     * @return array<int, array{path: string, mime: string}>
     */
    private function imagePayload(array $images): array
    {
        return array_map(fn (UploadedFile $image) => [
            'path' => $image->getRealPath(),
            'mime' => $image->getMimeType(),
        ], $images);
    }
}
