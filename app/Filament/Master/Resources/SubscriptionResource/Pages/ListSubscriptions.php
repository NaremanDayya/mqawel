<?php

namespace App\Filament\Master\Resources\SubscriptionResource\Pages;

use App\Filament\Master\Resources\SubscriptionResource;
use App\Models\Master;
use App\Models\SubscriptionPackage;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->after(function ($record): void {
                $Package= SubscriptionPackage::find($record->package_id);

                if($Package){
                    $record->period= $Package->period;
                    $record->ending_date= date('Y-m-d', strtotime('+ '.$Package->period.' month', strtotime($record->starting_date)));
                    $record->price= $Package->price;
                    $record->currency= $Package->currency;
                    $record->save();
                }

                $Masters= Master::where('is_active', 1)->get();

                foreach($Masters as $Master){
                    $message= '';

                    if($Master->locale == 'en'){
                        $message= 'New subscription added to the company "'.$record->company->name.'"';
                    }
                    else if($Master->locale == 'ar'){
                        $message= 'تمت اضافة اشتراك جديد الى شركة '.'"'.$record->company->name.'"';
                    }

                    Notification::make()->title($message)->sendToDatabase($Master);
                }
            }),
        ];
    }
}
