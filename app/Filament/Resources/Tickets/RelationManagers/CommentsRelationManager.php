<?php

namespace App\Filament\Resources\Tickets\RelationManagers;

use App\Models\Comment;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->defaultSort('created_at')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Author'),
                TextColumn::make('content')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    // The author is always the logged-in user, never a form field
                    ->mutateDataUsing(fn (array $data): array => [...$data, 'user_id' => auth()->id()]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    // Agents only have the View page, so comments must stay writable there (CommentPolicy still decides)
    public function isReadOnly(): bool
    {
        return false;
    }

    // CommentPolicy::create needs the ticket (closed? own ticket?), which Filament does not pass by default
    protected function getCreateAuthorizationResponse(): Response
    {
        return Gate::inspect('create', [Comment::class, $this->getOwnerRecord()]);
    }
}
