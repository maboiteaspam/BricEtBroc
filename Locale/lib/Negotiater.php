<?php
namespace BricEtBroc\Locale;
use BricEtBroc\Locale\Finder as Finder;

class Negotiater{
    protected $allowed_langs;
    protected $negotiated_locale;
    protected $best_locale;
    public function __construct( $allowed_langs ){
        $this->allowed_langs  = $allowed_langs;
    }
    
    public function set_best_locale( $best_locale ){
        $this->best_locale = $best_locale;
    }
    
    public function getNegotiatedLocale(){
        if( $this->negotiated_locale === null )
            $this->negotiate ();
        return $this->negotiated_locale;
    }
    
    public function getNegotiatedLang(){
        if( $this->negotiated_locale === null )
            $this->negotiate ();
        return substr($this->negotiated_locale,0,2);
    }
    
    
    protected function get_a_locale_for_a_lang( $lang ){
        foreach( $this->allowed_langs as $allowed_lang => $allowed_locale ){
            if( $allowed_lang == $lang ){
                return $allowed_locale;
            }
        }
        return null;
    }
    protected function get_first_locale( ){
        if( count($this->allowed_langs) > 0 ){
            $keys = array_keys($this->allowed_langs);
            return $this->allowed_langs[ $keys[0] ];
        }
        return null;
    }

    /**
     *
     * @param string $restrict_lang 
     * @return array
     */
    public function negotiate( $restrict_lang=null ){
        $best_locale = $this->best_locale;
        
        if( $restrict_lang !== NULL ){
            if( substr($best_locale,0,2) != $restrict_lang ){
                $best_locale = $this->get_a_locale_for_a_lang($restrict_lang);
                if( $best_locale === null ){
                    $best_locale = $this->get_first_locale();
                }
                
                if( $best_locale === null )
                    $best_locale = $this->best_locale;
            }
        }else{
            if( in_array($best_locale, $this->allowed_langs) ){
                return $best_locale;
            }else{
                $best_locale    = $this->get_a_locale_for_a_lang( substr($best_locale,0,2) );
                if( $best_locale === null ){
                    $best_locale = $this->get_first_locale();
                }
                
                if( $best_locale === null )
                    $best_locale = $this->best_locale;
            } 
        }
        
        $this->negotiated_locale = $best_locale;
        
        
        return $best_locale;
    }
}