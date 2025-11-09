<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\OAuthClient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OAuthClientStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalClients = OAuthClient::count();
        $activeClients = OAuthClient::where('revoked', false)->count();
        $revokedClients = OAuthClient::where('revoked', true)->count();

        return [
            Stat::make('Total OAuth Clients', $totalClients)
                ->description('All registered clients')
                ->descriptionIcon('heroicon-m-key')
                ->color('primary'),
            
            Stat::make('Active Clients', $activeClients)
                ->description('Currently active clients')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Revoked Clients', $revokedClients)
                ->description('Revoked clients')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
