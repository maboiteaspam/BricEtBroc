<?php
namespace BricEtBroc;

class Cache{
    protected static $shaed_file_name = array();
    
    /**
     *
     * @param string $file_path
     * @return string 
     */
    protected static function sha1_file_name( $file_path ){
        if( isset(self::$shaed_file_name[$file_path]) === false )
            self::$shaed_file_name[$file_path] = sha1($file_path);
        return self::$shaed_file_name[$file_path];
    }
    
    /**
     *
     * @param string $cache_path
     * @param string $config_file_path
     * @return bool 
     */
    public static function isFresh( $cache_path, $config_file_path ){
        if( $cache_path === null ){
            return false;
        }
        
        $cache_file_path    = $cache_path."/".self::sha1_file_name($config_file_path).".php";
        
        if(file_exists($cache_file_path) === false ) return false;
        
        $cached_config      = include($cache_file_path);
        
        foreach( $cached_config["files"] as $index => $file ){
            $f_exists = file_exists($file);
            if( $cached_config["times"][$index] === null && $f_exists ){
                return false;
            }
            if( $cached_config["times"][$index] !== null && !$f_exists ){
                return false;
            }
            if( $cached_config["times"][$index] === null && !$f_exists ){
                //- nothing todo
            }elseif( filemtime($file) > $cached_config["times"][$index] ){
                return false;
            }
        }
        
        return true;
    }
    
    /**
     *
     * @param string $cache_path
     * @param string $config_file_path
     * @return array 
     */
    public static function load( $cache_path, $config_file_path ){
        $cache_file_path = $cache_path."/".self::sha1_file_name($config_file_path).".php";
        $cached_config = include($cache_file_path);
        return $cached_config["data"];
    }
    
    /**
     *
     * @param string $cache_path
     * @param FileLoader $config_loader
     * @return bool 
     */
    public static function save( $cache_path, FileLoader $config_loader ){
        
        $config_meta = array(
            "files"=>$config_loader->getMergedFiles(),
            "data"=>$config_loader->getData()
        );
        $config_file_path = $config_loader->getConfigFilePath();
        
        $config_meta["times"] = array();
        foreach( $config_meta["files"] as $index=>$file ){
            if(file_exists($file) ){
                $config_meta["times"][ $index ] = filemtime( $file );
            }else{
                $config_meta["times"][ $index ] = null;
            }
        }
        
        $cache_file_path = $cache_path."/".self::sha1_file_name($config_file_path).".php";
        
        $config_meta["created"] = time();
        file_put_contents($cache_file_path, "<?php return ".var_export( $config_meta, true).";");
        
        return true;
    }
}
