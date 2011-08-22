<?php
namespace BricEtBroc\Translator;


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
    protected $loaded_data;
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
        return $this->loaded_data;
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
        $data = include($this->initial_path);
        $this->loaded_data = $data;
        return $this->loaded_data;
    }
}