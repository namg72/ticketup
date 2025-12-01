<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketRequest;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TicketController extends Controller
{

    public function index(Request $request)
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
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $categories = TicketCategory::all(['id', 'name']);


        return Inertia::render('Tickets/Create', [
            'user' => $user,
            "categories" => $categories

        ]);
    }

    public function store(TicketRequest $request)
    {
        Log::debug($request);
        $user = $request->user();

        if (!$user->hasRole('employee')) {
            return redirect()->back()->with('error', 'No está autorizado a subir un nuevo gasto');
        }

        $uploadFile = $request->file('image');


        $extension = $uploadFile->getClientOriginalExtension();

        $uri = $user->id . '-' . now()->format('YmdHis') . '.' . $extension;

        $path = $uploadFile->storeAs(
            'tickets',
            $uri,
            'public'
        );


        $total = $request->total_amount;
        $base = round($total / 1.21, 2);
        $iva  = round($total - $base, 2);

        $tickets = [
            'user_id' => $user->id,
            'supervisor_id' => $user->supervisor_id,
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'uri' => $path,
            'amount' => $base,
            'iva_amount' => $iva,
            'total_amount' => $total,

        ];
        try {
            Ticket::create($tickets);
        } catch (\Throwable $e) {

            // si la inserción en BD falla, borramos la imagen que acabamos de subir
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'message' => 'Error al guardar el ticket en la base de datos',
            ], 500);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Gasto subido correctamente');
    }

    public function edit(Ticket $ticket, Request $request)
    {


        $user = $request->user();

        $role = $user->getRoleNames()->first();
        $ticket->load('user', 'supervisor', 'category', 'comments');
        $categories = TicketCategory::all(['id', 'name']);
        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket,
            'categories' => $categories,
            'role' => $role,
        ]);
    }



    public function update(TicketRequest $request, string $id)
    {


        $ticket = Ticket::find($id);



        $uploadFile = $request->file('image');


        $uri = $ticket->uri;



        if ($uploadFile !== null) {

            if (!empty($ticket->uri) &&  Storage::disk('public')->exists($ticket->uri)) {
                Storage::disk('public')->delete($ticket->uri);
            }
            $extension = $uploadFile->getClientOriginalExtension();
            $uri = $ticket->user_id . '-' . now()->format('YmdHis') . '.' . $extension;

            $path = $uploadFile->storeAs(
                'tickets',
                $uri,
                'public'


            );



            $uri = $path;
        }



        try {

            $total = $request->total_amount;
            $base = round($total / 1.21, 2);
            $iva  = round($total - $base, 2);

            $ticket->fill([
                'title' => $request->title,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'uri' => $uri,
                'total_amount' => $total,
                'amount' => $base,
                'iva_amount' => $iva,
            ]);

            $ticket->save();

            return redirect()
                ->route('dashboard')
                ->with('success', 'Gasto subido correctamente');
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar el ticket en la base de datos',
                $th
            ], 500);

            return redirect()
                ->route('dashboard')
                ->with('success', 'Ha ocurrido un error al actualizar el gasto');
        }
    }
}
