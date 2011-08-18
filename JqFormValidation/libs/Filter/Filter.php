<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\InputValueAccessor as InputValueAccessor;

abstract class Filter{
    /**
     *
     * @var mixed
     */
    protected $assert_information;
    
    /**
     *
     * @var string
     */
    protected $identifier;
    
    /**
     *
     * @var int
     */
    protected static $identifier_incr = 0;
    
    /**
     *
     * @var Dependency 
     */
    protected $dependency;
    
    public function __construct(){
        $this->identifier = self::$identifier_incr;
        self::$identifier_incr++;
        $this->identifier = sha1(get_class($this)."_".$this->identifier);
    }
    
    /**
     *
     * @var InputValueAccessor 
     */
    protected $accessor;
    
    public function setAccessor( InputValueAccessor $accessor ) {
        $this->accessor = $accessor;
    }
    
    public function setAssertInformation( $assert_information ) {
        $this->assert_information = $assert_information;
    }
    
    public function getIdentifier(){
        return $this->identifier;
    }
    
    /**
     *
     * @param InputValueAccessor $accessor
     */
    public function filter( ){
        return $this->filter_value( $this->accessor );
    }
    
    /**
     * 
     * @param InputValueAccessor $accessor
     * @return bool
     */
    public abstract function filter_value( InputValueAccessor $accessor );
    
    public function __toJavascript(){
        $assert = "";
        
        return $assert;
    }
}

