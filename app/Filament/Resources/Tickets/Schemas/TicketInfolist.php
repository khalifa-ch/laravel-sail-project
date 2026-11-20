<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => TicketForm::STATUS_COLORS[$state]),
                TextEntry::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => TicketForm::PRIORITY_COLORS[$state]),
                TextEntry::make('client.name')
                    ->label('Client'),
                TextEntry::make('agent.name')
                    ->label('Agent')
                    ->placeholder('Unassigned'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
