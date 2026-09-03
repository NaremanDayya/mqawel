<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ContractorResource;
use App\Models\Contractor;
use Illuminate\Http\Request;

class ContractorController extends Controller
{
    public function index(Request $request)
    {
        $contractors = Contractor::where('company_id', $request->user()->company_id)
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return ContractorResource::collection($contractors);
    }

    public function show(Request $request, int $contractor)
    {
        $record = Contractor::where('company_id', $request->user()->company_id)->findOrFail($contractor);

        return new ContractorResource($record);
    }
}
