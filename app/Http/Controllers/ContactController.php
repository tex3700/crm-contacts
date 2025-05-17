<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Вывод списка контактов.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $tag = $request->query('tag');

        $contacts = Contact::when($tag, function ($query) use ($tag) {
            return $query->whereJsonContains('tags', $tag);
        })->get();

        return response()->json($contacts);
    }

    /**
     * Сохранение нового контакта.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email',
            'phone' => 'nullable|string|max:20',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact = Contact::create($request->all());

        // Здесь может быть логика отправки
        $this->sendTelegramNotification($contact);

        return response()->json($contact, 201);
    }

    /**
     * Вывод одиночного контакта.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        return response()->json($contact);
    }

    /**
     * Обновление одиночного контакта.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'email|unique:contacts,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact->update($request->all());

        return response()->json($contact);
    }

    /**
     * Удаление одиночного контакта.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return response()->json(null, 204);
    }

    /**
     * Отправка Telegram уведомления о новом контакте.
     *
     * @param  Contact  $contact
     * @return void
     */
    private function sendTelegramNotification(Contact $contact): void
    {
        // Это псевдореализация уведомления Telegram
        // В реальном приложении необходимо использовать API Telegram Bot

        $botToken = 'YOUR_BOT_TOKEN'; // Заменить на актуальный bot token
        $chatId = 'YOUR_CHAT_ID'; // Заменить на актуальный chat ID

        $message = "New Contact Created!\n"
            . "Name: {$contact->name}\n"
            . "Email: {$contact->email}\n"
            . "Phone: {$contact->phone}\n";

        // В реальной реализации здесь должна быть отправка HTTP-запроса к API Telegram
        // Для демонстрационных целей мы просто запишем это
        \Log::info('Telegram notification would be sent with message: ' . $message);
    }
}
