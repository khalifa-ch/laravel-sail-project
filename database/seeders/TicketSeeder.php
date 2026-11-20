<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::where('email', 'client@test.com')->firstOrFail();
        $agent  = User::where('email', 'agent@gmail.com')->firstOrFail();

        // Ticket 1 : ouvert, pas encore assigné
        $ticket1 = $client->tickets()->create([
            'title'       => 'Impossible de me connecter',
            'description' => "J'ai une erreur 500 quand je me connecte.",
            'priority'    => 'high',
        ]);

        // Ticket 2 : en cours, assigné à l'agent
        $ticket2 = $client->tickets()->create([
            'title'             => 'Facture introuvable',
            'description'       => 'Je ne trouve pas ma facture du mois dernier.',
            'status'            => 'in_progress',
            'priority'          => 'medium',
            'assigned_agent_id' => $agent->id,
        ]);

        // Ticket 3 : fermé, assigné à l'agent
        $client->tickets()->create([
            'title'             => 'Changer mon adresse email',
            'description'       => 'Je voudrais mettre à jour mon adresse email.',
            'status'            => 'closed',
            'priority'          => 'low',
            'assigned_agent_id' => $agent->id,
        ]);

        // Un commentaire du client sur le ticket 1
        $ticket1->comments()->create([
            'user_id' => $client->id,
            'content' => 'Toujours bloqué ce matin.',
        ]);

        // Un commentaire de l'agent sur le ticket 2
        $ticket2->comments()->create([
            'user_id' => $agent->id,
            'content' => 'Je regarde votre compte, je reviens vers vous rapidement.',
        ]);
    }
}
