<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Concerns\HasMockupPageHeader;
use App\Filament\Concerns\HasSectionNotificationSettings;
use App\Filament\Resources\UserResource;
use App\Models\CompanyRole;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ListUsers extends ListRecords
{
    use HasMockupPageHeader;
    use HasSectionNotificationSettings;

    protected static string $resource = UserResource::class;

    protected function pageSubtitle(): ?string
    {
        return __('backend.users_page_subtitle');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->notificationSettingsAction('users', __('backend.users')),

            Actions\CreateAction::make()
                ->label(__('backend.add_user'))
                ->icon('heroicon-o-plus')
                ->modalHeading(__('backend.add_user'))
                ->modalDescription(__('backend.add_user_description'))
                ->modalSubmitActionLabel(__('backend.add_user'))
                ->form([
                    FileUpload::make('picture')
                        ->label(__('backend.picture'))
                        ->avatar()
                        ->directory('form-attachments'),

                    TextInput::make('name')
                        ->label(__('backend.name'))
                        ->required(),

                    Grid::make(2)->schema([
                        TextInput::make('email')
                            ->label(__('backend.email'))
                            ->email()
                            ->required()
                            ->unique('users', 'email')
                            ->validationMessages([
                                'unique' => __('backend.unavailable_to_use'),
                            ]),

                        TextInput::make('phone')
                            ->label(__('backend.phone'))
                            ->tel(),
                    ]),

                    TextInput::make('role_name')
                        ->label(__('backend.role'))
                        ->required()
                        ->datalist(fn () => CompanyRole::query()
                            ->where('company_id', Auth::user()->company_id)
                            ->pluck('name')
                            ->all())
                        ->helperText(__('backend.role_hint')),

                    TextInput::make('password')
                        ->label(__('backend.password'))
                        ->required()
                        ->maxLength(255)
                        ->default(fn () => Str::password(10, symbols: false))
                        ->helperText(__('backend.generated_password_hint'))
                        ->suffixAction(
                            FormAction::make('regeneratePassword')
                                ->icon('heroicon-o-arrow-path')
                                ->action(fn (Set $set) => $set('password', Str::password(10, symbols: false))),
                        ),

                    Section::make(__('backend.permissions'))->schema([
                        Toggle::make('can_view')
                            ->label(__('backend.view'))
                            ->helperText(__('backend.view_hint'))
                            ->onColor('success')
                            ->default(true),

                        Toggle::make('can_manage')
                            ->label(__('backend.manage'))
                            ->helperText(__('backend.manage_hint'))
                            ->onColor('success')
                            ->default(false),
                    ]),
                ])
                ->using(function (array $data): User {
                    $companyId = Auth::user()->company_id;

                    $role = CompanyRole::query()
                        ->where('company_id', $companyId)
                        ->where('name', $data['role_name'])
                        ->first();

                    if (! $role) {
                        $permissionColumns = collect((new CompanyRole())->getFillable())
                            ->filter(fn (string $column): bool => str_starts_with($column, 'can_read_')
                                || str_starts_with($column, 'can_write_')
                                || str_starts_with($column, 'can_edit_'));

                        $permissions = $permissionColumns->mapWithKeys(fn (string $column) => [
                            $column => str_starts_with($column, 'can_read_') ? true : (bool) $data['can_manage'],
                        ]);

                        $role = CompanyRole::create([
                            'company_id' => $companyId,
                            'name' => $data['role_name'],
                            'created_by' => Auth::user()->id,
                            ...$permissions->toArray(),
                        ]);
                    }

                    return User::create([
                        'company_id' => $companyId,
                        'created_by' => Auth::user()->id,
                        'role_id' => $role->id,
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? null,
                        'picture' => $data['picture'] ?? null,
                        'password' => bcrypt($data['password']),
                        'is_active' => true,
                    ]);
                })
                ->after(function (User $record) {
                    foreach (Auth::user()->company->users ?? [] as $user) {
                        $message = $user->locale === 'en'
                            ? 'New user "'.$record->name.'" added to the company records'
                            : 'تمت اضافة مستخدم جديد "'.$record->name.'" الى سجل الشركة';

                        Notification::make()->title($message)->sendToDatabase($user);
                    }
                }),
        ];
    }
}
