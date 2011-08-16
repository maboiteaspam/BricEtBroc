<?php
namespace BricEtBroc;

use BricEtBroc\Container as Container;
use BricEtBroc\Cache as Cache;
use BricEtBroc\FileLoader as FileLoader;

class Bridge{
    
    /**
     * Connect \BricEtBroc\Bridge
     *
     * @return BricEtBroc\Container
     */
    public static function connect($path_to_config_files=array(), $cache_path=null ){
        $retour = array();
        
        foreach( $path_to_config_files as $path_to_config_file ){
            $curent_config = array();
            if(file_exists($path_to_config_file) ){
                if( Cache::isFresh($cache_path, $path_to_config_file) ){
                    $curent_config  = Cache::load($cache_path, $path_to_config_file);
                }else{
                    $loader  = FileLoader::loadFile($path_to_config_file);
                    Cache::save($cache_path, $path_to_config_file, $loader);
                    $curent_config = $loader->getData();
                }
                $retour = array_merge( $retour, $curent_config );
            }
        }
        
        return Container::setInstance($retour);
    }
}