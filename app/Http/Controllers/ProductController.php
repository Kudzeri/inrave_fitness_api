<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Sample Product"),
 *     @OA\Property(property="description", type="string", example="This is a sample product."),
 *     @OA\Property(property="price", type="number", format="float", example=19.99),
 *     @OA\Property(property="color", type="string", example="Red"),
 *     @OA\Property(property="composition", type="string", example="Cotton"),
 *     @OA\Property(property="images", type="array", @OA\Items(type="string", example="products/image1.jpg"))
 * )
 */

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Получить список товаров с фильтрами и разбивкой по страницам",
     *     tags={"Products"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Фильтровать товары по названию",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Фильтруйте товары по минимальной цене",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Фильтровать товары по максимальной цене",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->paginate(10);

        return response()->json($products);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Создать новый товар",
     *     tags={"Products"},
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "price"},
     *             @OA\Property(property="title", type="string", example="Sample Product"),
     *             @OA\Property(property="description", type="string", example="This is a sample product."),
     *             @OA\Property(property="price", type="number", format="float", example="19.99"),
     *             @OA\Property(property="color", type="string", example="Red"),
     *             @OA\Property(property="composition", type="string", example="Cotton"),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string", format="binary"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Товар успешно создан",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'color' => 'nullable|string|max:50',
            'composition' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ], [
            'title.required' => 'Поле "Название" обязательно для заполнения.',
            'title.string' => 'Поле "Название" должно быть строкой.',
            'title.max' => 'Поле "Название" не может быть длиннее 255 символов.',
            'description.string' => 'Поле "Описание" должно быть строкой.',
            'price.required' => 'Поле "Цена" обязательно для заполнения.',
            'price.numeric' => 'Поле "Цена" должно быть числом.',
            'color.string' => 'Поле "Цвет" должно быть строкой.',
            'color.max' => 'Поле "Цвет" не может быть длиннее 50 символов.',
            'composition.string' => 'Поле "Состав" должно быть строкой.',
            'composition.max' => 'Поле "Состав" не может быть длиннее 255 символов.',
            'images.array' => 'Поле "Изображения" должно быть массивом.',
            'images.*.image' => 'Каждый файл в поле "Изображения" должен быть изображением.',
            'images.*.max' => 'Каждый файл в поле "Изображения" не должен превышать 2 МБ.',
        ]);


        if ($request->has('images')) {
            $data['images'] = array_map(function ($image) {
                return $image->store('products', 'public');
            }, $request->file('images'));
        }

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Получить конкретный товар по ID",
     *     tags={"Products"},
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
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Товар не найден"
     *     )
     * )
     */
    public function show(string $id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Обновить существующий товар",
     *     tags={"Products"},
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
     *             @OA\Property(property="title", type="string", example="Updated Product Title"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="price", type="number", format="float", example="29.99"),
     *             @OA\Property(property="color", type="string", example="Blue"),
     *             @OA\Property(property="composition", type="string", example="Polyester"),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string", format="binary"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Товар успешно обновлен",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Товар не найден"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'color' => 'nullable|string|max:50',
            'composition' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ], [
            'title.required' => 'Поле "Название" обязательно для заполнения.',
            'title.string' => 'Поле "Название" должно быть строкой.',
            'title.max' => 'Поле "Название" не может быть длиннее 255 символов.',
            'description.string' => 'Поле "Описание" должно быть строкой.',
            'price.required' => 'Поле "Цена" обязательно для заполнения.',
            'price.numeric' => 'Поле "Цена" должно быть числом.',
            'color.string' => 'Поле "Цвет" должно быть строкой.',
            'color.max' => 'Поле "Цвет" не может быть длиннее 50 символов.',
            'composition.string' => 'Поле "Состав" должно быть строкой.',
            'composition.max' => 'Поле "Состав" не может быть длиннее 255 символов.',
            'images.array' => 'Поле "Изображения" должно быть массивом.',
            'images.*.image' => 'Каждый файл в поле "Изображения" должен быть изображением.',
            'images.*.max' => 'Каждый файл в поле "Изображения" не должен превышать 2 МБ.',
        ]);


        if ($request->has('images')) {
            if ($product->images) {
                foreach ($product->images as $image) {
                    \Storage::disk('public')->delete($image);
                }
            }
            $data['images'] = array_map(function ($image) {
                return $image->store('products', 'public');
            }, $request->file('images'));
        }

        $product->update($data);

        return response()->json($product, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Удаление товара по ID",
     *     tags={"Products"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Товар успешно удален",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Product deleted successfully"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Товар не найден"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        if ($product->images) {
            foreach ($product->images as $image) {
                \Storage::disk('public')->delete($image);
            }
        }

        $product->delete();

        return response()->json(['message' => 'Товар успешно удален'], 200);
    }
}
