<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\IFormComponent as IFormComponent;

/**
 * Description of FormSearSurf
 *
 * @author clement
 */
class FormPostSubmit implements IFormComponent {
    public $targetElement;
    public $options;
    
    public $has_parsed;
    
    
    /**
     *
     * @param string $targetElement
     * @param array $options 
     */
    public function __construct( $targetElement, $options ){
        $this->targetElement    = $targetElement;
        $this->input_values     = new InputValues( );
        $this->options          = $options;
        $this->has_parsed       = false;
    }
    
    /**
     *
     * @param Form $Form 
     */
    public function attachTo( Form $Form ){
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
        
        return true;
    }
    
    public function isSubmitted(){
        if( $this->input_values->getAccessor("form_id")->is_set() ){
            if( $this->input_values->getAccessor("form_id")->read() === $this->getIdentifierValue() ){
                return true;
            }
        }
        return false;
    }
    
    protected function getIdentifierValue(){
        return sha1($this->targetElement);
    }
    
    public function render( $is_submitted, $has_validated, \DOMDocument $doc ){
        $xpath      = new \DOMXpath($doc);
        
        if( isset($this->options["rules"]) ){
            $elements   = $xpath->query("//form[@name='".$this->targetElement."']");
            if( $elements !== false && $elements->length > 0 ){
                if( $is_submitted ){
                    foreach( $this->options["rules"] as $elementTarget=>$infos ){
                        $input_el   = $xpath->query("//*[@name='".$elementTarget."']", $elements->item(0) );
                        if( $input_el!==false && $input_el->length > 0 ){
                            if( $this->input_values->getAccessor($elementTarget)->is_set() ){
                                if( $input_el->item(0)->tagName == "input" ){
                                    $input_el->item(0)->setAttribute("value", $this->input_values->getAccessor($elementTarget)->read());
                                }else{
                                    $input_el->item(0)->appendChild( $doc->createTextNode ( $this->input_values->getAccessor($elementTarget)->read() ) );
                                }
                            }
                        }
                    }
                }
                

                $hidden_id = $doc->createElement ('input');
                $hidden_id->setAttribute("type", "hidden");
                $hidden_id->setAttribute("name", "form_id");
                $hidden_id->setAttribute("value", $this->getIdentifierValue());
                // Creating an empty text node forces <script></script>
                $elements->item(0)->appendChild ($hidden_id);

            }
        }
        
        return $doc;
    }
}

