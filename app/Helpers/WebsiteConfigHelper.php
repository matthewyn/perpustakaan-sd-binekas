<?php

namespace App\Helpers;

class WebsiteConfigHelper
{
    private static $config = null;

    public static function getConfig()
    {
        if (self::$config === null) {
            self::loadConfig();
        }
        return self::$config;
    }

    private static function loadConfig()
    {
        $supabaseUrl = getenv('SUPABASE_URL');
        $supabaseKey = getenv('SUPABASE_API_KEY');

        if (empty($supabaseUrl) || empty($supabaseKey)) {
            self::$config = config('Website');
            return;
        }

        $url = rtrim($supabaseUrl, '/') . '/rest/v1/website_config?limit=1';
        
        $headers = [
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $supabaseKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (is_array($data) && isset($data[0])) {
                self::$config = (object) [
                    'siteName' => $data[0]['site_name'] ?? config('Website')->siteName,
                    'navbarLogo' => $data[0]['navbar_logo'] ?? config('Website')->navbarLogo,
                    'homepageLogo' => $data[0]['homepage_logo'] ?? config('Website')->homepageLogo,
                    'homepageTitle' => $data[0]['homepage_title'] ?? config('Website')->homepageTitle,
                    'homepageDecorativeImage' => $data[0]['homepage_decorative_image'] ?? config('Website')->homepageDecorativeImage,
                    'profileImage' => $data[0]['profile_image'] ?? config('Website')->profileImage,
                    'latestBooksTitle' => $data[0]['latest_books_title'] ?? config('Website')->latestBooksTitle,
                    'loginBackgroundImage' => $data[0]['login_background_image'] ?? config('Website')->loginBackgroundImage,
                ];
                return;
            }
        }

        self::$config = config('Website');
    }

    public static function getSiteName()
    {
        return self::getConfig()->siteName;
    }

    public static function getNavbarLogo()
    {
        return self::getConfig()->navbarLogo;
    }

    public static function getHomepageLogo()
    {
        return self::getConfig()->homepageLogo;
    }

    public static function getLoginBackgroundImage()
    {
        return self::getConfig()->loginBackgroundImage;
    }

    public static function getHomepageDecorativeImage()
    {
        return self::getConfig()->homepageDecorativeImage;
    }

    public static function getProfileImage()
    {
        return self::getConfig()->profileImage;
    }
}
