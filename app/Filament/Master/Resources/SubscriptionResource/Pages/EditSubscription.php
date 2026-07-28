<?php

namespace App\Filament\Master\Resources\SubscriptionResource\Pages;

use App\Filament\Master\Resources\SubscriptionResource;
use App\Models\SubscriptionPackage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void {
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
    }
}
