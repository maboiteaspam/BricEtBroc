<?php
namespace BricEtBroc;

use BricEtBroc\Config as Config;
use BricEtBroc\ConfigCache as ConfigCache;
use BricEtBroc\ConfigLoader as ConfigLoader;

class ConfigBridge{
    
    /**
     * Connect \BricEtBroc\ConfigBridge
     *
     * @return BricEtBroc\ConfigBridge
     */
    public static function connect($path_to_config_files=array(), $cache_path=null ){
        $retour = array();
        
        foreach( $path_to_config_files as $path_to_config_file ){
            $curent_config = array();
            if(file_exists($path_to_config_file) ){
                if( ConfigCache::isFresh($cache_path, $path_to_config_file) ){
                    $curent_config  = ConfigCache::load($cache_path, $path_to_config_file);
                    $retour         = array_merge( $retour, $curent_config );
                }else{
                    $curent_config  = ConfigLoader::loadFile($path_to_config_file);
                    ConfigCache::save($cache_path, $path_to_config_file, $curent_config);
                    $curent_config  = $curent_config["config"];
                    $retour         = array_merge( $retour, $curent_config );
                }
            }
        }
        
        return Config::setInstance($retour);
    }
}