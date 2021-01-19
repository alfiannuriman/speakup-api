<?php

namespace Lib;

class Helpers
{
    public static function generateFileName()
    {
        return uniqid(date('YmdHis'));
    }

    public static function getUploadedExtension($uploaded_filename)
    {
        $exploded_filename = explode('.', $uploaded_filename);
        return end($exploded_filename);
    }

    public static function getBaseUrl($add_slash = false)
    {
        $base_url = sprintf(
            "%s://%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME']
        );

        return $add_slash ? $base_url . '/' : $base_url;
    }
}