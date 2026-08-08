<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkersRelationManager extends RelationManager
{
    protected static string $relationship = 'workers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')->required(),

                    TextInput::make('phone'),

                    TextInput::make('ethnicity'),

                    TextInput::make('living_address'),
                ])->columns(2),

                Section::make('Image')->schema([
                    FileUpload::make('picture')->directory('form-attachments')->image()->imageEditor()
                ])->collapsible(),

                Section::make('Files')->schema([
                    FileUpload::make('file_contract')->directory('form-attachments')->image()->imageEditor()->label('Upload contract'),
                    
                    FileUpload::make('file_residence')->directory('form-attachments')->image()->imageEditor()->label('Upload residence'),
                ])->collapsible(),
                
                Section::make('Status')
                ->schema([
                    Select::make('status')->options([
                        'active' => 'active',
                        'inactive' => 'inactive',
                    ])->required(),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                ImageColumn::make('picture'),
                TextColumn::make('name'),
                TextColumn::make('phone'),
                TextColumn::make('ethnicity'),
                TextColumn::make('living_address'),
                TextColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
