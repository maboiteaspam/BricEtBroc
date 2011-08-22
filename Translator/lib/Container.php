<?php
namespace BricEtBroc\Translator;

class Container extends \ArrayObject{
    /**
     *
     * @var BricEtBroc\Translator\Container
     */
    protected static $instance;
    /**
     *
     * @return BricEtBroc\Translator\Container
     */
    public static function getInstance(){
        return self::$instance;
    }
    /**
     *
     * @param array $values
     * @return BricEtBroc\Translator\Container
     */
    public static function setInstance( $locale, array $values ){
        self::$instance = NULL;
        self::$instance = new Container( $values );
        self::$instance->locale = $locale;
        return self::$instance;
    }
    
    protected $locale;
    
    public function setLocale( $locale ){
        $this->locale = $locale;
    }
    
    public function getLocale(){
        return $this->locale;
    }
    
    /**
     *
     * @param string $message_id
     * @param array $values 
     */
    public function translate( $message_id, $values=array() ){
        if( isset($message_id) ===false ){
            return "";
        }
        
        if( is_string($this[$message_id]) ){
            $this[$message_id] = msgfmt_create( $this->locale, $this[$message_id]);
        }
        
        return msgfmt_format($this[$message_id], $values);
    }
}
