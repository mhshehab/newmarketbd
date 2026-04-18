<?php

namespace App\Filament\Resources;

use App\Filament\Resources\POSResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Payment;
use App\Models\Discount;
use App\Models\LoyaltyPoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Reactive;

class POSResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'POS System';

    protected static ?string $modelLabel = 'POS Order';

    protected static ?string $pluralModelLabel = 'POS Orders';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        // Customer Information
                        Grid::make(1)
                            ->schema([
                                Section::make('Customer Information')
                                    ->description('Select customer for this order')
                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Customer')
                                            ->options(function () {
                                                return User::pluck('name', 'id')
                                                    ->toArray();
                                            })
                                            ->searchable()
                                            ->getSearchResultsUsing(function (string $search) {
                                                return User::where('name', 'like', "%{$search}%")
                                                    ->orWhere('phone', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%")
                                                    ->limit(10)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            })
                                            ->getOptionLabelUsing(function ($value) {
                                                $user = User::find($value);
                                                return $user ? "{$user->name} ({$user->phone})" : '';
                                            })
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, callable $set) => $set('loyalty_points', self::getCustomerLoyaltyPoints($state))),

                                        Placeholder::make('loyalty_points_display')
                                            ->label('Available Loyalty Points')
                                            ->content(function ($get) {
                                                $points = self::getCustomerLoyaltyPoints($get('user_id'));
                                                return $points ? "{$points} points" : 'No points available';
                                            }),

                                        TextInput::make('points_to_redeem')
                                            ->label('Points to Redeem')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(function ($get) {
                                                return self::getCustomerLoyaltyPoints($get('user_id'));
                                            })
                                            ->helperText('100 points = 10 Tk discount'),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(1),

                        // Order Items
                        Grid::make(1)
                            ->schema([
                                Section::make('Order Items')
                                    ->description('Add products to the order')
                                    ->schema([
                                        Repeater::make('items')
                                            ->label('Products')
                                            ->schema([
                                                Select::make('product_id')
                                                    ->label('Product')
                                                    ->options(function () {
                                                        return Product::where('stock_quantity', '>', 0)
                                                            ->pluck('name', 'id')
                                                            ->toArray();
                                                    })
                                                    ->searchable()
                                                    ->getSearchResultsUsing(function (string $search) {
                                                        return Product::where('name', 'like', "%{$search}%")
                                                            ->orWhere('barcode', 'like', "%{$search}%")
                                                            ->where('stock_quantity', '>', 0)
                                                            ->limit(10)
                                                            ->pluck('name', 'id')
                                                            ->toArray();
                                                    })
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        $product = Product::find($state);
                                                        if ($product) {
                                                            $set('unit_price', $product->price);
                                                            $set('stock_available', $product->stock_quantity);
                                                        }
                                                    }),

                                                TextInput::make('quantity')
                                                    ->label('Quantity')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(0.01)
                                                    ->step(0.01)
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        $unitPrice = $get('unit_price') ?? 0;
                                                        $quantity = $state ?? 0;
                                                        $set('total_price', $unitPrice * $quantity);
                                                    }),

                                                TextInput::make('unit_price')
                                                    ->label('Unit Price')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->dehydrated(false),

                                                TextInput::make('total_price')
                                                    ->label('Total Price')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->dehydrated(false),

                                                Toggle::make('is_weighted')
                                                    ->label('Weighted Product')
                                                    ->default(false),

                                                TextInput::make('weight')
                                                    ->label('Weight (kg)')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->visible(fn (callable $get) => $get('is_weighted'))
                                                    ->default(1.0),
                                            ])
                                            ->columns(2)
                                            ->itemLabel(fn (array $state): ?string => 
                                                isset($state['product_id']) ? Product::find($state['product_id'])?->name : null
                                            )
                                            ->collapsible()
                                            ->collapsed()
                                            ->addActionLabel('Add Product')
                                            ->reorderableWithButtons()
                                            ->collapsible(),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(2),
                    ]),

                // Payment and Discount Section
                Grid::make(2)
                    ->schema([
                        Section::make('Discount & Offers')
                            ->description('Apply discounts and offers')
                            ->schema([
                                TextInput::make('manual_discount')
                                    ->label('Manual Discount')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Tk')
                                    ->helperText('Enter discount amount'),

                                Select::make('manual_discount_type')
                                    ->label('Discount Type')
                                    ->options([
                                        'fixed' => 'Fixed Amount',
                                        'percentage' => 'Percentage',
                                    ])
                                    ->default('fixed')
                                    ->reactive(),

                                TextInput::make('discount_code')
                                    ->label('Discount Code')
                                    ->placeholder('Enter discount code')
                                    ->helperText('Apply promotional codes'),
                            ])
                            ->columns(2),

                        Section::make('Payment Information')
                            ->description('Payment method and details')
                            ->schema([
                                Select::make('payment_method')
                                    ->label('Payment Method')
                                    ->options(Payment::METHODS)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state !== 'cash') {
                                            $set('cash_received', null);
                                            $set('change_amount', 0);
                                        }
                                    }),

                                TextInput::make('cash_received')
                                    ->label('Cash Received')
                                    ->numeric()
                                    ->prefix('Tk')
                                    ->visible(fn (callable $get) => $get('payment_method') === 'cash')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $total = $get('total_amount') ?? 0;
                                        $cashReceived = $state ?? 0;
                                        $set('change_amount', max(0, $cashReceived - $total));
                                    }),

                                TextInput::make('change_amount')
                                    ->label('Change Amount')
                                    ->numeric()
                                    ->prefix('Tk')
                                    ->disabled()
                                    ->dehydrated(false),

                                Textarea::make('payment_notes')
                                    ->label('Payment Notes')
                                    ->rows(2)
                                    ->placeholder('Additional payment details'),
                            ])
                            ->columns(2),
                    ]),

                // Order Summary
                Section::make('Order Summary')
                    ->description('Review order details')
                    ->schema([
                        Placeholder::make('subtotal')
                            ->label('Subtotal')
                            ->content(function ($get) {
                                $items = $get('items') ?? [];
                                $subtotal = 0;
                                foreach ($items as $item) {
                                    $subtotal += ($item['total_price'] ?? 0);
                                }
                                return 'Tk ' . number_format($subtotal, 2);
                            }),

                        Placeholder::make('loyalty_discount')
                            ->label('Loyalty Points Discount')
                            ->content(function ($get) {
                                $points = $get('points_to_redeem') ?? 0;
                                $discount = ($points / 100) * 10; // 100 points = 10 Tk
                                return 'Tk ' . number_format($discount, 2);
                            }),

                        Placeholder::make('manual_discount_amount')
                            ->label('Manual Discount')
                            ->content(function ($get) {
                                $subtotal = 0;
                                $items = $get('items') ?? [];
                                foreach ($items as $item) {
                                    $subtotal += ($item['total_price'] ?? 0);
                                }
                                
                                $discountAmount = $get('manual_discount') ?? 0;
                                $discountType = $get('manual_discount_type') ?? 'fixed';
                                
                                if ($discountType === 'percentage') {
                                    $discountAmount = ($subtotal * $discountAmount) / 100;
                                }
                                
                                return 'Tk ' . number_format($discountAmount, 2);
                            }),

                        Placeholder::make('total_amount')
                            ->label('Total Amount')
                            ->content(function ($get) {
                                $subtotal = 0;
                                $items = $get('items') ?? [];
                                foreach ($items as $item) {
                                    $subtotal += ($item['total_price'] ?? 0);
                                }
                                
                                // Calculate loyalty discount
                                $points = $get('points_to_redeem') ?? 0;
                                $loyaltyDiscount = ($points / 100) * 10;
                                
                                // Calculate manual discount
                                $manualDiscount = $get('manual_discount') ?? 0;
                                $discountType = $get('manual_discount_type') ?? 'fixed';
                                if ($discountType === 'percentage') {
                                    $manualDiscount = ($subtotal * $manualDiscount) / 100;
                                }
                                
                                $total = $subtotal - $loyaltyDiscount - $manualDiscount;
                                return 'Tk ' . number_format(max(0, $total), 2);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Notes
                Section::make('Order Notes')
                    ->description('Additional order information')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Add any special instructions or notes for this order'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('BDT')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'processing',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->formatStateUsing(fn (string $state): string => 
                        Payment::METHODS[$state] ?? $state
                    ),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options(Payment::METHODS),

                SelectFilter::make('user_id')
                    ->label('Customer')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        return User::where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->limit(10)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $user = User::find($value);
                        return $user ? $user->name : '';
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record): string => route('orders.show', $record))
                    ->openUrlInNewTab(),

                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil'),

                Action::make('process_payment')
                    ->label('Process Payment')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'pending')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'processing']);
                        Notification::make()
                            ->title('Payment Processing')
                            ->body('Order payment is being processed')
                            ->success()
                            ->send();
                    }),

                Action::make('complete_order')
                    ->label('Complete Order')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'processing')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'delivered']);
                        Notification::make()
                            ->title('Order Completed')
                            ->body('Order has been successfully delivered')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                // Add bulk actions if needed
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPOS::route('/'),
            'create' => Pages\CreatePOS::route('/create'),
            'view' => Pages\ViewPOS::route('/{record}'),
            'edit' => Pages\EditPOS::route('/{record}/edit'),
        ];
    }

    private static function getCustomerLoyaltyPoints(?int $userId): int
    {
        if (!$userId) {
            return 0;
        }

        $earnedPoints = LoyaltyPoint::where('user_id', $userId)
            ->where('transaction_type', 'earned')
            ->sum('points_earned');

        $redeemedPoints = LoyaltyPoint::where('user_id', $userId)
            ->where('transaction_type', 'redeemed')
            ->sum('points_redeemed');

        return max(0, $earnedPoints - $redeemedPoints);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
