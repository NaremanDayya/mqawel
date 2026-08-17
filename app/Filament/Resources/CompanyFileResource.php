<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyFileResource\Pages;
use App\Filament\Resources\CompanyFileResource\RelationManagers;
use App\Models\File;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyFileResource extends Resource
{
    protected static ?string $model = File::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';

    protected static ?int $navigationSort= 2;

    /*public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('company_id', Auth::user()->company_id)->where('parent_table', 'companies')->count() > 0 ? static::getModel()::where('company_id', Auth::user()->company_id)->where('parent_table', 'companies')->count() : '';
    }*/

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),

                Hidden::make('created_by')->default(Auth::user()->id),

                Hidden::make('parent_table')->default('companies'),

                Hidden::make('parent_id')->default(Auth::user()->company_id),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')->unique(ignoreRecord:true, modifyRuleUsing: function($rule, $context, $record){
                            return $rule->where('company_id', Auth::user()->company_id);
                        })->validationMessages([
                            'unique' => __('backend.unavailable_to_use'),
                        ])->required()->label(__('backend.name')),
                        DatePicker::make('issue_date')->label(__('backend.issue_date')),

                        Forms\Components\Select::make('validity_type')->options([
                            'permanent' => __('backend.validity_type_permanent'),
                            'temporary' => __('backend.validity_type_temporary'),
                        ])->default('permanent')->label(__('backend.validity_type')),

                        DatePicker::make('expiry_date')->required()->label(__('backend.expiry_date')),
                        Forms\Components\Select::make('category')
                            ->label(__('backend.file_category'))
                            ->options([
                                'certificate' => __('backend.file_category_certificate'),
                                'work_photo' => __('backend.file_category_work_photo'),
                                'general' => __('backend.file_category_general'),
                            ])
                            ->default('general')
                            ->required(),
                        MarkdownEditor::make('description')->columnSpanFull()->label(__('backend.description')),
                    ])->columns(2),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        FileUpload::make('file')->directory(fn () => 'documents/'.\Illuminate\Support\Str::uuid())->preserveFilenames()->maxSize(10240)->helperText(__('backend.max_file_size_10_mb'))->openable()->downloadable()->required()->label(__('backend.document')),
                    ]),

                    Section::make()->schema([
                        Toggle::make('is_active')->default(true)->label(__('backend.active')),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function($query){
                $query->where('company_id', Auth::user()->company_id)->where('company_id', '<>', null);
            })
            ->description('⚠️ '.__('backend.max_file_size_10_mb'))
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                TextColumn::make('name')
                    ->searchable()
                    ->label(__('backend.name'))
                    ->description(fn (File $record): ?string => $record->file
                        ? strtoupper(pathinfo($record->file, PATHINFO_EXTENSION)) ?: null
                        : null),
                TextColumn::make('related_entity')
                    ->label(__('backend.party'))
                    ->state(fn (File $record): ?string => match ($record->parent_table) {
                        'companies' => $record->parentCompany?->name,
                        'projects' => $record->parentProject?->name,
                        'workers' => $record->parentWorker?->name,
                        default => null,
                    }),
                TextColumn::make('expiry_date')
                    ->label(__('backend.expiry_date'))
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(function (File $record): string {
                        if (! $record->expiry_date) {
                            return '';
                        }

                        $daysUntilExpiry = now()->diffInDays($record->expiry_date, false);

                        return $daysUntilExpiry < 0
                            ? __('backend.expired_since_days', ['days' => abs((int) $daysUntilExpiry)])
                            : __('backend.valid');
                    })
                    ->color(fn (File $record): string => ($record->expiry_date && now()->greaterThan($record->expiry_date)) ? 'danger' : 'success')
                    ->description(fn (File $record): ?string => $record->expiry_date ? \Carbon\Carbon::parse($record->expiry_date)->format('d-m-Y') : null),
                TextColumn::make('validity_type')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'temporary' => __('backend.validity_type_temporary'),
                    default => __('backend.validity_type_permanent'),
                })->badge()->toggleable(isToggledHiddenByDefault: true)->label(__('backend.validity_type')),
            ])
            ->actionsColumnLabel(__('backend.actions'))
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
                Tables\Actions\Action::make('download_file')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->tooltip(__('backend.download'))
                    ->iconButton()
                    ->color('success')
                    ->action(function(array $data, $record) : StreamedResponse {
                        $filePath= 'public/'.$record->file;

                        if(!Storage::exists($filePath)){
                            abort(404, __('backend.file_not_found'));
                        }

                        return Storage::download($filePath, $record->downloadFilename());
                    })->label(__('backend.download')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanyFiles::route('/'),
            'edit' => Pages\EditCompanyFile::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.files');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.company');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.files');
    }

    public static function getModelLabel(): string
    {
        return __('backend.files');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.files');
    }

    public static function canAccess(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_company_files == true || $Role->can_write_company_files == true || $Role->can_edit_company_files == true;
    }

    public static function canView(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_company_files;
    }

    public static function canCreate(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_write_company_files;
    }

    public static function canEdit(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_company_files;
    }

    public static function canDelete(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_company_files;
    }
}
