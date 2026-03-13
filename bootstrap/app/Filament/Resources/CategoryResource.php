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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Select::make('parent_id')
                            ->label('Parent Category')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('মূল ক্যাটাগরি হলে খালি রাখুন')
                            ->helperText('নেস্টেড করতে চাইলে মেইন ক্যাটাগরি সিলেক্ট করুন।'),

                        FileUpload::make('image')
                            ->label('Category Image')
                            ->image()
                            ->directory('categories')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->minSize(1)
                            ->imageEditor() // ইমেজ ক্রপ করার অপশন
                            ->imageCropAspectRatio('1:1') // সবসময় স্কয়ার ছবি
                            ->imageResizeTargetWidth('600')
                            ->imageResizeTargetHeight('600')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->circular(),
                
                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.name')
                    ->label('Hierarchy')
                    ->sortable()
                    ->default('Main Category')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'Main Category' ? 'success' : 'info'),

                TextColumn::make('slug')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Filter by Parent')
                    ->relationship('parent', 'name'),
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