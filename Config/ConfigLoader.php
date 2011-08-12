<?php
namespace BricEtBroc;

use \sfYaml as sfYaml;
use BricEtBroc\Super_Array_walk_recursive as Super_Array_walk_recursive;

class ConfigLoader{
    
    protected static $in_merge_config;
    
    public static function loadFile( $file_path ){
        self::$in_merge_config = array();
        self::$in_merge_config[] = $file_path;
        $meta_config = array(
                    "files"=>array(),
                    "config"=>array());
        $config                 = sfYaml::load($file_path);
        $meta_config["config"]  = self::parseConfig($config, $file_path);
        $meta_config["files"]   = self::$in_merge_config;
        return $meta_config;
    }
    
    public static function parseConfig($config, $file_path){
        $recurse = new Super_Array_walk_recursive($config, array('CKConfigLoader','detect_and_load_external'), dirname($file_path)."/" );
        if( $recurse->input ){
            return $recurse->input;
        }
        throw new Exception("Unparsable file : $file_path");
    }
    
    public static function detect_and_load_external($item, $key, $file_path){
        if( is_string($item) ){
            if( substr($item,0,3) === "::@" ){
                if( substr($item,3,4) === "/" ){
                    $external_file = substr($item,4);
                    self::$in_merge_config[] = $external_file;
                    $config = sfYaml::load($external_file);
                    $config = self::parseConfig($config, $file_path);
                }else{
                    $external_file = $file_path."/".substr($item,3);
                    self::$in_merge_config[] = $external_file;
                    $config = sfYaml::load($external_file);
                    $config = self::parseConfig($config, $file_path);
                }
                if( is_array($config) ) $item = $config;
            }
        }
    }
}