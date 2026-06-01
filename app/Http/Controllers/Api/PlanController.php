<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Plan::class);

        $plans = Plan::query()
            ->where('activo', true)
            ->orderBy('precio_mensual')
            ->paginate(15);

        return response()->json($plans);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Plan::class);

        try {
            $tenantId = auth()->user()->tenant_id;
            $data = $request->validate([
                'nombre' => [
                    'required', 'string', 'max:100',
                    Rule::unique('plans', 'nombre')->where('tenant_id', $tenantId),
                ],
                'descripcion' => ['nullable', 'string'],
                'precio_mensual' => ['required', 'numeric', 'min:0'],
                'precio_anual' => ['required', 'numeric', 'min:0'],
                'control_ventas_stock' => ['required', 'boolean'],
                'max_usuarios' => ['required', 'integer', 'min:1'],
                'nivel_reportes' => ['required', Rule::in(['basico', 'avanzado', 'premium'])],
                'activo' => ['required', 'boolean'],
            ]);

            $plan = Plan::create($data);

            return response()->json($plan, 201);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Database error', 'error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Unexpected error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Plan $plan): JsonResponse
    {
        $this->authorize('view', $plan);
        return response()->json($plan);
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $this->authorize('update', $plan);

        try {
            $tenantId = auth()->user()->tenant_id;
            $data = $request->validate([
                'nombre' => [
                    'required', 'string', 'max:100',
                    Rule::unique('plans', 'nombre')->where('tenant_id', $tenantId)->ignore($plan->id),
                ],
                'descripcion' => ['nullable', 'string'],
                'precio_mensual' => ['required', 'numeric', 'min:0'],
                'precio_anual' => ['required', 'numeric', 'min:0'],
                'control_ventas_stock' => ['required', 'boolean'],
                'max_usuarios' => ['required', 'integer', 'min:1'],
                'nivel_reportes' => ['required', Rule::in(['basico', 'avanzado', 'premium'])],
                'activo' => ['required', 'boolean'],
            ]);

            $plan->update($data);

            return response()->json($plan);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Database error', 'error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Unexpected error', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $this->authorize('delete', $plan);

        try {
            $plan->delete();
            return response()->json(null, 204);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Unexpected error', 'error' => $e->getMessage()], 500);
        }
    }
}

