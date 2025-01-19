<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
/**
 * @OA\Schema(
 *     schema="News",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Breaking News"),
 *     @OA\Property(property="description", type="string", example="This is a news description."),
 *     @OA\Property(property="image", type="string", nullable=true, example="news/image.jpg")
 * )
 */
class NewsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/news",
     *     summary="Получить список новостей",
     *     tags={"News"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/News"))
     *     )
     * )
     */
    public function index()
    {
        return response()->json(News::paginate(10));
    }

    /**
     * @OA\Post(
     *     path="/api/news",
     *     summary="Создать новую новость",
     *     tags={"News"},
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Breaking News"),
     *             @OA\Property(property="description", type="string", example="This is a news description."),
     *             @OA\Property(property="image", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Новость успешно создана",
     *         @OA\JsonContent(ref="#/components/schemas/News")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ], [
            'title.required' => 'Поле "Название" обязательно для заполнения.',
            'title.string' => 'Поле "Название" должно быть строкой.',
            'title.max' => 'Поле "Название" не может содержать больше 255 символов.',
            'description.string' => 'Поле "Описание" должно быть строкой.',
            'image.image' => 'Файл в поле "Изображение" должен быть изображением.',
            'image.max' => 'Размер изображения не должен превышать 2 МБ.',
        ]);


        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        }

        $news = News::create($data);

        return response()->json($news, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/news/{id}",
     *     summary="Получить конкретную новость по ID",
     *     tags={"News"},
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
     *         @OA\JsonContent(ref="#/components/schemas/News")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Новость не найдена"
     *     ),
     *     @OA\Response(
     *         response=501,
     *         description="Ошибка на стороне сервера"
     *      )
     * )
     */
    public function show(string $id)
    {
        try {
            $news = News::findOrFail($id);
            return response()->json($news, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Новость не найдена'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Произошла ошибка', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/news/{id}",
     *     summary="Обновить существующую новость",
     *     tags={"News"},
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
     *             @OA\Property(property="title", type="string", example="Updated News Title"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="image", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Новость успешно обновлена",
     *         @OA\JsonContent(ref="#/components/schemas/News")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Новость не найдена"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $news = News::findOrFail($id);

            $data = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|max:2048',
            ], [
                'title.required' => 'Поле "Название" обязательно, если оно указано.',
                'title.string' => 'Поле "Название" должно быть строкой.',
                'title.max' => 'Поле "Название" не может быть длиннее 255 символов.',
                'description.string' => 'Поле "Описание" должно быть строкой.',
                'image.image' => 'Загруженный файл должен быть изображением.',
                'image.max' => 'Размер изображения не должен превышать 2 МБ.',
            ]);

            if ($request->hasFile('image')) {
                if ($news->image) {
                    \Storage::disk('public')->delete($news->image);
                }
                $data['image'] = $request->file('image')->store('news', 'public');
            }

            $news->update($data);

            return response()->json($news, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Новость не найдена'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Ошибка валидации', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Произошла ошибка при обновлении', 'details' => $e->getMessage()], 500);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/news/{id}",
     *     summary="Удалить новость по ID",
     *     tags={"News"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Новость успешно удалена",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="News deleted successfully"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Новость не найдена"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $news = News::findOrFail($id);

            if ($news->image) {
                \Storage::disk('public')->delete($news->image);
            }

            $news->delete();

            return response()->json(['message' => 'Новость успешно удалена'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Новость не найдена'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Произошла ошибка при удалении', 'details' => $e->getMessage()], 500);
        }
    }

}
