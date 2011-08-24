<?php
namespace BricEtBroc\FilesLoader;


/**
 * Description of MergedIncludes
 *
 * @author clement
 */
class MergedIncludesLoader {
    
    protected $path_to_load_dirs;
    protected $files_to_load;
    protected $runtime_data;
    
    protected $builded_lists;
    protected $unique_name;
    
    public function __construct( $runtime_data=array(), $path_to_load_dirs=array(), $files_to_load=array() ){
        $this->path_to_load_dirs    = $path_to_load_dirs;
        $this->files_to_load        = $files_to_load;
        $this->runtime_data         = $runtime_data;
        $this->reset();
    }
    
    public function getRuntimeData(){
        return $this->runtime_data;
    }
    
    public function reset(){
        $this->builded_lists    = array();
        $this->unique_name      = null;
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
    
    public function buildListOfFiles($path_to_load_dirs, $files_to_load){
        $retour = array();
        foreach( $path_to_load_dirs as $path_to_load_dir ){
            foreach ($files_to_load as $file_to_load ){
                $retour[] = $path_to_load_dir.$file_to_load;
            }
        }
        return $retour;
    }
    
    public function listOfFiles( ){
        if( count($this->builded_lists) == 0 )
            $this->builded_lists = $this->buildListOfFiles($this->path_to_load_dirs, $this->files_to_load);
        return $this->builded_lists;
    }
    
    public function load(){
        $files  = $this->listOfFiles();
        $data   = array();
        foreach( $files as $file ){
            if(file_exists($file) ){
                $current_data = include($file);
                $data = array_merge_recursive($data, $current_data);
            }
        }
        $data = array_merge_recursive($data, $this->runtime_data);
        return $data;
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
}





