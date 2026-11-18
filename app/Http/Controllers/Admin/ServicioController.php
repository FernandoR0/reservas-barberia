<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServicioController extends Controller
{
    public function index(): View
    {
        return view('admin.servicios', ['servicios' => Servicio::orderBy('nombre')->get()]);
    }

    public function guardar(Request $request)
    {
        Servicio::create($this->validar($request));

        return back()->with('ok', 'Servicio creado.');
    }

    public function actualizar(Request $request, Servicio $servicio)
    {
        $servicio->update($this->validar($request));

        return back()->with('ok', "Servicio «{$servicio->nombre}» actualizado.");
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'duracion_minutos' => ['required', 'integer', 'min:10', 'max:240'],
            'precio' => ['required', 'numeric', 'min:0', 'max:9999'],
        ]) + ['activo' => $request->boolean('activo')];
    }
}
