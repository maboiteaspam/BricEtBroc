<?php
namespace BricEtBroc\Locale;

class Finder{
    /**
     *
     * @param string $restrict_lang 
     * @return array
     */
    public static function expand_locales( $restrict_lang=null ){
        $retour = array();
        if( $restrict_lang === null ){
            $locale   = strtolower(locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']));
            $locale_2 = explode("_", $locale);
            $locale_2 = $locale_2[0];
            $retour = array($locale_2, $locale,);
        }else{
            $restrict_lang = substr($restrict_lang,0,2);
            $default = strtolower( $restrict_lang."_".strtoupper($restrict_lang) );
            $retour = array($restrict_lang,$default,);

            $locale   = strtolower(locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']));
            if(($locale) !== ($default) ){
                if( substr(($locale),0,2) == substr(($default),0,2) ){
                    $retour[] = ($locale);
                }
            }
        }
        return array_unique($retour);
    }
}