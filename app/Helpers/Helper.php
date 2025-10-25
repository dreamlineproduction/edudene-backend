<?php
use Illuminate\Support\Str;

if (!function_exists('notEmpty')) {
    /**
     * Generates a slug-based username from a full name.
     *
     * @param string $fullName
     * @return string
     */
    function notEmpty($value)
    {
        if(!empty($value)){
            return true;
        }
        
        return false;
    }
}

if (!function_exists('generateSlug')) {
    /**
     * Generates a slug-based username from a full name.
     *
     * @param string $fullName
     * @return string
     */
    function generateSlug(string $fullName): string
    {
        return Str::of($fullName)->lower()->replace(' ', '_')->replaceMatches('/[^a-z0-9_]/', '');
    }
}


if (!function_exists('getDefaultTimezone')) {
    /**
     * Generates a slug-based username from a full name.
     *
     * @param string $timezone
     * @return string
     */
    function getDefaultTimezone($timezone = null)
    {   
        if(empty($timezone)){
            return 'UTC';
        }

        return $timezone;        
    }
}

if (!function_exists('jsonResponse')) {
   function jsonResponse($status, $message, $data = [], $code = 200)
    {
        $response = [
            'status' => $status,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }
        
        return response()->json($response, $code);
    }
}

if(!function_exists('generateUniqueSlug')){
    function generateUniqueSlug($title, $model,  $id = null, $field = 'slug'){
        if(!class_exists($model)){
            throw new Exception("Model class $model does not exist.");
        }

        if(empty($title)){
           throw new Exception("Title cannot be empty.");
        } 

        $counter = 1;
        $baseSlug = generateSlug($title);
        $slug = $baseSlug;

        // Ensure it's unique
        while ($model::where($field, $slug)->where('id','!=',$id)->exists()) {
            $slug = $baseSlug . '_' . $counter++;
        }

        return $slug;
    }
}
?>