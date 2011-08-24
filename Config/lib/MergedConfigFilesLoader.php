<?php
namespace BricEtBroc\Config;

use BricEtBroc\Config\Container as Container;
use BricEtBroc\Config\FileLoader as FileLoader;
use \BricEtBroc\FilesLoader\MergedIncludesLoader as MergedIncludesLoader;

/**
 * Description of MergedConfigFilesLoader
 *
 * @author clement
 */
class MergedConfigFilesLoader extends MergedIncludesLoader{
    
    protected $dependant_files;
    
    public function __construct( $runtime_config=array(), $path_to_load_dirs=array(), $files_to_load=array() ){
        $this->dependant_files  = array();
        parent::__construct($runtime_config, $path_to_load_dirs, $files_to_load);
    }
    
    public function listOfDependantFiles(){
        $retour = array();
        foreach( $this->dependant_files as $files ){
            $retour = array_merge($retour, $files);
        }
        return $retour;
    }
    
    public function load(){
        $files  = $this->listOfFiles();
        $data   = array();
        foreach( $files as $file ){
            $loader         = FileLoader::loadFile($file);
            $merged_files   = $loader->getMergedFiles();
            $first_file     = array_shift($merged_files);
            if( $first_file !== null ){
                $this->dependant_files[$file] = $merged_files;
            }
            $data   = array_merge_recursive($data, $loader->getData());
        }
        $data = array_merge_recursive($data, $this->runtime_data);
        $data = $this->replace_lsb_values( $data );
        return $data;
    }
    
    public function replace_lsb_values( $data ){
        $container = new Container( $data );
        array_walk_recursive($data, function (&$item, $key) use($container){
            if( is_string($item) ){
                if( substr($item,0,3) === "::%" ){
                    $item = $container->getByPath(substr($item,3));
                }
            }
        });
        return $data;
    }
}