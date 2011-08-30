<?php
namespace BricEtBroc\Translator;

class SimplePhp implements ITranslator{
    protected $messages;
    protected $locale;
    
    public function setMessages( $messages ){
        $this->messages = $messages;
    }
    
    public function setLocale( $locale ){
        $this->locale = $locale;
    }
    
    /**
     *
     * @param string $message_id
     * @param array $values 
     */
    public function translate( $message_id, $values=array() ){
        if( isset($this->messages[$message_id])===false)
            return $message_id;
        return vsprintf ( $this->messages[$message_id] , $values );
    }
}