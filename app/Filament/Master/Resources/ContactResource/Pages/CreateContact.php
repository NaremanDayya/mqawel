<?php

namespace App\Filament\Master\Resources\ContactResource\Pages;

use App\Filament\Master\Resources\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
