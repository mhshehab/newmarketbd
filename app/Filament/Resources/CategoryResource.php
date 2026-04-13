<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag'; 

    protected static ?string $navigationGroup = 'Shop Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Category Information')
                    ->description('ক্যাটাগরির নাম, প্যারেন্ট এবং ইমেজ এখানে সেট করুন।')
                    ->schema([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->unique(Category::class, 'slug', ignoreRecord: true),

                        Select::make('parent_id')
                            ->label('Parent Category')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('মূল ক্যাটাগরি হলে খালি রাখুন'),

                        // এখানে আমরা ড্রপডাউনে আরও কিছু প্রয়োজনীয় আইকন যোগ করেছি
                        Select::make('icon')
                            ->label('Category Icon')
                            ->options([
                                'heroicon-o-shopping-bag' => 'Shopping Bag',
                                'heroicon-o-cpu-chip' => 'Electronics',
                                'heroicon-o-home' => 'Home & Garden',
                                'heroicon-o-bolt' => 'Fashion',
                                'heroicon-o-beaker' => 'Health & Beauty',
                                'heroicon-o-gift' => 'Gifts',
                                'heroicon-o-camera' => 'Gadgets',
                                'heroicon-o-device-phone-mobile' => 'Mobile Phones',
                                'heroicon-o-academic-cap' => 'Books & Education',
                                'heroicon-o-sparkles' => 'Jewelry',
                                'heroicon-o-truck' => 'Automotive',
                            ])
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('parent_id') === null), 

                        FileUpload::make('image')
                            ->label('Category Image')
                            ->image()
                            ->directory('categories')
                            ->imageEditor() 
                            ->imageCropAspectRatio('1:1') 
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Thumbnail')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),
                
                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon(fn (Category $record): ?string => $record->icon) 
                    ->iconColor('primary'), // আইকন নীল কালার দেখাবে

                TextColumn::make('parent.name')
                    ->label('Hierarchy')
                    ->default('Main Category')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'Main Category' ? 'success' : 'info'),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Filter by Parent')
                    ->relationship('parent', 'name'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}