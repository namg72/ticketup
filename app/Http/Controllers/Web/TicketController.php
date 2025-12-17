<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\CommentRequest;
use App\Http\Requests\TicketRequest;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class TicketController extends Controller
{

    public function index(Request $request)
    {


        $user = $request->user();

        //Obtencion del role con spatie
        $role = $user->getRoleNames()->first();

        $query = Ticket::with('user', 'supervisor:id,name', 'category');

        //Filtros

        // 1. FILTRO DE RANGO DE FECHAS (from & to)
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            // Usamos Carbon para asegurar que incluimos todo el día de fin (hasta 23:59:59)
            $endDate = \Carbon\Carbon::parse($request->get('to'))->endOfDay();
            $query->where('created_at', '<=', $endDate);
        }

        // 2. FILTRO POR ESTADO (status)
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // 3. FILTRO CATEGORIA
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // 4. FILTRO POR SUPERVISOR (solo visible y útil para Admin/Supervisores)
        if ($request->filled('supervisor_id')) {
            $query->where('supervisor_id', $request->get('supervisor_id'));
        }


        // 5. FILTRO POR NOMBRE DE USUARIO (user_name)
        if ($request->filled('user_name')) {
            // Buscamos dentro de la relación 'user' por el nombre usando LIKE (case-insensitive)
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->get('user_name') . '%');
            });
        }



        $defaultYear = Carbon::now()->year;
        $selectedYear = (int) $request->input('year', $defaultYear);


        //  Obtener los gastos para el año seleccionado (12 meses, con relleno a cero)
        $monthlyExpenses = $this->getMonthlyExpenses($user, $selectedYear);

        //  Obtener la lista de años disponibles para el selector (incluye el año actual forzado)
        $availableYears = $this->getAvailableYears($request);


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

        // Incluimos en la respuesa las categorias

        $categories = TicketCategory::select('id', 'name')->get();

        // Incluimos en la respuesta los supervisores

        $supervisors = User::whereHas('roles', function ($query) {
            $query->where('name', 'supervisor');
        })
            ->orderBy('name')
            ->get(['id', 'name']);

        $filters = $request->only(['from', 'to', 'status', 'supervisor_id', 'user_name', 'category_id']);

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
            'monthlyExpenses' => $monthlyExpenses,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears->toArray(),
            'categories' => $categories,
            'filters' => $filters,
            'supervisorList' => $supervisors,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $categories = TicketCategory::where('active', true)->get(['id', 'name']);


        return Inertia::render('Tickets/Create', [
            'user' => $user,
            "categories" => $categories

        ]);
    }

    public function store(TicketRequest $request)
    {

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
        $ticket->load('user', 'supervisor', 'category', 'comments.user');
        $categories = TicketCategory::where('active', true)
            ->get(['id', 'name']);


        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket,
            'categories' => $categories,
            'role' => $role,
            'comments' => $ticket->comments,
            'user' => $user,

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

    public function createComment(Ticket $ticket, CommentRequest $request)
    {
        $this->authorize('createComment', $ticket);

        $user = $request->user();

        $data = $request->validated();

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $data['message'],
        ]);


        return redirect()
            ->route('tickets.edit', $ticket)
            ->with('success', 'Comentario creado correctamente.');
    }

    public function updateComment(Ticket $ticket, TicketComment $comment, CommentRequest $request)
    {
        $this->authorize('editComment', [$ticket, $comment]);

        $data = $request->validated(); // ['message' => '...']

        $comment->update([
            'message' => $data['message'],
        ]);

        return redirect()
            ->route('tickets.edit', $ticket)
            ->with('success', 'Comentario acutalizado correctamente.');
    }

    public function destroyComment(Ticket $ticket, TicketComment $comment, Request $request)
    {

        $this->authorize('deleteComment', [$ticket, $comment]);

        $comment->delete();
    }

    // Aunque Inertia se usa más en la función 'render'

    public function changeStatus(Ticket $ticket, Request $request)
    {
        // 1. Validar la entrada (El número de estado sigue viniendo en el Request Body)
        $validated = $request->validate([
            'status' => 'required|integer|between:1,4',
        ]);

        // 2. Mapeo de estados (La lógica de BD no cambia)
        $statusMap = [
            1 => 'pending',
            2 => 'review',
            3 => 'approved',
            4 => 'rejected',
        ];

        $newStatus = $validated['status'];

        if (isset($statusMap[$newStatus])) {
            $ticket->status = $statusMap[$newStatus];
            if ($request->user()->hasRole('admin')) {

                $ticket->finalized_by_admin = $request->finalized_by_admin;
            }
            $ticket->save();
        }


        // Redireccionamos t.

        return redirect()
            ->route('dashboard')
            ->with('success', 'Gasto subido correctamente');
    }

    private function getAvailableYears(Request $request): \Illuminate\Support\Collection
    {
        $user = $request->user();
        $currentYear = Carbon::now()->year;

        $query = Ticket::query();
        $query->where('status',  'approved');

        // Filtros por usuariso admin, ve todo supervisor y empleados solo los suyos 
        if ($user->hasRole('supervisor')) {
            $query->where('supervisor_id', $user->id);
        } elseif ($user->hasRole('employee')) {
            $query->where('user_id', $user->id);
        }

        // Obtenemos los años 
        $years = $query->distinct()
            ->select(DB::raw('YEAR(created_at) as year'))
            ->pluck('year')
            ->unique();

        // Aseguramos la inclusión del año actual
        if (!$years->contains($currentYear)) {
            $years->push($currentYear);
        }

        // 💡 SOLUCIÓN: Devolver la Collection directamente.
        // Esto hace que $availableYears en el método index sea una Collection válida.
        return $years->sortDesc()->values();
    }
    private function getMonthlyExpenses(User $user, int $year): array
    {
        // 1. Preparar la consulta y aplicar filtros de rol.
        $query = Ticket::query();




        if ($user->hasRole('supervisor')) {

            $query->where('supervisor_id', $user->id);
        } elseif ($user->hasRole('employee')) {

            $query->where('user_id', $user->id);
        }


        // 2. Filtramos solo los ticket aprovados

        $query->where('status', 'approved');

        // 3. Filtrar por el año solicitado ANTES de la agregación.
        $query->where(DB::raw('YEAR(created_at)'), $year);

        // 4. Ejecutar la consulta de agregación (SUM y GROUP BY) en la base de datos.
        $expenses = $query->select(
            DB::raw('MONTH(created_at) as month_num'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('SUM(total_amount) as total_expenses')
        )

            // Ordenar solo por mes, ya que el año está filtrado y obtyener la colección
            ->groupBy('year', 'month_num')
            ->orderBy('month_num', 'asc')
            ->get();

        // 5. Definir el rango de fechas completo: Enero a Diciembre del año solicitado.
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, 12, 1)->startOfMonth();

        // 6. Crear el array base ($fullRange) con todos los 12 meses inicializados a cero.
        $fullRange = collect();
        $currentDate = $startDate->copy();

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $key = $currentDate->format('Y-m'); // Ej: '2025-01'
            $fullRange->put($key, [
                // Formato de etiqueta para el gráfico (Ej: 'Ene 2025')
                'month' => $currentDate->isoFormat('MMM YYYY'),
                'total' => 0.00,
            ]);
            $currentDate->addMonth();
        }

        // 7. Formatear y mapear los gastos reales obtenidos de la BD.
        $actualExpenses = $expenses->mapWithKeys(function ($item) {
            $date = Carbon::createFromDate($item->year, $item->month_num, 1);
            $key = $date->format('Y-m');

            return [
                // Usamos la clave 'Y-m' para que coincida con la del fullRange
                $key => [
                    'month' => $date->isoFormat('MMM YYYY'),
                    // Asegurar que el total sea un float redondeado
                    'total' => round((float) $item->total_expenses, 2),
                ]
            ];
        });

        // 8. Combinar (Merge): Sobrescribir los valores de 0 del fullRange con los valores reales.
        // El método merge() mantiene el orden de la colección original ($fullRange).
        $filledExpenses = $fullRange->merge($actualExpenses);

        // 9. Devolver los valores como un array PHP indexado para el frontend.
        return $filledExpenses->values()->all();
    }
}
