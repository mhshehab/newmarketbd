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
use Filament\Forms\Components\Toggle; // নতুন যুক্ত করা হয়েছে
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn; // নতুন যুক্ত করা হয়েছে
use Filament\Forms\Set;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    //protected static ?string $navigationLabel = 'পণ্য তালিকা'; // বাংলায় চাইলে

    protected static ?string $navigationGroup = 'Shop Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        // বাম পাশের কলাম (Product Info & Media)
                        Grid::make(1)
                            ->schema([
                                Section::make('Product Information')
                                    ->description('Basic details about the product.')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                                $set('slug', Str::slug($state));
                                                // নাম লিখলে অটোমেটিক একটি প্রোবেবল SKU কোড জেনারেট হবে
                                                $set('sku', strtoupper(Str::random(4)) . '-' . rand(1000, 9999));
                                            }),

                                        TextInput::make('slug')
                                            ->required()
                                            ->disabled()
                                            ->dehydrated()
                                            ->unique(Product::class, 'slug', ignoreRecord: true),

                                        Select::make('category_id')
                                            ->relationship('category', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload(),

                                        TextInput::make('unit')
                                            ->label('Unit (e.g., 1kg, 1pc)')
                                            ->placeholder('Ex: 1 kg')
                                            ->required(),
                                    ])->columns(2),

                                Section::make('Product Media & Description')
                                    ->schema([
                                        FileUpload::make('image')
                                            ->image()
                                            ->directory('products')
                                            ->disk('public') // ইমেজ শো করার জন্য এটি নিশ্চিত করুন
                                            ->imageEditor()
                                            ->columnSpanFull(),

                                        RichEditor::make('description')
                                            ->columnSpanFull(),
                                    ]),
                            ])->columnSpan(2),

                        // ডান পাশের কলাম (Pricing, Stock & Status)
                        Grid::make(1)
                            ->schema([
                                Section::make('Pricing & Stock')
                                    ->schema([
                                        TextInput::make('price')
                                            ->label('Regular Price')
                                            ->numeric()
                                            ->prefix('৳')
                                            ->minValue(0) // নেগেটিভ প্রাইস বন্ধ করতে
                                            ->required(),

                                        TextInput::make('discount_price')
                                            ->label('Discount Price')
                                            ->numeric()
                                            ->prefix('৳')
                                            ->minValue(0),

                                        TextInput::make('stock_quantity')
                                            ->label('Current Stock')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->required(),

                                        DatePicker::make('expiry_date')
                                            ->label('মেয়াদ')
                                            ->required()
                                            ->placeholder('তারিখ সিলেক্ট করুন')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),

                                        TextInput::make('sku')
                                            ->label('SKU Code')
                                            ->unique(ignoreRecord: true),

                                        TextInput::make('barcode')
                                            ->label('Barcode (POS)')
                                            ->unique(ignoreRecord: true),
                                    ]),

                                Section::make('Availability')
                                    ->schema([
                                        Toggle::make('status')
                                            ->label('Active Status')
                                            ->helperText('Enable or disable product visibility')
                                            ->default(true),

                                        Toggle::make('is_featured')
                                            ->label('Featured Product')
                                            ->helperText('Show on home page featured section')
                                            ->default(false),
                                    ]),
                            ])->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->disk('public'), // ইমেজ শো করার জন্য গুরুত্বপূর্ণ
                
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): string => $record->unit ?? ''),

                TextColumn::make('category.name')
                    ->sortable()
                    ->badge(),

                TextColumn::make('price')
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

                TextColumn::make('expiry_date')
                    ->label('মেয়াদ')
                    ->date()
                    ->sortable()
                    ->color(fn (Product $record): string => $record->expiry_date && $record->expiry_date->isPast() ? 'danger' : 'success')
                    ->description(fn (Product $record): string => $record->expiry_date && $record->expiry_date->isPast() ? 'মেয়াদ শেষ!' : ''),

                IconColumn::make('status')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Active Status'),
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