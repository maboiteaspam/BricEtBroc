<?php
namespace BricEtBroc\Translator;

use BricEtBroc\Translator\Container as Container;
use BricEtBroc\Translator\Cache as Cache;
use BricEtBroc\Translator\FileLoader as FileLoader;

class Loader{
    
    protected $locale_containers;
    protected $translations_files;
    protected $translations_dirs;
    protected $cache_path;
    
    public function __construct( $translations_dirs, $cache_path ){
        $this->translations_dirs = $translations_dirs;
        $this->cache_path = $cache_path;
    }
    
    /**
     *
     * @param type $locale
     * @param type $translations_files 
     */
    public function getTranslations( $locale, $translations_files ){
        $this->translations_files[$locale] = $translations_files;
        unset( $this->locale_containers[$locale] );
        $data = $this->load_files($locale);
        $Container = new Container( $data );
        $Container->setLocale( $locale );
        $this->locale_containers[$locale] = $Container;
        return $Container;
    }
    
    
    protected function load_files($locale){
        $translations = array();
        $cache_path = $this->cache_path;
        foreach( $this->translations_dirs as $tr_dir ){
            foreach( $this->translations_files[$locale] as $translation_file ){
                $curent_translation = array();
                $path_to_translation_file = $tr_dir.$translation_file;
                if(file_exists($path_to_translation_file) ){
                    if( Cache::isFresh($cache_path, $path_to_translation_file) ){
                        $curent_translation  = Cache::load($cache_path, $path_to_translation_file);
                    }else{
                        $loader  = FileLoader::loadFile($path_to_translation_file);
                        Cache::save($cache_path, $loader);
                        $curent_translation = $loader->getData();
                    }
                    $translations = array_merge( $translations, $curent_translation );
                }
            }
        }
        return $translations;
    }
}