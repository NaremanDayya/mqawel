<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FileResource;
use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $files = File::where('company_id', $request->user()->company_id)
            ->when($request->filled('parent_table'), fn ($query) => $query->where('parent_table', $request->string('parent_table')))
            ->when($request->filled('parent_id'), fn ($query) => $query->where('parent_id', $request->integer('parent_id')))
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return FileResource::collection($files);
    }

    public function show(Request $request, int $file)
    {
        $record = File::where('company_id', $request->user()->company_id)->findOrFail($file);

        return new FileResource($record);
    }
}
