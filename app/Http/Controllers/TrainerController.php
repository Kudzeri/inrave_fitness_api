<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use Illuminate\Http\Request;
/**
 * @OA\Schema(
 *     schema="Trainer",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="description", type="string", example="Experienced boxing trainer."),
 *     @OA\Property(property="photo", type="string", nullable=true, example="trainers/photo.jpg"),
 *     @OA\Property(
 *         property="services",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Service")
 *     )
 * )
 */

class TrainerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/trainers",
     *     summary="Получить список тренеров",
     *     tags={"Trainers"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Фильтровать тренеров по имени или фамилии",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="Фильтровать тренеров по услугам ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Trainer"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Trainer::with('services');

        // Filter by first name or last name
        if ($request->has('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->name . '%')
                    ->orWhere('last_name', 'like', '%' . $request->name . '%');
            });
        }

        // Filter by service ID
        if ($request->has('service_id')) {
            $query->whereHas('services', function ($q) use ($request) {
                $q->where('id', $request->service_id);
            });
        }

        $trainers = $query->paginate(10);

        return response()->json($trainers);
    }

    /**
     * @OA\Post(
     *     path="/api/trainers",
     *     summary="Создайть нового тренера",
     *     tags={"Trainers"},
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "description"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="description", type="string", example="Experienced boxing trainer."),
     *             @OA\Property(property="photo", type="string", format="binary"),
     *             @OA\Property(property="services", type="array", @OA\Items(type="integer", example=1))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Тренер успешно создан",
     *         @OA\JsonContent(ref="#/components/schemas/Trainer")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|max:2048',
            'services' => 'array',
            'services.*' => 'exists:services,id',
        ], [
            'first_name.required' => 'Поле "Имя" обязательно для заполнения.',
            'first_name.string' => 'Поле "Имя" должно быть строкой.',
            'first_name.max' => 'Поле "Имя" не может быть длиннее 255 символов.',
            'last_name.required' => 'Поле "Фамилия" обязательно для заполнения.',
            'last_name.string' => 'Поле "Фамилия" должно быть строкой.',
            'last_name.max' => 'Поле "Фамилия" не может быть длиннее 255 символов.',
            'description.required' => 'Поле "Описание" обязательно для заполнения.',
            'description.string' => 'Поле "Описание" должно быть строкой.',
            'photo.image' => 'Файл в поле "Фотография" должен быть изображением.',
            'photo.max' => 'Размер фотографии не должен превышать 2 МБ.',
            'services.array' => 'Поле "Услуги" должно быть массивом.',
            'services.*.exists' => 'Одна или несколько указанных услуг не найдены.',
        ]);


        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('trainers', 'public');
        }

        $trainer = Trainer::create($data);

        if (!empty($data['services'])) {
            $trainer->services()->attach($data['services']);
        }

        return response()->json($trainer->load('services'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/trainers/{id}",
     *     summary="Найти конкретного тренера по ID",
     *     tags={"Trainers"},
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
     *         @OA\JsonContent(ref="#/components/schemas/Trainer")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тренер не найден"
     *     )
     * )
     */
    public function show(string $id)
    {
        $trainer = Trainer::with('services')->findOrFail($id);
        return response()->json($trainer);
    }

    /**
     * @OA\Put(
     *     path="/api/trainers/{id}",
     *     summary="Обновить существующего тренера",
     *     tags={"Trainers"},
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
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="photo", type="string", format="binary"),
     *             @OA\Property(property="services", type="array", @OA\Items(type="integer", example=1))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Тренер успешно обновлен",
     *         @OA\JsonContent(ref="#/components/schemas/Trainer")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тренер не найден"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $trainer = Trainer::findOrFail($id);

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|max:2048',
            'services' => 'array',
            'services.*' => 'exists:services,id',
        ], [
            'first_name.required' => 'Поле "Имя" обязательно для заполнения.',
            'first_name.string' => 'Поле "Имя" должно быть строкой.',
            'first_name.max' => 'Поле "Имя" не может быть длиннее 255 символов.',
            'last_name.required' => 'Поле "Фамилия" обязательно для заполнения.',
            'last_name.string' => 'Поле "Фамилия" должно быть строкой.',
            'last_name.max' => 'Поле "Фамилия" не может быть длиннее 255 символов.',
            'description.required' => 'Поле "Описание" обязательно для заполнения.',
            'description.string' => 'Поле "Описание" должно быть строкой.',
            'photo.image' => 'Файл в поле "Фотография" должен быть изображением.',
            'photo.max' => 'Размер фотографии не должен превышать 2 МБ.',
            'services.array' => 'Поле "Услуги" должно быть массивом.',
            'services.*.exists' => 'Одна или несколько указанных услуг не найдены.',
        ]);


        if ($request->hasFile('photo')) {
            if ($trainer->photo) {
                \Storage::disk('public')->delete($trainer->photo);
            }
            $data['photo'] = $request->file('photo')->store('trainers', 'public');
        }

        $trainer->update(collect($data)->except('services')->toArray());

        if (isset($data['services'])) {
            $trainer->services()->sync($data['services']);
        }

        return response()->json($trainer->load('services'), 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/trainers/{id}",
     *     summary="Удалить тренера с помощью ID",
     *     tags={"Trainers"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Тренер успешно удален",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Trainer deleted successfully"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тренер не найден"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $trainer = Trainer::findOrFail($id);

        if ($trainer->photo) {
            \Storage::disk('public')->delete($trainer->photo);
        }

        $trainer->services()->detach();
        $trainer->delete();

        return response()->json(['message' => 'Тренер успешно удален'], 200);
    }
}
