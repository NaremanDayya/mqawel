<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CompanyResource;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function show(Request $request)
    {
        return new CompanyResource($request->user()->company);
    }

    public function update(Request $request)
    {
        $company = $request->user()->company;

        $data = $request->validate([
            'logo' => ['nullable', 'image', 'max:10240'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'business_number' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'address' => ['nullable', 'string', 'max:255'],
            'activity' => ['nullable', 'string', 'max:255'],
            'services' => ['nullable', 'array'],
            'services.*' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            $data['picture'] = $request->file('logo')->store('form-attachments', 'public');
        }
        unset($data['logo']);

        $company->update($data);

        return new CompanyResource($company->refresh());
    }
}
