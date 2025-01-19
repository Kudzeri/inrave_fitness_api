<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
/**
 * @OA\Schema(
 *     schema="Service",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Boxing Training"),
 *     @OA\Property(property="description", type="string", example="Intensive boxing training sessions."),
 *     @OA\Property(property="price", type="number", format="float", example="99.99"),
 *     @OA\Property(property="image", type="string", nullable=true, example="services/image.jpg"),
 *     @OA\Property(
 *         property="trainers",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Trainer")
 *     )
 * )
 */

class ServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/services",
     *     summary="Получите список услуг",
     *     tags={"Services"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Фильтровать услуги по названию",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Фильтровать услуги по минимальной цене",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Фильтровать услуги по максимальной цене",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="trainer_id",
     *         in="query",
     *         description="Фильтровать услуги по идентификатору тренера",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Service"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Service::with('trainers');

        // Filter by title
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Filter by minimum and maximum price
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by trainer
        if ($request->has('trainer_id')) {
            $query->whereHas('trainers', function ($q) use ($request) {
                $q->where('id', $request->trainer_id);
            });
        }

        $services = $query->paginate(5);

        return response()->json($services);
    }

    /**
     * @OA\Post(
     *     path="/api/services",
     *     summary="Создать новую услугу",
     *     tags={"Services"},
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "price"},
     *             @OA\Property(property="title", type="string", example="Boxing Training"),
     *             @OA\Property(property="description", type="string", example="Intensive boxing training sessions."),
     *             @OA\Property(property="price", type="number", format="float", example="99.99"),
     *             @OA\Property(property="trainers", type="array", @OA\Items(type="integer", example=1)),
     *             @OA\Property(property="image", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Услуга успешно создана",
     *         @OA\JsonContent(ref="#/components/schemas/Service")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'trainers' => 'array',
            'trainers.*' => 'exists:trainers,id',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
        ], [
            'title.required' => 'Поле "Название" обязательно для заполнения.',
            'title.string' => 'Поле "Название" должно быть строкой.',
            'title.max' => 'Поле "Название" не может быть длиннее 255 символов.',
            'description.required' => 'Поле "Описание" обязательно для заполнения.',
            'description.string' => 'Поле "Описание" должно быть строкой.',
            'trainers.array' => 'Поле "Тренеры" должно быть массивом.',
            'trainers.*.exists' => 'Один или несколько указанных тренеров не существуют.',
            'price.required' => 'Поле "Цена" обязательно для заполнения.',
            'price.numeric' => 'Поле "Цена" должно быть числом.',
            'image.image' => 'Файл в поле "Изображение" должен быть изображением.',
            'image.max' => 'Размер изображения не должен превышать 2 МБ.',
        ]);


        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $serviceData = collect($data)->except('trainers')->toArray();
        $service = Service::create($serviceData);

        if (!empty($data['trainers'])) {
            $service->trainers()->attach($data['trainers']);
        }

        return response()->json($service->load('trainers'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/services/{id}",
     *     summary="Получить конкретную услугу можно по ID",
     *     tags={"Services"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(ref="#/components/schemas/Service")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Услуга не найдена"
     *     )
     * )
     */
    public function show(string $id)
    {
        $service = Service::with('trainers')->findOrFail($id);
        return response()->json($service);
    }

    /**
     * @OA\Put(
     *     path="/api/services/{id}",
     *     summary="Обновить существующую услугу",
     *     tags={"Services"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Advanced Boxing Training"),
     *             @OA\Property(property="description", type="string", example="Advanced techniques in boxing."),
     *             @OA\Property(property="price", type="number", format="float", example="150.00"),
     *             @OA\Property(property="trainers", type="array", @OA\Items(type="integer", example=1)),
     *             @OA\Property(property="image", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Услуга успешно обновлена",
     *         @OA\JsonContent(ref="#/components/schemas/Service")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Услуга не найдена"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'trainers' => 'array',
            'trainers.*' => 'exists:trainers,id',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
        ], [
            'title.required' => 'Поле "Название" обязательно для заполнения.',
            'title.string' => 'Поле "Название" должно быть строкой.',
            'title.max' => 'Поле "Название" не может быть длиннее 255 символов.',
            'description.required' => 'Поле "Описание" обязательно для заполнения.',
            'description.string' => 'Поле "Описание" должно быть строкой.',
            'trainers.array' => 'Поле "Тренеры" должно быть массивом.',
            'trainers.*.exists' => 'Один или несколько указанных тренеров не существуют.',
            'price.required' => 'Поле "Цена" обязательно для заполнения.',
            'price.numeric' => 'Поле "Цена" должно быть числом.',
            'image.image' => 'Файл в поле "Изображение" должен быть изображением.',
            'image.max' => 'Размер изображения не должен превышать 2 МБ.',
        ]);


        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($service->image) {
                \Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $service->update(collect($data)->except('trainers')->toArray());

        if (isset($data['trainers'])) {
            $service->trainers()->sync($data['trainers']);
        }

        return response()->json($service->load('trainers'), 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/services/{id}",
     *     summary="Удалите услугу с помощью ID",
     *     tags={"Services"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Услуга успешно удалена",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Service deleted successfully"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Услуга не найдена"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);

        if ($service->image) {
            \Storage::disk('public')->delete($service->image);
        }

        $service->trainers()->detach();
        $service->delete();

        return response()->json(['message' => 'Услуга успешно удалена'], 200);
    }
}
