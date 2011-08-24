<?php
namespace BricEtBroc\Cache;
use BricEtBroc\Cache\ICache as ICache;

/**
 * Description of MergedIncludes
 *
 * @author clement
 */


class ApcCache implements ICache{
    public function remove( $cache_name ){
        apc_delete($cache_name);
    }
    
    public function read( $cache_name ){
        return apc_fetch($cache_name);
    }

    public function exists( $cache_name ){
        apc_fetch($cache_name, $success);
        return $success;
    }

    public function write( $cache_name, $data ){
        return apc_store($cache_name, $data, 0);
    }
}