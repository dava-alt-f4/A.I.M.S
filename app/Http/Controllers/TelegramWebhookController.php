<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();

        Log::info('Webhook Telegram Masuk:', $update);

        // Data Inject to Gemini here

        return response()->json(['status' => 'success']);
    }
}
