<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\InputValueAccessor as InputValueAccessor;

class TrimFilter extends Filter{
    public function filter_value( InputValueAccessor $valueAccessor, $do_trim ){
        if( $valueAccessor->is_set() == false ) return;
        $value = $valueAccessor->read();
        $value = trim($value);
        $valueAccessor->set( $value );
    }
}

class NoHTMLFilter extends Filter{
    public function filter_value( InputValueAccessor $valueAccessor, $no_html ){
        if( $valueAccessor->is_set() == false ) return;
        $value = $valueAccessor->read();
        $value = strip_tags($value);
        $valueAccessor->set( $value );
    }
}

class NoCharsFilter extends Filter{
    public function filter_value( InputValueAccessor $valueAccessor, $no_chars ){
        if( $valueAccessor->is_set() == false ) return;
        $value = $valueAccessor->read();
        $value = str_replace($no_chars, "", $value);
        $valueAccessor->set( $value );
    }
}

class CallbackFilter extends Filter{
    public function filter_value( InputValueAccessor $valueAccessor, $callbacks ){
        $php_callback = $callbacks[0];
        call_user_func_array($php_callback, array($valueAccessor));
    }
    public function __toJavascript(){
        $js_callback = $this->assert_information[1];
        $assert = "";
        
        return $js_callback;
    }
}
