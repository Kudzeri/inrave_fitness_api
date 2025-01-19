<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Войти в систему как пользователь и сгенерируй токен API",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|qwertyuiopasdfghjklzxcvbnm123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неверные учетные данные"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Поле email обязательно для заполнения.',
            'email.email' => 'Введите корректный email.',
            'password.required' => 'Поле пароль обязательно для заполнения.',
            'password.string' => 'Пароль должен быть строкой.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Неверные учетные данные'], 401);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Выйти из системы пользователя и удалить токен",
     *     tags={"Authentication"},
     *     security={{"BearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Успешный выход из системы",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Успешный выход из системы")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не найден или не аутентифицирован"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json(['error' => 'Пользователь не найден или не аутентифицирован'], 401);
            }

            $request->user()->tokens()->delete();

            return response()->json(['message' => 'Успешный выход из системы'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Произошла ошибка при выходе из системы', 'details' => $e->getMessage()], 500);
        }
    }

}
