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

class EmailValidator extends Validator{
    public function validate_value( InputValueAccessor $valueAccessor ){
        $pattern = "/^[a-z0-9!#$%&*+=?^_`{|}~-]+(\.[a-z0-9!#$%&*+-=?^_`{|}~]+)*@([-a-z0-9]+\.)+([a-z]{2,3}|info|arpa|aero|coop|name|museum)$/i";
        return preg_match($pattern, $valueAccessor->read()) === 1;
    }
}

class UrlValidator extends Validator{
    public function validate_value( InputValueAccessor $valueAccessor ){
        $pattern = '{
  \\b
  # Match the leading part (proto://hostname, or just hostname)
  (
    # http://, or https:// leading part
    (https?)://[-\\w]+(\\.\\w[-\\w]*)+
  |
    # or, try to find a hostname with more specific sub-expression
    (?i: [a-z0-9] (?:[-a-z0-9]*[a-z0-9])? \\. )+ # sub domains
    # Now ending .com, etc. For these, require lowercase
    (?-i: com\\b
        | edu\\b
        | biz\\b
        | gov\\b
        | in(?:t|fo)\\b # .int or .info
        | mil\\b
        | net\\b
        | org\\b
        | [a-z][a-z]\\.[a-z][a-z]\\b # two-letter country code
    )
  )
  # Allow an optional port number
  ( : \\d+ )?
  # The rest of the URL is optional, and begins with /
  (
    /
    # The rest are heuristics for what seems to work well
    [^.!,?;"\'<>()\[\]\{\}\s\x7F-\\xFF]*
    (
      [.!,?]+ [^.!,?;"\'<>()\\[\\]\{\\}\s\\x7F-\\xFF]+
    )*
  )?
}ix';
        return preg_match($pattern, $valueAccessor->read()) === 1;
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