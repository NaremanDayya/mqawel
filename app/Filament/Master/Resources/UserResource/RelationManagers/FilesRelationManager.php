<?php

namespace App\Filament\Master\Resources\UserResource\RelationManagers;

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
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'Files';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),

                Hidden::make('created_by')->default(Auth::user()->id),

                Hidden::make('parent_table')->default('users'),

                Hidden::make('parent_id')->default($this->ownerRecord->id),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')->required()->label(__('backend.name')),
                        DatePicker::make('expiry_date')->label(__('backend.expiry_date')),
                        MarkdownEditor::make('description')->columnSpanFull()->label(__('backend.description')),
                    ])->columns(2),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        FileUpload::make('file')->directory('documents')->required()->label(__('backend.document')),
                    ]),

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
                TextColumn::make('name')->label(__('backend.name')),
                TextColumn::make('expiry_date')->label(__('backend.expiry_date')),
                IconColumn::make('is_active')->boolean()->label(__('backend.active')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download_file')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function(array $data, $record) : StreamedResponse {
                        $filePath= 'public/'.$record->file;

                        /*if(!Storage::exists($filePath)){
                            abort(404, __('backend.file_not_found'));
                        }*/

                        return Storage::download($filePath);
                    })->label(__('backend.download')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('backend.files');
    }

    public static function getModelLabel(): string
    {
        return __('backend.files');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.files');
    }
}
