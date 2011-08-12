<?php
namespace BricEtBroc;

class Config extends \ArrayObject{
    /**
     *
     * @var CKConfig 
     */
    protected static $instance;
    /**
     *
     * @return CKConfig 
     */
    public static function getInstance(){
        return self::$instance;
    }
    /**
     *
     * @param array $values
     * @return CKConfig 
     */
    public static function setInstance( array $values ){
        self::$instance = new Config( $values );
        return self::$instance;
    }
}
