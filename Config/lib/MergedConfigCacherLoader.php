<?php
namespace BricEtBroc\Config;

use \BricEtBroc\FilesLoader\MergedIncludesCacherLoader as MergedIncludesCacherLoader;
use \BricEtBroc\Config\MergedConfigFilesLoader as MergedConfigFilesLoader;
use BricEtBroc\Cache\ICache as ICache;

/**
 * Description of MergedConfigCacherLoader
 *
 * @author clement
 */
class MergedConfigCacherLoader extends MergedIncludesCacherLoader{
    public function __construct( MergedConfigFilesLoader $loader, ICache $cacher ){
        $this->loader = $loader;
        $this->cacher = $cacher;
    }

    public function cacheIsValid( $data ){
        if( parent::cacheIsValid( $data ) ){
            $files_to_check = $data["dpdt_files"];
            foreach( $files_to_check as $file => $file_time ){
                if( $this->isCachedFileStillValid($file, $file_time) === false ){
                    return false;
                }
            }
        }
        return true;
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
        $loader_dpdt_files   = array();
        foreach( $this->loader->listOfDependantFiles() as $file ){
            $loader_dpdt_files[$file] = NULL;
            if(file_exists($file))
                $loader_dpdt_files[$file] = filemtime($file);
        }
        $data = array(
            "files"         => $loader_files,
            "dpdt_files"    => $loader_dpdt_files,
            "data"          => $loader_data,
            "runtime_data"  => $this->loader->getRuntimeData(),
            "created"       => time(),
        );
        $this->cacher->write($cache_name, $data);
        return $data;
    }
}