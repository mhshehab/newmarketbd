<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get setting value by key
     */
    function setting(string $key, $default = null)
    {
        return Setting::getValue($key, $default);
    }
}

if (!function_exists('get_website_name')) {
    /**
     * Get website name
     */
    function get_website_name()
    {
        return \App\Models\Setting::getValue('website_name', 'Default Name');
    }
}

if (!function_exists('get_website_title')) {
    /**
     * Get website title
     */
    function get_website_title()
    {
        return \App\Models\Setting::getValue('website_title', 'Default Title');
    }
}

if (!function_exists('get_website_logo')) {
    /**
     * Get website logo
     */
    function get_website_logo()
    {
        $logo = \App\Models\Setting::getValue('website_logo', 'images/logo.png');
        return asset('storage/' . $logo);
    }
}

if (!function_exists('get_favicon_icon')) {
    /**
     * Get favicon icon
     */
    function get_favicon_icon()
    {
        $favicon = \App\Models\Setting::getValue('website_favicon', 'images/favicon.ico');
        return asset('storage/' . $favicon);
    }
}
