<?php
use Illuminate\Support\Str;

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
?>