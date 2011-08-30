<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\IHtmlWriter as IHtmlWriter;
use BricEtBroc\Form\IFormComponent as IFormComponent;

/**
 * Description of FormSearSurf
 *
 * @author clement
 */
class FormSeaSurf implements IFormComponent {
    public $targetElement;
    public $options;
    
    public $input_values;
    public $salt;
    
    public $has_parsed;
    
    
    /**
     *
     * @param string $targetElement
     * @param array $options 
     */
    public function __construct( $targetElement, $options ){
        $this->targetElement    = $targetElement;
        $this->input_values     = new InputValues( array() );
        $this->options          = $options;
        $this->has_parsed       = false;
    }
    
    /**
     *
     * @param Form $Form 
     */
    public function attachTo( Form $Form ){
        $Form->listenTo("before_filter", $this, "check_seasurf");
        $this->setInputValues($Form->input_values);
    }
    
    /**
     *
     * @param InputValues $input_values
     */
    public function setInputValues( InputValues $input_values ){
        $this->input_values = $input_values;
    }
    
    
    /**
     *
     * @return bool
     */
    public function parseOptions(){
        $this->has_parsed = true;
        
        $salt = null;
        $token_max_lifetime = null;
        if( isset($this->options["seasurf"]) ){
            if( isset($this->options["seasurf"]["salt"]) ){
                $salt = $this->options["seasurf"]["salt"];
            }
            if( isset($this->options["seasurf"]["token_max_lifetime"]) ){
                $token_max_lifetime = $this->options["seasurf"]["token_max_lifetime"];
            }
        }
        
        
        if( $salt === null ){
            $salt = isset($_SERVER["SERVER_NAME"])?$_SERVER["SERVER_NAME"]:"";
        }
        if( $token_max_lifetime === null ){
            $token_max_lifetime = ini_get("session.gc_maxlifetime");
        }
        
        $this->salt = $salt;
        $this->token_max_lifetime = $token_max_lifetime;
        
        return true;
    }
    
    /**
     *
     * @return type 
     */
    public function check_seasurf( ){
        if( ! $this->has_parsed )
            $this->parseOptions ();
        
        if( $_SERVER["REQUEST_METHOD"] !== "POST" ){
            return false;
        }
        if( isset($_SESSION["_ck_csrf_tokens"]) === false ){
            return false;
        }
        if( isset($_SESSION["_ck_csrf_tokens"][$this->targetElement]) === false ){
            return false;
        }
        if( $this->input_values->getAccessor("seasurf_token")->is_set() === false ){
            return false;
        }
        $time           = time();
        $birthday_token = $_SESSION["_ck_csrf_tokens"][$this->targetElement]['token_time'];
        
        if( ($time - $birthday_token) > $this->token_max_lifetime ){
            return false;
        }
        
        $in_token       = $this->input_values->getAccessor("seasurf_token")->read();
        $correct_token  = $_SESSION["_ck_csrf_tokens"][$this->targetElement]['token'];
        
        if( $in_token !== $correct_token ){
            return false;
        }
        
        return true;
    }
    
    /**
     * Return a new token value
     * for the given form
     * and saves it in session
     *
     * @param CKForm $form
     * @return string
     */
    public function generateCSRFToken( ){
        if( ! $this->has_parsed )
            $this->parseOptions ();
        if( isset($_SESSION["_ck_csrf_tokens"]) === false ){
            $_SESSION["_ck_csrf_tokens"] = array();
        }
        
        $token_value = md5(uniqid(rand(), TRUE).$this->targetElement.$this->salt);
        $_SESSION["_ck_csrf_tokens"][$this->targetElement] = array(
            'token'=>$token_value,
            'token_time'=>time(),
        );
        return $token_value;
    }
        
    public function render( $has_validated, \DOMDocument $doc ){
        $xpath      = new \DOMXpath($doc);
        $elements   = $xpath->query("//form[@name='".$this->targetElement."']");
        
        $new_token = $this->generateCSRFToken();
        
        if ( $elements->length > 0) {
            $csrf_el   = $xpath->query("//imput[@name='seasurf_token']", $elements->item(0));
            if( $csrf_el->length === 0 ){
                $node = $doc->createElement("input");
                $node->setAttribute("name", "seasurf_token");
                $node->setAttribute("type", "hidden");
                $node->setAttribute("value", $new_token);
                $newnode = $elements->item(0)->appendChild($node);
            }else{
                /*
                $attr = $elements[0]->attributes->getNamedItem("value");
                if( $attr === null ){
                    
                }*/
                $attr = $csrf_el->item(0)->setAttribute("value", $new_token);
            }
        }
        return $doc;
    }
}

