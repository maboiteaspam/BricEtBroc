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
        return count($valueAccessor->read())>=intval( $this->assert_information );
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
class RemoteValidator extends CallbackValidator{
    public function __toJavascript(){
        $retour = "";
        $retour .= "{";
        $retour .= "'url':'".$_SERVER["REQUEST_URI"]."?vid=".$this->identifier."', ";
        $retour .= "'type':'POST'";
        $retour .= "}";
        return $retour;
    }
}

class FileUploadValidator extends Validator{
    
    public function validate_value( InputValueAccessor $valueAccessor ){
        $valueAccessor->setInputValuesSource("files");
        $files  = $valueAccessor->read();
        $retour = $files["error_code"]===UPLOAD_ERR_OK;
        return $retour;
    }
}

class ImageUploadValidator extends Validator{
    
    public function validate_value( InputValueAccessor $valueAccessor ){
        if (! extension_loaded('gd')) { return true; }
        
        $valueAccessor->setInputValuesSource("files");
        $valid_types     = is_array($this->assert_information)?$this->assert_information:array($this->assert_information);

        foreach( $valid_types as $valid_type ){
            if(imagetypes() & $valid_type){
                if( $valid_type === IMG_GIF){
                    try{
                        $retour = imagecreatefromgif($im);
                        if( $retour === true ) break;
                    }catch(Exception $Ex ){}
                }elseif( $valid_type === IMG_JPG){
                    try{
                        $retour = imagecreatefromjpeg($im);
                        if( $retour === true ) break;
                    }catch(Exception $Ex ){}
                }elseif( $valid_type === IMG_PNG){
                    try{
                        $retour = imagecreatefrompng($im);
                        if( $retour === true ) break;
                    }catch(Exception $Ex ){}
                }elseif( $valid_type === IMG_WBMP){
                    try{
                        $retour = imagecreatefromwbmp($im);
                        if( $retour === true ) break;
                    }catch(Exception $Ex ){}
                }elseif( $valid_type === IMG_XPM){
                    try{
                        $retour = imagecreatefromxpm($im);
                        if( $retour === true ) break;
                    }catch(Exception $Ex ){}
                }
            }
        }
        
        return $retour;
    }
}

class ExtUploadValidator extends Validator{
    
    public function validate_value( InputValueAccessor $valueAccessor ){
        $valueAccessor->setInputValuesSource("files");
        $files          = $valueAccessor->read();
        $valid_exts     = is_array($this->assert_information)?$this->assert_information:array($this->assert_information);
        $retour         = false;
        foreach( $valid_exts as $valid_ext ){
            if( substr($files["name"], -strlen($valid_ext)) == $valid_ext ){
                $retour = true;
                break;
            }
        }
        return $retour;
    }
}