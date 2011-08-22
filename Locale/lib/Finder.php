<?php
namespace BricEtBroc\Locale;

class Finder{
    public static function get_best_locale(){
        if( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) )
            return locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }
}