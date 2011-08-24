<?php
namespace BricEtBroc\Cache;

/**
 * 
 */
interface ICache{
    public function remove( $cache_name );
    
    public function read( $cache_name );

    public function exists( $cache_name );

    public function write( $cache_name, $data );
}