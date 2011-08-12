<?php

class Validator{
    /**
     *
     * @var mixed
     */
    protected $assert_information;
    /**
     *
     * @var Dependency 
     */
    protected $dependency;
    
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
            $retour = $this->dependency->is_confirmed( );
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
        return $this->validate_value( $this->accessor );
    }
    
    public function has_dependency(){
        return $this->dependency !== NULL;
    }
    
    public function __toJavascript(){
        $assert = "";
        if( $this->has_dependency() ){
            $assert = $this->dependency->__toJavascript();
        }else{
            if(is_object($var) === false )
                $assert = strval($this->assert_information);
            elseif(is_string($var) === false )
                $assert = var_export($this->assert_information, true);
            $assert = "'".$assert."'";
        }
        return $assert;
    }
}

class RequiredValidator extends Validator{
    public function validate_value( InputValueAccessor $valueAccessor ){
        if( $valueAccessor->is_set() === false ) return false;
        $value = $valueAccessor->read();
        if(is_string($value) && $value === "" ) return false;
        if(is_array($value) && count($value) === 0) return false;
        return true;
    }
}

class EmailValidator extends Validator{
    public function validate_value( InputValueAccessor $valueAccessor ){
        return $valueAccessor->read()!="";
    }
}

class MinLengthValidator extends Validator{
    public function validate_value( InputValueAccessor $valueAccessor ){
        return strlen($valueAccessor->read())>=intval( $this->assert_information );
    }
}

class MaxLengthValidator extends Validator{
    public function validate_value( InputValueAccessor $valueAccessor ){
        return strlen($valueAccessor->read())<=intval( $this->assert_information );
    }
}

class MinCountValidator extends Validator{
    public function validate_value( InputValueAccessor $valueAccessor ){
        return count($valueAccessor->read())<=intval( $this->assert_information );
    }
}

class MaxCountValidator extends Validator{
    public function validate_value( InputValueAccessor $valueAccessor ){
        return count($valueAccessor->read())<=intval( $this->assert_information );
    }
}

class RegexValidator extends Validator{
    public function validate_value( InputValueAccessor $valueAccessor ){
        return preg_match($this->assert_information, $valueAccessor->read()) === 1;
    }
}

class CallbackValidator extends Validator{
    
    public function validate_value( InputValueAccessor $valueAccessor ){
        return call_user_func_array($this->assert_information, array($valueAccessor));
    }
}
class AjaxValidator extends CallbackValidator{
    
    public function getIdentifier(){
        
    }
    
    public function __toJavascript(){
        return "function(){alert('i should do it on ajax !!')";
    }
}

class FileUploadValidator{
    
}

abstract class Dependency{
    /**
     *
     * @var InputValueAccessor 
     */
    protected $accessor;
    
    public function __construct( ) {
    }
    
    public function setAccessor( InputValueAccessor $accessor ) {
        $this->accessor = $accessor;
    }
    public abstract function is_confirmed();
    public function __toJavascript(){
        return "'".$this->accessor->data_source_target."'";
    }
}
class CheckedDependency extends Dependency{
    public function is_confirmed( ){
        return $this->accessor->is_set();
    }
}
class SelectedDependency extends Dependency{
    public function is_confirmed( ){
        return $this->accessor->is_set();
    }
}
class UncheckedDependency extends Dependency{
    public function is_confirmed( ){
        $retour = !$this->accessor->is_set();
        return $retour;
    }
}
class NotBlankDependency extends Dependency{
    public function is_confirmed( ){
        if( ! $this->accessor->is_set() ) return false;
        if( $this->accessor->read() === "" ) return false;
        return true;
    }
}
class BlankDependency extends Dependency{
    public function is_confirmed( ){
        if( ! $this->accessor->is_set() ) return true;
        if( $this->accessor->read() === "" ) return true;
        return false;
    }
}