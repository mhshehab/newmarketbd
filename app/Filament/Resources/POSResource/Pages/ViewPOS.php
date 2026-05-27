<?php

namespace App\Filament\Resources\POSResource\Pages;

use App\Filament\Resources\POSResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ViewPOS extends ViewRecord
{
    protected static string $resource = POSResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Order Information')
                    ->description('Basic order details')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('order_number')
                                    ->label('Order Number')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->money('BDT')
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('created_at')
                                    ->label('Order Date')
                                    ->dateTime('M d, Y h:i A'),
                            ]),
                    ]),

                Section::make('Customer Information')
                    ->description('Customer details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Customer Name')
                                    ->placeholder('Guest Customer'),

                                TextEntry::make('user.phone')
                                    ->label('Phone Number')
                                    ->placeholder('N/A'),

                                TextEntry::make('user.email')
                                    ->label('Email')
                                    ->placeholder('N/A'),

                                TextEntry::make('user.address')
                                    ->label('Address')
                                    ->placeholder('N/A'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->user),

                Section::make('Order Items')
                    ->description('Products in this order')
                    ->schema([
                        RepeatableEntry::make('orderItems')
                            ->label('Products')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('product.name')
                                            ->label('Product')
                                            ->weight('medium'),

                                        TextEntry::make('quantity')
                                            ->label('Quantity')
                                            ->alignCenter(),

                                        TextEntry::make('unit_price')
                                            ->label('Unit Price')
                                            ->money('BDT')
                                            ->alignCenter(),

                                        TextEntry::make('total_price')
                                            ->label('Total')
                                            ->money('BDT')
                                            ->weight('bold')
                                            ->alignEnd(),
                                    ]),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),

                Section::make('Payment Information')
                    ->description('Payment details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('payment_method')
                                    ->label('Payment Method')
                                    ->formatStateUsing(fn ($state) => \App\Models\Payment::METHODS[$state] ?? $state)
                                    ->badge()
                                    ->color(fn ($state) => match($state) {
                                        'cash' => 'success',
                                        'card' => 'primary',
                                        'bkash' => 'purple',
                                        'nagad' => 'orange',
                                        'rocket' => 'blue',
                                        'upay' => 'green',
                                        default => 'gray',
                                    }),

                                TextEntry::make('payments.first.amount')
                                    ->label('Amount Paid')
                                    ->money('BDT')
                                    ->weight('bold'),

                                TextEntry::make('payments.first.payment_details.cash_received')
                                    ->label('Cash Received')
                                    ->money('BDT')
                                    ->visible(fn ($record) => $record->payment_method === 'cash'),

                                TextEntry::make('payments.first.payment_details.change_amount')
                                    ->label('Change Amount')
                                    ->money('BDT')
                                    ->visible(fn ($record) => $record->payment_method === 'cash'),

                                TextEntry::make('payments.first.status')
                                    ->label('Payment Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        'refunded' => 'info',
                                        default => 'gray',
                                    }),

                                TextEntry::make('payments.first.paid_at')
                                    ->label('Payment Date')
                                    ->dateTime('M d, Y h:i A')
                                    ->visible(fn ($record) => $record->payments->isNotEmpty()),
                            ]),
                    ]),

                Section::make('Loyalty Points')
                    ->description('Customer loyalty points information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('loyaltyPointsEarned')
                                    ->label('Points Earned')
                                    ->formatStateUsing(function ($record) {
                                        return $record->loyaltyPoints()
                                            ->where('transaction_type', 'earned')
                                            ->sum('points_earned');
                                    })
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('loyaltyPointsRedeemed')
                                    ->label('Points Redeemed')
                                    ->formatStateUsing(function ($record) {
                                        return $record->loyaltyPoints()
                                            ->where('transaction_type', 'redeemed')
                                            ->sum('points_redeemed');
                                    })
                                    ->badge()
                                    ->color('warning'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->user && $record->loyaltyPoints->isNotEmpty()),

                Section::make('Order Notes')
                    ->description('Additional information')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('No notes provided')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->notes))
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $record = $this->record;

        return [
            Action::make('edit')
                ->label('Edit Order')
                ->icon('heroicon-o-pencil')
                ->url(fn () => static::getResource()::getUrl('edit', ['record' => $record]))
                ->visible(fn () => $record->status === 'pending'),

            Action::make('process')
                ->label('Process Order')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => $record->status === 'pending')
                ->action(function () use ($record) {
                    $record->update(['status' => 'processing']);
                    
                    Notification::make()
                        ->title('Order Processing')
                        ->body("Order #{$record->order_number} is now being processed")
                        ->success()
                        ->send();
                }),

            Action::make('complete')
                ->label('Complete Order')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $record->status === 'processing')
                ->action(function () use ($record) {
                    $record->update(['status' => 'delivered']);
                    
                    Notification::make()
                        ->title('Order Completed')
                        ->body("Order #{$record->order_number} has been delivered successfully")
                        ->success()
                        ->send();
                }),

            Action::make('cancel')
                ->label('Cancel Order')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => in_array($record->status, ['pending', 'processing']))
                ->requiresConfirmation()
                ->action(function () use ($record) {
                    $record->update(['status' => 'cancelled']);
                    
                    Notification::make()
                        ->title('Order Cancelled')
                        ->body("Order #{$record->order_number} has been cancelled")
                        ->danger()
                        ->send();
                }),

            Action::make('download_invoice')
                ->label('Download Invoice')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->url(fn () => url("/pos/invoice/{$record->id}/download"))
                ->openUrlInNewTab(),
        ];
    }
}
