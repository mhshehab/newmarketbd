<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
// কম্পোনেন্টগুলো ইমপোর্ট করা হয়েছে
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo'; // আইকন পরিবর্তন করা হয়েছে

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Slider Details')
                    ->schema([
                        TextInput::make('title')
                            ->placeholder('Enter slider title')
                            ->maxLength(255),
                        
                        FileUpload::make('image')
                            ->image()
                            ->directory('sliders')
                            ->imageEditor() // ইমেজ ক্রপ করার অপশন দিবে
                            ->required(),
                            
                        TextInput::make('link')
                            ->url()
                            ->placeholder('https://example.com/promo'),
                        
                        TextInput::make('button_text')
                            ->default('Shop Now'),
                            
                        Toggle::make('is_active')
                            ->label('Visible on Website')
                            ->default(true),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // টেবিল ভিউতে ইমেজ দেখানোর জন্য
                ImageColumn::make('image')
                    ->circular(),
                
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('button_text'),
                
                // একটিভ কি না তা আইকন দিয়ে দেখাবে
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}