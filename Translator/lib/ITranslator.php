<?php
namespace BricEtBroc\Translator;

/**
 *
 * @author clement
 */
interface ITranslator {
    public function setMessages( $messages );
    public function setLocale( $locale );
    public function translate( $message_id, $values=array() );
}
