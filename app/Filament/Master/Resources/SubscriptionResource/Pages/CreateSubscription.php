<?php

namespace App\Filament\Master\Resources\SubscriptionResource\Pages;

use App\Filament\Master\Resources\SubscriptionResource;
use App\Models\Master;
use App\Models\SubscriptionPackage;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    public function afterCreate(): void {
        $record= $this->record;

        $Package= SubscriptionPackage::find($record->package_id);

        if($Package){
            $package_period= $Package->period;
            $package_price= $Package->price;
            $package_currency= $Package->currency;

            $record->period= $package_period;
            $record->ending_date= date('Y-m-d', strtotime('+ '.$package_period.' month', strtotime($record->starting_date)));
            $record->price= $package_price;
            $record->currency= $package_currency;
            $record->save();
        }

        $Masters= Master::where('is_active', 1)->get();

        if($Masters){
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
        }
    }
}
