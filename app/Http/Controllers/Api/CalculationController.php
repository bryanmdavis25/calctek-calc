<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCalculationRequest;
use App\Models\Calculation;
use Illuminate\Http\JsonResponse;

class CalculationController extends Controller
{
    /**
     * Get all calculations.
     */
    public function index(): JsonResponse
    {
        $calculations = Calculation::orderByDesc('created_at')->get();

        return response()->json($calculations);
    }

    /**
     * Store a new calculation.
     */
    public function store(StoreCalculationRequest $request): JsonResponse
    {
        $calculation = Calculation::create($request->validated());

        return response()->json($calculation, 201);
    }

    /**
     * Delete a specific calculation.
     */
    public function destroy(Calculation $calculation): JsonResponse
    {
        $calculation->delete();

        return response()->json(['message' => 'Calculation deleted successfully.']);
    }

    /**
     * Clear all calculations.
     */
    public function clearAll(): JsonResponse
    {
        Calculation::truncate();

        return response()->json(['message' => 'All calculations cleared successfully.']);
    }
}
