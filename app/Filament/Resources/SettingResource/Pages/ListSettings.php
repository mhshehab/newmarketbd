<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('clearCache')
                ->label('Clear All Cache')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->action(function () {
                    Cache::flush();
                    
                    Notification::make()
                        ->title('Cache Cleared')
                        ->body('All Laravel cache has been successfully cleared.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
