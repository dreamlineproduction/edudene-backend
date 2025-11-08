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
    function generateSlug(string $string, string $separator = '-'): string
    {
        return Str::of($string)->lower()->replace(' ', $separator)->replaceMatches('/[^a-z0-9_]/', '');
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

        if (!is_null($data) && !empty($data)) {
            $response['data'] = $data;
        }
        
        return response()->json($response, $code);
    }
}

if(!function_exists('generateUniqueSlug')){
    function generateUniqueSlug($title, $model,  $id = null, $field = 'slug'){
        $separator = '-';

        if(!class_exists($model)){
            throw new Exception("Model class $model does not exist.");
        }

        if(empty($title)){
           throw new Exception("Title cannot be empty.");
        } 

        if($field !== 'slug'){
            $separator = '_';
        }

        $counter = 1;
        $baseSlug = generateSlug($title,$separator);
        $slug = $baseSlug;

        // Ensure it's unique
        while ($model::where($field, $slug)->where('id','!=',$id)->exists()) {
            $slug = $baseSlug . '_' . $counter++;
        }

        return $slug;
    }
}

if(!function_exists('getYouTubeId')) {
    function getYouTubeId(string $url) {
        if(empty($url)){
            return;
        }

        if (preg_match('/(?:youtube\.com\/.*v=|youtube\.com\/embed\/|youtu\.be\/)([A-Za-z0-9_-]{6,})/i', $url, $m)) {
            return $m[1];
        }

        // fallback: try parse query
        $parts = parse_url($url);
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $qs);
            return $qs['v'] ?? null;
        }
        return;
    }
}

if(!function_exists('isVimeo')){
    function isVimeo($url){
        if(empty($url)){
            return;
        }
        return (bool) preg_match('/vimeo\.com/i', $url);
    }
}

if(!function_exists('isYouTube')){
    function isYouTube($url){
        if(empty($url)){
            return;
        }
        return (bool) preg_match('/(youtube\.com|youtu\.be)/i', $url);
    }
}
?>