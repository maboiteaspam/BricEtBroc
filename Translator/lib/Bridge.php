<?php
namespace BricEtBroc\Translator;

use BricEtBroc\Translator\Container as Container;
use BricEtBroc\Translator\Loader as Loader;
use BricEtBroc\Translator\Cache as Cache;
use BricEtBroc\Translator\FileLoader as FileLoader;

class Bridge{
    
    /**
     * Connect \BricEtBroc\Translator
     *
     * @return BricEtBroc\Translator\Container
     */
    public static function connect($path_to_translations_dirs=array(), $cache_path=null ){
        return new Loader($path_to_translations_dirs, $cache_path);
    }
}