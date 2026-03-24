<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;

class LanguageController extends Controller
{
    //
    public function index()
    {
        $languages = Language::where('status','Active')
        ->orderByRaw("CASE WHEN title = 'English' THEN 0 ELSE 1 END")
        ->orderBy('title', 'ASC')
        ->get();

        return jsonResponse(true, 'Language fetched successfully', [
            'languages' => $languages,            
        ]);
    }
}
