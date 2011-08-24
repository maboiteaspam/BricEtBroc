<?php
namespace BricEtBroc\Cache;
use BricEtBroc\Cache\ICache as ICache;

/**
 * Description of PhpFileCache
 *
 * @author clement
 */
class PhpFileCache implements ICache{
    public $path_to_cache;
    public function __construct( $path_to_cache ){
        $this->path_to_cache = $path_to_cache;
    }
    public function remove( $cache_file_name ){
        unlink( $this->path_to_cache.$cache_file_name.".php" );
    }
    
    public function read( $cache_file_name ){
        return include( $this->path_to_cache.$cache_file_name.".php" );
    }

    public function exists( $cache_file_name ){
        return file_exists($this->path_to_cache.$cache_file_name.".php" );
    }

    public function write( $cache_file_name, $data ){
        $wrote = file_put_contents($this->path_to_cache.$cache_file_name.".php", "<?php return ".var_export( $data, true).";");
        return $wrote>0;
    }
}