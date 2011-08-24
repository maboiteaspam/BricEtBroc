<?php
namespace BricEtBroc\FilesLoader;

use BricEtBroc\Cache\ICache as ICache;
use \BricEtBroc\FilesLoader\MergedIncludesLoader as MergedIncludesLoader;

/**
 * Description of MergedIncludesCacherLoader
 *
 * @author clement
 */
class MergedIncludesCacherLoader{
    public $loader;
    public $cacher;
    
    public function __construct( MergedIncludesLoader $loader, ICache $cacher ){
        $this->loader = $loader;
        $this->cacher = $cacher;
    }
    
    public function isCachedFileStillValid( $file, $file_mtime ){
        $f_exists = file_exists($file);
        if( $file_mtime === null && $f_exists ){
            return false;
        }elseif( $f_exists ){
            if( filemtime($file) > $file_mtime )
                return false;
        }else{
            if( $file_mtime !== null )
                return false;
        }
        return true;
    }
    
    public function cacheIsValid( $data ){
        if( $data["runtime_data"] != $this->loader->getRuntimeData() ){
            return false;
        }
        $files_to_check = $data["files"];
        foreach( $files_to_check as $file => $file_time ){
            if( $this->isCachedFileStillValid($file, $file_time) === false ){
                return false;
            }
        }
        return true;
    }
    
    public function loadFromCache(){
        $data = null;
        $cache_name = $this->loader->uniqueName();
        if( $this->cacher->exists($cache_name) ){
            $data = $this->cacher->read($cache_name);
            if( $this->cacheIsValid($data) === false ){
                $this->cacher->remove($cache_name);
                $data = null;
            }
        }
        return $data;
    }
    
    public function writeToCache(){
        $data = null;
        
        $cache_name     = $this->loader->uniqueName();
        $loader_data    = $this->loader->load();
        $loader_files   = array();
        foreach( $this->loader->listOfFiles() as $file ){
            $loader_files[$file] = NULL;
            if(file_exists($file))
                $loader_files[$file] = filemtime($file);
        }
        $data = array(
            "files"         => $loader_files,
            "data"          => $loader_data,
            "runtime_data"  => $this->loader->getRuntimeData(),
            "created"       => time(),
        );
        $this->cacher->write($cache_name, $data);
        return $data;
    }
    
    public function load(){
        $data = $this->loadFromCache();
        if( $data === null ){
            $data = $this->writeToCache();
        }
        return $data["data"];
    }
}