<?php

namespace App\Filament\Resources\Tickets\Actions;

use App\Filament\Resources\Tickets\Schemas\TicketForm;
use App\Models\Ticket;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class ChangeStatusAction
{
    public static function make(): Action
    {
        return Action::make('changeStatus')
            ->label('Change status')
            ->icon(Heroicon::OutlinedArrowPath)
            ->visible(fn (Ticket $record): bool => self::allowedStatuses($record) !== [])
            ->schema(fn (Ticket $record): array => [
                Select::make('status')
                    ->options(self::allowedStatuses($record))
                    ->required(),
            ])
            ->action(function (Action $action, Ticket $record, array $data): void {
                Gate::authorize('updateStatus', [$record, $data['status']]);

                $record->update(['status' => $data['status']]);

                $action->success();
            })
            ->successNotificationTitle('Status updated');
    }

    // Ask TicketPolicy::updateStatus for each status, so the rule is never duplicated here
    private static function allowedStatuses(Ticket $ticket): array
    {
        return collect(TicketForm::STATUSES)
            ->except($ticket->status)
            ->filter(fn (string $label, string $status): bool => Gate::allows('updateStatus', [$ticket, $status]))
            ->all();
    }
}
