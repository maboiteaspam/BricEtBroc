<?php
namespace BricEtBroc\Config;

use \BricEtBroc\Config\FileLoader as FileLoader;
use \BricEtBroc\Config\MergedConfigFilesLoader as MergedConfigFilesLoader;
use \BricEtBroc\Config\MergedConfigCacherLoader as MergedConfigCacherLoader;
use \BricEtBroc\Config\Container as Container;
use BricEtBroc\Cache\ICache as ICache;

/**
 * Description of Loader
 *
 * @author clement
 */
class Loader{
    
    protected $config_loader;
    protected $cacher;
    protected $container;
    
    public function __construct( $runtime_config, $path_to_load_dirs=array(), $files_to_load=array() ){
        $this->config_loader    = new MergedConfigFilesLoader($runtime_config, $path_to_load_dirs, $files_to_load );
    }
    public function addFileToLoad( $file_to_load, $at_the_end=true ){
        if( $this->config_loader->addFileToLoad( $file_to_load, $at_the_end ) ){
            $this->container = null;
            return true;
        }
        return false;
    }
    
    public function addPathToLoadDir( $path_to_config_dir, $at_the_end=true ){
        if( $this->config_loader->addPathToLoadDir( $path_to_config_dir, $at_the_end ) ){
            $this->container = null;
            return true;
        }
        return false;
    }
    
    
    public function setCacher( ICache $cacher ){
        $this->cacher = $cacher;
    }
    
    public function get(){
        if( $this->container === null ){
            $data = array();
            if( $this->cacher === null ){
                $data = $this->config_loader->load();
            }else{
                $loader_cacher  = new MergedConfigCacherLoader($this->config_loader, $this->cacher);
                $data           = $loader_cacher->load();
            }
            $this->container = new Container($data);
        }
        return $this->container;
    }
}
