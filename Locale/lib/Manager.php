<?php
namespace BricEtBroc\Locale;

class Manager{
    protected $change_locale_callback;
    protected $change_lang_callback;
    
    protected $locale;
    protected $language;
    
    public function __construct( ){
        $this->change_locale_callback   = array();
        $this->change_lang_callback     = array();
    }
    
    public function add_locale_callback($callback){
        $this->change_locale_callback[] = $callback;
    }
    
    public function add_language_callback($callback){
        $this->change_lang_callback[] = $callback;
    }
    
    
    public function set_locale( $locale ){
        $this->locale = $locale;
        foreach( $this->change_locale_callback as $callback ){
            call_user_func_array($callback, array($locale));
        }
        if( substr($this->locale,0,2) !== $this->language ){
            $this->set_language(substr($this->locale,0,2));
        }
    }
    
    protected function set_language( $lang ){
        $this->language = $lang;
        foreach( $this->change_lang_callback as $callback ){
            call_user_func_array($callback, array($lang));
        }
    }
}