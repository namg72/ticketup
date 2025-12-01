<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /* public function index(Request $request)
    {


        $user = $request->user();

        $role = $user->getRoleNames()->first();

        $query = Ticket::with('user', 'supervisor:id,name', 'category');

        if ($user->hasRole('admin')) {
        } elseif ($user->hasRole('supervisor')) {
            // Supervisor → tickets de los que es supervisor
            $query->where('supervisor_id', $user->id);
        } else {
            // Empleado (u otro rol) → solo sus tickets
            $query->where('user_id', $user->id);
        }


        $totalTickets = (clone $query)->count();

        $supervisor = User::find($user->supervisor_id);

        $pendingCount = (clone $query)->where('status', 'pending')->count();
        $reviewCount = (clone $query)->where('status', 'review')->count();
        $approvedCount = (clone $query)->where('status', 'approved')->count();
        $rejectedCount = (clone $query)->where('status', 'rejected')->count();

        $tickets = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();



        return Inertia::render('Dashboard', [
            'example'       => 'Hola desde DashboardController',
            'role'          => $role,
            'totalTickets'  => $totalTickets,
            'tickets'       => $tickets,
            'user'          => $user,
            'supervisor'   => $supervisor,
            'statusCounts' => [
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'review' => $reviewCount,
                'rejected' => $rejectedCount,
            ],
        ]);
    } */
}
