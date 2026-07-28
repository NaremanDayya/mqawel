<?php

namespace App\Filament\Pages\Auth;

use App\Models\Company;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Register extends BaseRegister{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make(__('backend.profile'))
                        ->icon('heroicon-o-user')
                        ->schema([
                            $this->getNameFormComponent(),
                            $this->getEmailFormComponent(),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                            /*Select::make('role')->options([
                                '1' => 'Admin - مسؤول شامل',
                            ])->required()->label(__('backend.role')),*/
                        ]),
                    Wizard\Step::make(__('backend.company'))
                        ->icon('heroicon-o-building-office-2')
                        ->schema([
                            TextInput::make('company_name')->required()->maxLength(255)->label(__('backend.company_name')),
                            TextInput::make('company_phone')->maxLength(255)->label(__('backend.company_phone')),
                            TextInput::make('company_email')->maxLength(255)->label(__('backend.company_email')),
                            TextInput::make('company_website')->maxLength(255)->label(__('backend.company_website')),
                            TextInput::make('company_activity')->required()->maxLength(255)->label(__('backend.company_activity')),
                            TextInput::make('company_business_number')->required()->maxLength(255)->label(__('backend.company_business_number')),
                            TextInput::make('company_tax_number')->required()->maxLength(255)->label(__('backend.company_tax_number')),
                        ]),
                ])
                // Add the register button to the end of the wizard
                //->submitAction(view('filament.pages.auth.register-button')),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        return DB::transaction(function() use($data){
            $User = User::create([
                'role_id' => 1,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $Company= Company::create([
                'name' => $data['company_name'],
                'email' => $data['company_email'],
                'phone' => $data['company_phone'],
                'website' => $data['company_website'],
                'business_number' => $data['company_business_number'],
                'tax_number' => $data['company_tax_number'],
                'activity' => $data['company_activity'],
            ]);

            $Company->refresh();

            $User->company_id= $Company->id;
            $User->save();
            $User->refresh();

            return $User;
        });
    }
}