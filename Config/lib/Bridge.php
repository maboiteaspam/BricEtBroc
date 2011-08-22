<?php
namespace BricEtBroc\Config;

use BricEtBroc\Config\Container as Container;
use BricEtBroc\Config\Cache as Cache;
use BricEtBroc\Config\FileLoader as FileLoader;

class Bridge{
    
    /**
     * Connect \BricEtBroc\Bridge
     *
     * @return BricEtBroc\Config\Container
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
                    Cache::save($cache_path, $loader);
                    $curent_config = $loader->getData();
                }
                $retour = array_merge( $retour, $curent_config );
            }
        }
        
        return Container::setInstance($retour);
    }
}