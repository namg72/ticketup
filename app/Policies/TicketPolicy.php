<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false;
    }


    public function createComment(User $user, Ticket $ticket): bool
    {
        if ($ticket->status !== 'pending' && $ticket->status !== 'review') {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        };
        if ($user->hasRole('supervisor') && $user->id === $ticket->supervisor_id) {
            return true;
        };
        if ($user->hasRole('employee') && $user->id === $ticket->user_id) {
            return true;
        };

        return false;
    }
    public function editComment(User $user, Ticket $ticket, TicketComment $comment): bool
    {
        // 1️⃣ Seguridad extra: el comentario debe pertenecer a ESTE ticket
        if ($comment->ticket_id !== $ticket->id) {
            return false;
        }

        // 2️⃣ Reutilizamos la lógica base: 
        // ¿este usuario puede comentar este ticket en general?
        if (! $this->createComment($user, $ticket)) {
            return false;
        }

        // 3️⃣ Solo el autor del comentario puede editarlo
        return $user->id === $comment->user_id;
    }
    public function deleteComment(User $user, Ticket $ticket, TicketComment $comment): bool
    {

        // El comentario se creó hace más de 15 minutos → NO puede borrar
        if ($comment->created_at->lt(now()->subMinutes(15))) {
            return false;
        }

        if ($this->editComment($user, $ticket, $comment)) {
            return true;
        }


        return false;
    }
}
