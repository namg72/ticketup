<?php

namespace App\Http\Controllers\Web\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index(Request $request)
    {
        // type: employee | supervisor (por defecto employee)
        $type = $request->input('type', 'employee');

        if ($type === 'employee') {
            // Usuarios con rol "employee"
            $query = User::role('employee');
        } elseif ($type === 'supervisor') {
            // Usuarios con rol "supervisor"
            $query = User::role('supervisor');
        }

        $supervisors = User::role(2) // ⬅️ Spatie busca en model_has_roles donde role_id = 2
            ->select('id', 'name') // ⬅️ Solo necesitamos el ID y el Nombre para el <el-select>
            ->get();


        $usersData = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Users/Users', [
            'users' => $usersData,
            'type'  => $type,
            'supervisors' => $supervisors
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
    public function store(UserRequest $request)
    {

        // 1. Generar Contraseña Temporal Segura
        $temporaryPassword = Hash::make(Str::random(12));

        $roleMap = [
            'supervisor' => 2,
            'employee' => 3,

        ];

        $roleId = $roleMap[$request->roleType];

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'supervisor_id' => $request->supervisor_id,
            'is_active' => $request->is_active || true,
            'password' =>  $temporaryPassword,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($roleId);


        // 2: Notificar al usuario (Email de Bienvenida con Contraseña Temporal)
        //Mail::to($user->email)->send(new WelcomeEmail($temporaryPassword));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

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
