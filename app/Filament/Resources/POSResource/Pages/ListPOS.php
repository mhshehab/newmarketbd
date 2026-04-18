<?php

namespace App\Filament\Resources\POSResource\Pages;

use App\Filament\Resources\POSResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListPOS extends ListRecords
{
    protected static string $resource = POSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New POS Order')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->columns([
                TextColumn::make('id')
                    ->label('Order #')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT)),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        if ($record->user) {
                            return $record->user->name . ' (' . $record->user->phone . ')';
                        }
                        return 'Guest Customer';
                    }),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('BDT')
                    ->sortable()
                    ->weight('bold'),

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
                    ->formatStateUsing(fn ($state) => \App\Models\Payment::METHODS[$state] ?? $state)
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'cash' => 'success',
                        'card' => 'primary',
                        'bkash' => 'purple',
                        'nagad' => 'orange',
                        'rocket' => 'blue',
                        'upay' => 'green',
                        'digital_wallet' => 'gray',
                        'gift_card' => 'pink',
                        'bank_transfer' => 'indigo',
                        default => 'gray',
                    }),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->formatStateUsing(function ($record) {
                        return $record->orderItems()->count() . ' items';
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => static::getResource()::getUrl('view', ['record' => $record]))
                    ->color('primary'),

                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => static::getResource()::getUrl('edit', ['record' => $record]))
                    ->visible(fn ($record) => $record->status === 'pending'),

                Action::make('process')
                    ->label('Process')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'processing']);
                        \Filament\Notifications\Notification::make()
                            ->title('Order Processing')
                            ->body('Order #' . $record->id . ' is now being processed')
                            ->success()
                            ->send();
                    }),

                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'processing')
                    ->action(function ($record) {
                        $record->update(['status' => 'delivered']);
                        \Filament\Notifications\Notification::make()
                            ->title('Order Completed')
                            ->body('Order #' . $record->id . ' has been delivered successfully')
                            ->success()
                            ->send();
                    }),

                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'processing']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'cancelled']);
                        \Filament\Notifications\Notification::make()
                            ->title('Order Cancelled')
                            ->body('Order #' . $record->id . ' has been cancelled')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->with(['user', 'orderItems']);
    }
}
