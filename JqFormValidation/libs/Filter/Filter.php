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
        return $this->filter_value( $this->accessor, $this->assert_information );
    }
    
    /**
     * 
     * @param InputValueAccessor $accessor
     * @return bool
     */
    public abstract function filter_value( InputValueAccessor $accessor, $assert );
    
    public function __toJavascript(){
        $assert = "";

        if(is_object($this->assert_information) )
            $assert = "'".strval($this->assert_information)."'";
        elseif(is_bool($this->assert_information) )
            $assert = $this->assert_information===true?"true":"false";
        elseif(is_int($this->assert_information) )
            $assert = $this->assert_information;
        elseif(is_array($this->assert_information) )
            $assert = "'".var_export($this->assert_information, true)."'";
        else
            $assert = "'".$this->assert_information."'";
        
        return $assert;
    }
}

