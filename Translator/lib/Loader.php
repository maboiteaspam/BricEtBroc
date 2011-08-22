<?php
namespace BricEtBroc\Translator;

use BricEtBroc\Translator\Container as Container;
use BricEtBroc\Translator\Cache as Cache;
use BricEtBroc\Translator\FileLoader as FileLoader;

class Loader{
    
    protected $locale_containers;
    protected $translations_dirs;
    protected $cache_path;
    
    public function __construct( $translations_dirs, $cache_path){
        $this->translations_dirs    = $translations_dirs;
        $this->cache_path           = $cache_path;
        $this->locale_containers    = array();
    }
    
    public function buildFilesList( $locale ){
        $retour = array();
        $retour[] = strtolower(substr($locale,0,2)).".php";
        if( substr($locale,0,2) != substr($locale,3) ){
            $retour[] = substr($locale,0,2)."_".  strtoupper(substr($locale,0,2)).".php";
        }
        $retour[] = $locale.".php";
        return $retour;
    }
    
    public function buildTranslations( $locale ){
        $translations_files = $this->buildFilesList($locale);

        unset( $this->locale_containers[$locale] );
        $Container = new Container( $this->load_files($translations_files) );
        $this->locale_containers[$locale] = $Container;
        return $Container;
    }
    
    /**
     *
     * @param type $locale
     * @param type $translations_files 
     */
    public function getContainer( $locale ){
        return $this->locale_containers[$locale];
    }
    
    
    protected function load_files($translations_files){
        $translations   = array();
        $cache_path     = $this->cache_path;
        foreach( $this->translations_dirs as $tr_dir ){
            foreach( $translations_files as $translation_file ){
                $current_translation = array();
                $path_to_translation_file = $tr_dir.$translation_file;
                if(file_exists($path_to_translation_file) ){
                    $current_translation = include($path_to_translation_file);
                    $translations = array_merge( $translations, $current_translation );
                }
            }
        }
        return $translations;
    }
}