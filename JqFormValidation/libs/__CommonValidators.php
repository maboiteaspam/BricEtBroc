<?php
namespace BricEtBroc\Form;

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