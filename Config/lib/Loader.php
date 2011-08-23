<?php
namespace BricEtBroc\Config;

use BricEtBroc\Config\FileLoader as FileLoader;

/**
 * Description of Loader
 *
 * @author clement
 */
class Loader {
    protected $path_to_config_dirs;
    protected $files_to_load;
    protected $builded_lists;
    protected $dependant_files;
    
    public function __construct( $path_to_config_dirs=array(), $files_to_load=array() ){
        $this->path_to_config_dirs  = $path_to_config_dirs;
        $this->files_to_load        = $files_to_load;
        $this->reset();
    }
    
    public function reset(){
        $this->unique_name          = null;
        $this->builded_lists        = array();
        $this->dependant_files      = array();
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
    
    public function addPathToConfigDir( $path_to_config_dir, $at_the_end=true ){
        if( in_array($path_to_config_dir, $this->path_to_config_dirs) === false ){
            $this->reset();
            if( $at_the_end )
                $this->path_to_config_dirs[] = $path_to_config_dir;
            else
                array_unshift ($this->path_to_config_dirs, $path_to_config_dir);
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
    
    public function buildUniqueName( $list_of_files ){
        $retour = implode("|", $list_of_files);
        return sha1($retour);
    }
    
    public function uniqueName(){
        if( $this->unique_name === null )
            $this->unique_name = $this->buildUniqueName($this->listOfFiles());
        return $this->unique_name;
    }
    
    public function listOfFiles(){
        if( count($this->builded_lists) == 0 )
            $this->builded_lists = $this->buildListOfFiles($this->path_to_config_dirs, $this->files_to_load);
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
}

?>
