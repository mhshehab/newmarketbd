<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag'; // আইকন পরিবর্তন করা হয়েছে

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3) // পুরো ফর্মকে ৩ কলামে ভাগ করা হয়েছে
                    ->schema([
                        Section::make('Product Information')
                            ->description('Basic details about the product.')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                                TextInput::make('slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),

                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('unit')
                                    ->label('Unit (e.g., 1kg, 500g, 1pc)')
                                    ->placeholder('Ex: 1 kg')
                                    ->required(),
                            ])->columnSpan(2),

                        Section::make('Pricing & Stock')
                            ->schema([
                                TextInput::make('price')
                                    ->label('Regular Price')
                                    ->numeric()
                                    ->prefix('৳')
                                    ->required(),

                                TextInput::make('discount_price')
                                    ->label('Discount Price (Optional)')
                                    ->numeric()
                                    ->prefix('৳'),

                                TextInput::make('stock_quantity')
                                    ->label('Stock Quantity')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                TextInput::make('sku')
                                    ->label('SKU Code')
                                    ->unique(ignoreRecord: true),

                                TextInput::make('barcode')
                                    ->label('Barcode (for POS)')
                                    ->unique(ignoreRecord: true),
                            ])->columnSpan(1),

                        Section::make('Product Media & Description')
                            ->schema([
                                FileUpload::make('image')
                                    ->image()
                                    ->directory('products')
                                    ->imageEditor(), // ছবি ক্রপ করার সুবিধা

                                RichEditor::make('description')
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image'),
                
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): string => $record->unit ?? ''),

                TextColumn::make('category.name')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('BDT')
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 20 => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
