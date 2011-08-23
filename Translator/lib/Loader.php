<?php
namespace BricEtBroc\Translator;

use \BricEtBroc\Translator\Container as Container;
use \BricEtBroc\FilesLoader\ILoader as ILoader;

/**
 * Description of Loader
 *
 * @author clement
 */
class Loader implements ILoader{
    
    protected $path_to_load_dirs;
    protected $files_to_load;
    protected $builded_lists;
    protected $container;
    
    protected $locale;
    
    public function __construct( $translations_dirs ){
        $this->path_to_load_dirs    = $translations_dirs;
        $this->files_to_load        = array();
        $this->locale               = "";
    }
    
    public function reset(){
        $this->builded_lists        = array();
        $this->dependant_files      = array();
        $this->container            = null;
    }
    
    public function addFileToLoad( $file_to_load, $at_the_end=true ){
        if( in_array($file_to_load, $this->files_to_load) === false ){
            $this->reset();
            if( $at_the_end )
                $this->files_to_load[] = $file_to_load;
            else
                array_unshift ($this->files_to_load, $file_to_load);
            return true;
        }
        return false;
    }
    
    public function addPathToLoadDir( $path_to_config_dir, $at_the_end=true ){
        if( in_array($path_to_load_dir, $this->path_to_load_dirs) === false ){
            $this->reset();
            if( $at_the_end )
                $this->path_to_load_dirs[] = $path_to_load_dir;
            else
                array_unshift ($this->path_to_load_dirs, $path_to_load_dir);
            return true;
        }
        return false;
    }
    
    public function buildListOfFiles($path_to_config_dirs, $files_to_load){
        $retour = array();
        foreach( $path_to_config_dirs as $path_to_config_dir ){
            foreach ($files_to_load as $file_to_load ){
                $retour[] = $path_to_config_dir.$file_to_load;
            }
        }
        return $retour;
    }
    
    public function listOfFiles( ){
        if( count($this->builded_lists) == 0 )
            $this->builded_lists = $this->buildListOfFiles($this->path_to_load_dirs, $this->files_to_load);
        return $this->builded_lists;
    }
    
    public function completeListOfFiles( ){
        return $this->listOfFiles();
    }
    
    public function load(){
        $files  = $this->listOfFiles();
        $data   = array();
        foreach( $files as $file ){
            if(file_exists($file) ){
                $current_data = include($file);
                $data = array_merge($data, $current_data);
            }
        }
        return $data;
    }
    
    public function createResponse( array $data ){
        return new Container($data);
    }
    
    public function get(){
        if( $this->container === null ){
            $this->container = $this->createResponse( $this->load() );
        }
        return $this->container;
    }
    
    public function setLocale( $locale ){
        if( $locale != $this->locale ){
            $this->buildFilesListForLocale( $locale );
            $this->reset();
        }
        $this->locale = $locale;
    }
    
    public function buildFilesListForLocale( $locale ){
        $retour = array();
        $this->addFileToLoad(strtolower(substr($locale,0,2)).".php");
        if( substr($locale,0,2) != substr($locale,3) ){
            $this->addFileToLoad(substr($locale,0,2)."_".  strtoupper(substr($locale,0,2)).".php");
        }
        $this->addFileToLoad( $locale.".php");
        return $this->files_to_load;
    }
}