<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ApiTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ticket::query();

        $user = request()->user();

        //Filtro por tipo de usuario para traernos los ticket de los empleados
        if ($user->hasRole('admin')) {
        } elseif ($user->hasRole('supervisor')) {
            $employeeIds = $user->employees->pluck('id');

            $query->whereIn('user_id', $employeeIds);
        } else {
            $query->where('user_id', $user->id);
        }

        //Filtrado por año


        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        //Filtrado por mes
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        //SubFiltrado por empleados
        if ($request->filled('user_id')) {
            if ($user->hasRole('admin')) {
                $query->where('user_id', $request->user_id);
            } elseif ($user->hasRole('supervisor')) {
                // supervisor solo puede filtrar dentro de sus empleados
                if ($user->employees->pluck('id')->contains($request->user_id)) {
                    $query->where('user_id', $request->user_id);
                }
            }
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);



        $years = Ticket::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($user->hasRole('admin')) {
            $users = User::all(['id', 'name']);
        } elseif ($user->hasRole('supervisor')) {
            $users = $user->employees()->get(['id', 'name']);
        } else {
            $users = collect([$user]);
        }
        return response()->json([
            'tickets' => $tickets,
            'filters' => [
                'year' => $request->year,
                'month' => $request->month,
                'user_id' => $request->user_id,
            ],
            'years' => $years,
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
