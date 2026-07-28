<?php

namespace App\Filament\Resources\StorageItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\ItemMovement;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStorageItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function afterCreate(): void {
        $record= $this->record;

        $previous_storage_quantity= 0;
        $new_storage_quantity= 0;

        if($record->storage){
            //must calculate previous and new storage quantities.
        }

        $Movement= new ItemMovement();
        $Movement->company_id= Auth::user()->company_id;
        $Movement->storage_id= $record->storage_id;
        $Movement->project_id= null;
        $Movement->item_id= $record->id;
        $Movement->type= 'in';
        $Movement->quantity= $record->quantity;
        $Movement->previous_storage_quantity= $previous_storage_quantity;
        $Movement->new_storage_quantity= $new_storage_quantity;
        $Movement->movement_date= date('Y-m-d', strtotime($record->created_at));
        $Movement->notes= null;
        $Movement->address= $record->storage ? $record->storage->name : null;
        $Movement->created_by= Auth::user()->id;
        $Movement->save();

        $Users= Auth::user()->company->users;

        if($Users){
            foreach($Users as $User){
                $message= '';

                if($User->locale == 'en'){
                    $message= 'New storage item "'.$record->name.'" added to the company records';
                }
                else if($User->locale == 'ar'){
                    $message= 'تمت اضافة عنصر مخزن جديد'.' "'.$record->name.'" '.'الى سجل الشركة';
                }

                Notification::make()->title($message)->sendToDatabase($User);
            }
        }

        Notification::make()->title(__('backend.item_movement_success_message'))->success()->send();
    }
}
