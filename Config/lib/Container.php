<?php
namespace BricEtBroc;

class Container extends \ArrayObject{
    /**
     *
     * @var BricEtBroc\Container
     */
    protected static $instance;
    /**
     *
     * @return BricEtBroc\Container
     */
    public static function getInstance(){
        return self::$instance;
    }
    /**
     *
     * @param array $values
     * @return BricEtBroc\Container
     */
    public static function setInstance( array $values ){
        self::$instance = new Container( $values );
        return self::$instance;
    }
}
