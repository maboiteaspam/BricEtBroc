<?php
namespace BricEtBroc\Locale;
use BricEtBroc\Locale\Finder as Finder;

class URLNegotiater{
    protected $current_url;
    protected $url_domains;
    protected $current_domain;
    public function __construct( $current_url, $url_domains ){
        $this->current_url      = $current_url;
        $this->url_domains      = $url_domains;
        $this->current_domain   = $url_domains[0];
    }
    
    public function find_url_domain(){
        foreach( $this->url_domains as $url_domain ){
            if( substr($this->current_url, 1, strlen($url_domain)) == $url_domain ){
                return $url_domain;
            }
        }
        return false;
    }
    
    public function getCurrentDomain(){
        return $this->current_domain;
    }
    /**
     *
     * @param string $restrict_lang 
     * @return array
     */
    public function negotiate( ){
        $url_domain = $this->find_url_domain();
        
        if( $url_domain !== false ){
            $this->current_domain = $url_domain;
            $locales = Finder::expand_locales( $url_domain );
        }else{
            $locales = Finder::expand_locales( );
            $locale = $locales[0];
            if( in_array($locales[0], $this->url_domains) === false ){
                $locales = Finder::expand_locales( $this->url_domains[0] );
            }
            $this->current_domain = $locales[0];
        }
        return $locales;
    }
}