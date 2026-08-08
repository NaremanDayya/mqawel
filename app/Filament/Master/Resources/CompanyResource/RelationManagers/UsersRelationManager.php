<?php

namespace App\Filament\Master\Resources\CompanyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'Users';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make(__('backend.information'))->schema([
                        TextInput::make('name')->columnSpanFull()->required()->label(__('backend.name')),

                        Select::make('role')->relationship('role', 'name', function($query){
                            /*$query->where('company_id', Auth::user()->company_id);
                            $query->orWhere('company_id', null);*/
                        })->required()->label(__('backend.role')),

                        TextInput::make('email')->required()->unique(ignoreRecord:true)->label(__('backend.email')),
                        
                        TextInput::make('phone')->label(__('backend.phone')),
                        
                        TextInput::make('password')->password()->label(__('backend.password')),
                    ])->columns(2),

                    Section::make()->schema([
                        FileUpload::make('picture')->directory('form-attachments')->image()->imageEditor()->label(__('backend.picture'))
                    ])->collapsible(),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('job_title')->required()->label(__('backend.job_title')),

                        MarkdownEditor::make('job_description')->columnSpan('full')->label(__('backend.job_description')),
                    ])->columns(1),

                    Section::make()->schema([
                        Toggle::make('is_active')->default(true)->label(__('backend.active')),
                    ]),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                ImageColumn::make('picture')->defaultImageUrl(asset('images/no_profile_picture.png'))->circular()->label(''),
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name')),
                TextColumn::make('role.name')->badge()->color('primary')->searchable()->sortable()->label(__('backend.role')),
                TextColumn::make('email')->searchable()->sortable()->label(__('backend.email')),
                TextColumn::make('phone')->searchable()->sortable()->label(__('backend.phone')),
                TextColumn::make('job_title')->searchable()->sortable()->label(__('backend.job_title')),
                IconColumn::make('is_active')->boolean()->label(__('backend.active')),
            ])
            ->filters([
                TernaryFilter::make('is_active')->boolean()->trueLabel(__('backend.active'))->falseLabel(__('backend.inactive'))->native(false)->label(__('backend.active')),
                SelectFilter::make('role')->options([
                    'admin' => __('backend.admin'),
                    'staff' => __('backend.staff'),
                    'viewer' => __('backend.viewer'),
                    'worker' => __('backend.worker'),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('backend.users');
    }

    public static function getModelLabel(): string
    {
        return __('backend.users');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.users');
    }
}
