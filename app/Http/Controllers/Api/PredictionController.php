<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function __construct(
        protected PredictionService $predictionService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/prediction/student/{id}",
     *     summary="Get student prediction",
     *     tags={"Prediction"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Student prediction data"),
     *     @OA\Response(response=404, description="Student not found")
     * )
     */
    public function studentPrediction(Request $request, int $id): JsonResponse
    {
        $prediction = $this->predictionService->predictStudentSuccess($id);
        
        if (isset($prediction['error'])) {
            return response()->json(['error' => $prediction['error']], 404);
        }
        
        return response()->json($prediction);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/prediction/statistics",
     *     summary="Get class statistics",
     *     tags={"Prediction"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Class statistics data")
     * )
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->predictionService->getClassStatistics();
        return response()->json($statistics);
    }
}
