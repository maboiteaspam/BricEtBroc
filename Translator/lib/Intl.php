<?php
namespace BricEtBroc\Translator;

class Intl implements ITranslator{
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
        if( isset($message_id) ===false ){
            return "";
        }
        
        if( is_string($this->messages[$message_id]) ){
            $this->messages[$message_id] = msgfmt_create( $this->locale, $this->messages[$message_id]);
        }
        
        return msgfmt_format($this->messages[$message_id], $values);
    }
}