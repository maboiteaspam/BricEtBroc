<?php
namespace BricEtBroc\FilesLoader;

/**
 * Description of FilesCachedLoader
 *
 * @author clement
 */

class CachedLoader{
    protected $path_to_cache;
    protected $loader;
    protected $response;
    protected $loaded_cache;
    protected $unique_name;
    
    public function __construct( $path_to_cache, ILoader $loader ){
        $this->path_to_cache    = $path_to_cache;
        $this->loader           = $loader;
        $this->invalidate();
    }
    
    public function addFileToLoad( $file_to_load, $at_the_end=true ){
        if( $this->loader->addFileToLoad( $file_to_load, $at_the_end ) ){
            $this->invalidate();
        }
    }
    
    public function addPathToLoadDir( $path_to_config_dir, $at_the_end=true ){
        if( $this->loader->addPathToLoadDir( $path_to_config_dir, $at_the_end ) ){
            $this->invalidate();
        }
    }
    
    public function getLoader(){
        return $this->loader;
    }
    
    public function invalidate(){
        $this->response     = null;
        $this->loaded_cache = null;
        $this->unique_name  = null;
    }
    
    public function load(){
        if( $this->response === null ){
            $cache_file_name = $this->getCacheFilename();
            
            if( $this->cacheExists( $cache_file_name ) === false ){
                $data = $this->createCache( $cache_file_name );
            }elseif( $this->isValidCache( $cache_file_name ) === false ){
                $this->removeCache( $cache_file_name );
                $this->invalidate();
                $data = $this->createCache( $cache_file_name );
            }else{
                $data = $this->readCache( $cache_file_name );
            }
            
            $this->response = $this->loader->createResponse($data);
        }
        return $this->response;
    }
    
    public function buildUniqueName( $list_of_files ){
        $retour = implode("|", $list_of_files);
        return sha1($retour);
    }
    
    public function uniqueName(){
        if( $this->unique_name === null )
            $this->unique_name = $this->buildUniqueName($this->loader->listOfFiles());
        return $this->unique_name;
    }
    
    public function getCacheFilename(){
        return $this->uniqueName().".php";
    }
    
    public function isValidCache( $cache_file_name ){
        $this->loadCache($cache_file_name);
        $files = $this->loaded_cache["files"];
        foreach( $files as $file => $file_time ){
            $f_exists = file_exists($file);
            if( $file_time === null && $f_exists ){
                return false;
            }elseif( $f_exists ){
                if( filemtime($file) > $file_time )
                    return false;
            }else{
                if( $file_time !== null )
                    return false;
            }
        }
        return true;
    }
    
    public function createCache( $cache_file_name ){
        
        $cache  = array();
        $files  = array();
        $data   = array();
        
        $data               = $this->loader->load();
        $listOfFiles        = $this->loader->completeListOfFiles();
        foreach( $listOfFiles as $file ){
            $files[$file] = null;
            if(file_exists($file) )
                $files[$file] = filemtime($file);
        }
        
        $cache["files"]     = $files;
        $cache["data"]      = $data;
        $cache["created"]   = time();
        $p = file_put_contents($this->path_to_cache.$cache_file_name, "<?php return ".var_export( $cache, true).";");
        
        return $data;
    }
    
    public function removeCache( $cache_file_name ){
        unlink( $this->path_to_cache.$cache_file_name );
    }
    
    public function readCache( $cache_file_name ){
        $cached_data = $this->loadCache($cache_file_name);
        return $cached_data["data"];
    }
    
    public function loadCache( $cache_file_name ){
        if( $this->loaded_cache === null )
            $this->loaded_cache = include( $this->path_to_cache.$cache_file_name );
        return $this->loaded_cache;
    }
    
    public function cacheExists( $cache_file_name ){
        return file_exists($this->path_to_cache.$cache_file_name);
    }
}
