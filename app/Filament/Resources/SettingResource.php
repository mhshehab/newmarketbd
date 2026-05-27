<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('General Information')
                ->schema([
                    Forms\Components\TextInput::make('key')
                        ->required()
                        ->disabled(fn ($record) => $record !== null),
                    Forms\Components\Select::make('type')
                        ->options([
                            'text' => 'Text',
                            'image' => 'Image',
                            'boolean' => 'Boolean',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($set) => $set('value', null)),
                ])->columns(2),

            Forms\Components\Section::make('Setting Value')
                ->schema([
                    Forms\Components\Group::make()
                        ->schema(function ($get) {
                            $type = $get('type');
                            
                            if ($type === 'text') {
                                return [
                                    Forms\Components\TextInput::make('value')
                                        ->label('Value (Text)')
                                        ->columnSpanFull()
                                ];
                            }
                            
                            if ($type === 'image') {
                                return [
                                    Forms\Components\FileUpload::make('value')
                                        ->label('Image / Logo')
                                        ->image()
                                        ->disk('public')
                                        ->directory('settings')
                                        ->formatStateUsing(fn ($state) => is_array($state) ? $state : ($state ? [$state] : []))
                                        ->dehydrateStateUsing(fn ($state) => is_array($state) ? reset($state) : $state)
                                        ->required()
                                ];
                            }
                            
                            if ($type === 'boolean') {
                                return [
                                    Forms\Components\Toggle::make('value')
                                        ->label('Enable / Disable')
                                        ->afterStateHydrated(fn ($component, $state) => $component->state((bool) $state))
                                ];
                            }
                            
                            return [];
                        })
                        ->key('dynamic-value-field')
                ]),

            Forms\Components\Section::make('Metadata')
                ->schema([
                    Forms\Components\Textarea::make('description')->columnSpanFull(),
                    Forms\Components\Toggle::make('is_public')->default(true),
                    Forms\Components\TextInput::make('position')->numeric()->default(0),
                ])->columns(2),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\ToggleColumn::make('is_public'),
                Tables\Columns\TextColumn::make('position')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSettings::route('/'),
                'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
