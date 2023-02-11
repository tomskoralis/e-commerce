<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function register(): JsonResponse
    {
        return response()->json([
            'test' => '123',
        ]);
    }
}
