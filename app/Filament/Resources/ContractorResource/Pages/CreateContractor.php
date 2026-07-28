<?php

namespace App\Filament\Resources\ContractorResource\Pages;

use App\Filament\Resources\ContractorResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateContractor extends CreateRecord
{
    protected static string $resource = ContractorResource::class;

    public function afterCreate(): void {
        $record= $this->record;

        $Users= Auth::user()->company->users;

        if($Users){
            foreach($Users as $User){
                $message= '';

                if($User->locale == 'en'){
                    $message= 'New contractor "'.$record->name.'" added to the company records';
                }
                else if($User->locale == 'ar'){
                    $message= 'تمت اضافة مقاول جديد'.' "'.$record->name.'" '.'الى سجل الشركة';
                }

                Notification::make()->title($message)->sendToDatabase($User);
            }
        }
    }
}
