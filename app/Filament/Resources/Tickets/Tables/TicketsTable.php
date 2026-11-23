<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Filament\Resources\Tickets\Actions\ChangeStatusAction;
use App\Filament\Resources\Tickets\Schemas\TicketForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => TicketForm::STATUS_COLORS[$state]),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => TicketForm::PRIORITY_COLORS[$state])
                    ->sortable(),
                TextColumn::make('client.name')
                    ->searchable(),
                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->placeholder('Unassigned')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(TicketForm::STATUSES),
                SelectFilter::make('priority')
                    ->options(TicketForm::PRIORITIES),
            ])
            ->recordActions([
                ViewAction::make(),
                ChangeStatusAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
