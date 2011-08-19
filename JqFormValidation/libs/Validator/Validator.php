<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\InputValueAccessor as InputValueAccessor;
use BricEtBroc\Form\Dependency as Dependency;
use BricEtBroc\Form\IJavascripter as IJavascripter;

abstract class Validator implements IJavascripter{
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
    
    public function setDependency( Dependency $dependency=NULL ) {
        $this->dependency = $dependency;
    }
    
    public function setAssertInformation( $assert_information ) {
        $this->assert_information = $assert_information;
    }
    
    public function getIdentifier(){
        return $this->identifier;
    }
    
    /**
     * 
     */
    public function is_ignored(){
        $retour = false;
        if( $this->has_dependency() ){
            /**
             * If :checked
             *  - with a checked value, is_confirmed will return true, we don't ignore
             *  - with an unchecked value, is_confirmed will return false, we ignore
             */
            $retour = $this->dependency->confirm( );
        }
        return $retour;
    }
    
    /**
     * Must return TRUE if validation is correct
     * FALSE otherwise
     *
     * @param InputValueAccessor $accessor
     * @return bool
     */
    public function validate( ){
        if( $this->is_ignored( ) ){
            return true;
        }
        return $this->validate_value( $this->accessor, $this->assert_information );
    }
    
    /**
     * 
     * @param InputValueAccessor $accessor
     * @return bool
     */
    public abstract function validate_value( InputValueAccessor $accessor, $assert_information );
    
    public function has_dependency(){
        return $this->dependency !== NULL;
    }
    
    public function __toJavascript(){
        $assert = "";
        if( $this->has_dependency() ){
            $assert = $this->dependency->__toJavascript();
        }else{
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
        }
        return $assert;
    }
}

