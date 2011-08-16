<?php
namespace BricEtBroc;

use \sfYaml as sfYaml;
use BricEtBroc\Super_Array_walk_recursive as Super_Array_walk_recursive;

class FileLoader{
    
    /**
     *
     * @var array
     */
    protected $merged_files;
    
    /**
     *
     * @var array
     */
    protected $loaded_config;
    /**
     *
     * @var string
     */
    protected $initial_path;
    
    public function __construct( $file_path ){
        $this->merged_files = array();
        $this->initial_path = $file_path;
    }
    
    /**
     *
     * @param string $file_path
     * @return FileLoader 
     */
    public static function loadFile( $file_path ){
        $retour = new FileLoader( $file_path );
        $retour->load();
        return $retour;
    }
    
    /**
     *
     * @return array|null
     */
    public function getData(){
        return $this->loaded_config;
    }
    
    /**
     *
     * @return array
     */
    public function getMergedFiles(){
        return $this->merged_files;
    }
    
    /**
     *
     * @return string
     */
    public function getConfigFilePath(){
        return $this->initial_path;
    }
    
    /**
     *
     * @return array 
     */
    public function load( ){
        $this->merged_files     = array();
        $this->merged_files[]   = $this->initial_path;
        $config = sfYaml::load($this->initial_path);
        $config = $this->lookup_for_externals($config, $this->initial_path);
        $this->loaded_config = $config;
        return $this->loaded_config;
    }
    
    /**
     *
     * @param array $config
     * @param string $file_path
     * @return type 
     */
    protected function lookup_for_externals($config, $file_path){
        $recurse = new Super_Array_walk_recursive($config, array($this,'detect_and_load_external'), dirname($file_path)."" );
        if( $recurse->input ){
            return $recurse->input;
        }
        throw new Exception("Unparsable file : $file_path");
    }
    
    /**
     *
     * @param string|array $item
     * @param string $key
     * @param string $relative_config_dir 
     */
    public function detect_and_load_external($item, $key, $relative_config_dir){
        if( is_string($item) ){
            if( substr($item,0,3) === "::@" ){
                if( substr($item,3,1) === "/" ){
                    $external_file = substr($item,3);
                    $this->merged_files[] = $external_file;
                    $config = sfYaml::load($external_file);
                    $config = $this->lookup_for_externals($config, $relative_config_dir);
                }else{
                    $external_file = $relative_config_dir."/".substr($item,3);
                    $this->merged_files[] = $external_file;
                    $config = sfYaml::load($external_file);
                    $config = $this->lookup_for_externals($config, $relative_config_dir);
                }
                if( is_array($config) ) $item = $config;
            }
        }
    }
}