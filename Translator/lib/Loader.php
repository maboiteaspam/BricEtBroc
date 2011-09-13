<?php
namespace BricEtBroc\Translator;

use \BricEtBroc\Translator\Container as Container;
use \BricEtBroc\FilesLoader\MergedIncludesCacherLoader as MergedIncludesCacherLoader;
use \BricEtBroc\FilesLoader\MergedIncludesLoader as MergedIncludesLoader;
use BricEtBroc\Cache\ICache as ICache;

/**
 * Description of Loader
 *
 * @author clement
 */
class Loader{
    
    protected $config_loader;
    protected $cacher;
    protected $containers;
    protected $locale;
    
    public function __construct( $path_to_load_dirs=array(), $files_to_load=array() ){
        $this->config_loader    = new MergedIncludesLoader(array(), $path_to_load_dirs, $files_to_load );
        $this->locale           = null;
        $this->containers       = array();
    }
    public function addFileToLoad( $file_to_load, $at_the_end=true ){
        if( $this->config_loader->addFileToLoad( $file_to_load, $at_the_end ) ){
            if( isset($this->containers[$this->locale]) )
                unset($this->containers[$this->locale]);
            return true;
        }
        return false;
    }
    
    public function addPathToLoadDir( $path_to_config_dir, $at_the_end=true ){
        if( $this->config_loader->addPathToLoadDir( $path_to_config_dir, $at_the_end ) ){
            if( isset($this->containers[$this->locale]) )
                unset($this->containers[$this->locale]);
            return true;
        }
        return false;
    }
    
    public function setCacher( ICache $cacher ){
        $this->cacher = $cacher;
    }
    
    public function setLocale( $locale ){
        if( trim($locale) !== ""
                && $locale !== $this->locale ){
            $this->buildFilesListForLocale( $locale );
            $this->locale = $locale;
        }
    }
    
    public function buildFilesListForLocale( $locale ){
        $retour = array();
        $this->addFileToLoad(strtolower(substr($locale,0,2)).".php");
        if( substr($locale,0,2) != substr($locale,3) ){
            $this->addFileToLoad(substr($locale,0,2)."_".  strtoupper(substr($locale,0,2)).".php");
        }
        $this->addFileToLoad( $locale.".php");
    }
    
    public function get(){
        if( $this->locale === null )
            return new Container();
        if( isset($this->containers[$this->locale]) === false ){
            $data = array();
            if( $this->cacher === null ){
                $data = $this->config_loader->load();
            }else{
                $loader_cacher  = new MergedIncludesCacherLoader($this->config_loader, $this->cacher);
                $data           = $loader_cacher->load();
            }
            $this->containers[$this->locale] = new Container($data);
        }
        return $this->containers[$this->locale];
    }
}
