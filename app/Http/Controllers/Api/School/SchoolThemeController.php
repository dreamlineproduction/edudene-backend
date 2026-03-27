<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolTheme;
use Illuminate\Http\Request;

class SchoolThemeController extends Controller
{
    public function update(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return jsonResponse(false, 'Unauthorized', null, 401);
        }

        $school = $user->school()->first();
        
        if (!$school) {
            return jsonResponse(false, 'School not found', null, 404);
        }

        $validated = $request->validate([
            'primary_color' => 'nullable|string|max:20',
            'primary_hover_color' => 'nullable|string|max:20',
            'primary_outline_color' => 'nullable|string|max:20',
            'primary_outline_hover_color' => 'nullable|string|max:20',

            'primary_text_color' => 'nullable|string|max:20',
            'primary_hover_text_color' => 'nullable|string|max:20',
            'primary_outline_text_color' => 'nullable|string|max:20',
            'primary_outline_hover_text_color' => 'nullable|string|max:20',

            'secondary_color' => 'nullable|string|max:20',
            'secondary_hover_color' => 'nullable|string|max:20',
            'secondary_outline_color' => 'nullable|string|max:20',
            'secondary_outline_hover_color' => 'nullable|string|max:20',

            'secondary_text_color' => 'nullable|string|max:20',
            'secondary_hover_text_color' => 'nullable|string|max:20',
            'secondary_outline_text_color' => 'nullable|string|max:20',
            'secondary_outline_hover_text_color' => 'nullable|string|max:20',

            'logo' => 'nullable',
            'banner' => 'nullable',
        ]);

        // Theme update/create
        $theme = SchoolTheme::updateOrCreate(
            ['school_id' => $school->id],
            [
                'primary_color' => $validated['primary_color'] ?? null,
                'primary_hover_color' => $validated['primary_hover_color'] ?? null,
                'primary_outline_color' => $validated['primary_outline_color'] ?? null,
                'primary_outline_hover_color' => $validated['primary_outline_hover_color'] ?? null,

                'primary_text_color' => $validated['primary_text_color'] ?? null,
                'primary_hover_text_color' => $validated['primary_hover_text_color'] ?? null,
                'primary_outline_text_color' => $validated['primary_outline_text_color'] ?? null,
                'primary_outline_hover_text_color' => $validated['primary_outline_hover_text_color'] ?? null,

                'secondary_color' => $validated['secondary_color'] ?? null,
                'secondary_hover_color' => $validated['secondary_hover_color'] ?? null,
                'secondary_outline_color' => $validated['secondary_outline_color'] ?? null,
                'secondary_outline_hover_color' => $validated['secondary_outline_hover_color'] ?? null,

                'secondary_text_color' => $validated['secondary_text_color'] ?? null,
                'secondary_hover_text_color' => $validated['secondary_hover_text_color'] ?? null,
                'secondary_outline_text_color' => $validated['secondary_outline_text_color'] ?? null,
                'secondary_outline_hover_text_color' => $validated['secondary_outline_hover_text_color'] ?? null,
            ]
        );

        if ($request->filled('logo')) {
            if ($theme->logo) {
                deleteS3File($theme->logo);
            }

            $finalFile = finalizeFile($request->logo, 'schools');

            $theme->logo_image = $finalFile['path'];
            $theme->logo_image_url = $finalFile['url'];
        }

        // Banner Upload (School table)
        if ($request->filled('banner')) {
            if ($theme->banner_image) {
                deleteS3File($theme->banner_image);
            }

            $finalFile = finalizeFile($request->banner, 'schools');

            $theme->banner_image = $finalFile['path'];
            $theme->banner_image_url = $finalFile['url'];
        }

        $theme->save();

        return jsonResponse(true, 'Theme saved successfully', [
            'theme' => $theme,
        ]);
    }


    public function show(string $id)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return jsonResponse(false, 'Unauthorized', null, 401);
        }

        $school = $user->school()->first();
        
        if (!$school) {
            return jsonResponse(false, 'School not found', null, 404);
        }

        $schoolTheme = SchoolTheme::where('school_id',$school->id)->first();

        if (!$school) {
            return jsonResponse(false, 'School not found', null, 404);
        }

        return jsonResponse(true, 'School theme details fetched', [
            'theme' => $schoolTheme
        ]);
    }
}
