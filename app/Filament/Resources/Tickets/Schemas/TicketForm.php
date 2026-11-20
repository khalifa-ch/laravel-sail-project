<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TicketForm
{
    public const STATUSES = ['open' => 'Open', 'in_progress' => 'In progress', 'closed' => 'Closed'];

    public const PRIORITIES = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'];

    public const STATUS_COLORS = ['open' => 'info', 'in_progress' => 'warning', 'closed' => 'success'];

    public const PRIORITY_COLORS = ['low' => 'gray', 'medium' => 'warning', 'high' => 'danger'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(self::STATUSES)
                    ->default('open')
                    ->required(),
                Select::make('priority')
                    ->options(self::PRIORITIES)
                    ->default('medium')
                    ->required(),
                Select::make('client_id')
                    ->relationship('client', 'name')
                    ->disabledOn('edit')
                    ->required(),
                Select::make('assigned_agent_id')
                    ->label('Assigned agent')
                    ->relationship('agent', 'name', fn (Builder $query) => $query->role('agent'))
                    ->searchable()
                    ->preload(),
            ]);
    }
}
