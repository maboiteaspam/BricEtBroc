<?php
namespace BricEtBroc\Config;

use \BricEtBroc\Config\FileLoader as FileLoader;
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
    
    protected $dependant_files;
    
    public function __construct( $path_to_load_dirs=array(), $files_to_load=array() ){
        $this->path_to_load_dirs  = $path_to_load_dirs;
        $this->files_to_load      = $files_to_load;
        $this->reset();
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
    
    public function addPathToLoadDir( $path_to_load_dir, $at_the_end=true ){
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
    
    public function listOfFiles(){
        if( count($this->builded_lists) == 0 )
            $this->builded_lists = $this->buildListOfFiles($this->path_to_load_dirs, $this->files_to_load);
        return $this->builded_lists;
    }
    
    public function listOfDependantFiles(){
        return $this->dependant_files;
    }
    
    public function load(){
        $files  = $this->listOfFiles();
        $data   = array();
        foreach( $files as $file ){
            $loader = FileLoader::loadFile($file);
            $this->dependant_files[$file] = $loader->getMergedFiles();
            $data = array_merge_recursive($data, $loader->getData());
        }
        return $data;
    }
    
    public function completeListOfFiles(){
        $retour = $this->listOfFiles();
        foreach( $this->listOfDependantFiles() as $file => $dpdt_files ){
            $retour = array_merge($retour, $dpdt_files);
        }
        return $retour;
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
}
