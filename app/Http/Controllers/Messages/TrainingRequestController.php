<?php

namespace App\Http\Controllers\Messages;

use App\Http\Controllers\Controller;
use App\Models\Messages\TrainingRequest;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Training Requests",
 *     description="API for managing training requests"
 * )
 */
class TrainingRequestController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/training-requests",
     *     summary="Create a training request",
     *     tags={"Training Requests"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone", "consent"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="message", type="string", example="I'd like to join boxing classes."),
     *             @OA\Property(property="consent", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=400, description="Bad Request")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string',
            'consent' => 'required|boolean',
        ]);

        $trainingRequest = TrainingRequest::create($validated);

        return response()->json($trainingRequest, 201);
    }
}
