<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\IFormComponent as IFormComponent;

/**
 * Description of FormSearSurf
 *
 * @author clement
 */
class FormTooltip implements IFormComponent {
    public $targetElement;
    public $options;
    public $parsed_options;
    
    public $input_values;
    
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
        $this->has_parsed       = true;
        $this->parsed_options   = "";
        
        $this->parsed_options   .= "{";
        if( isset($this->options["tooltip"]) ){
            foreach( $this->options["tooltip"] as $option_name => $option_value ){
                if( is_callable($option_value)){
                    $option_value = call_user_func_array($option_value, array($this));
                }elseif(is_string ($option_value) ){
                    $option_value = "'".$option_value."'";
                }else{
                    $option_value = (string)$option_value;
                }
                $this->parsed_options .= "'".$option_name."':".$option_value.", ";
            }
        }
        //$this->parsed_options = substr($this->parsed_options,0,-2);
        $this->parsed_options   .= "bodyHandler: function() {
                                     return $('#error-'+this.name).html();
                                   }";
        $this->parsed_options   .= "}";
        
        return true;
    }
    
    
    public function __toHTML( $surrounded = true ){
        if( ! $this->has_parsed )
            $this->parseOptions ();
        
        $retour = "";
        if( isset($this->options["rules"]) ){
            foreach( $this->options["rules"] as $elementTarget=>$infos ){
                $retour .= '
                    $("form[name='.$this->targetElement.']").find("[name='.$elementTarget.']").tooltip('.$this->parsed_options.');
                    ';
            }
        }
        if( $retour !== "" ){
            $retour = '
                $(document).ready(function(){'.$retour.'});
                ';
            if( $surrounded ){
                $retour = '<script type="text/javascript">'.$retour.'</script>';
            }
        }
        
        
        return $retour;
    }
    
    public function render( $is_submitted, $has_validated, \DOMDocument $doc ){
        $xpath      = new \DOMXpath($doc);
        $elements   = $xpath->query("/html/head");

        if ( $elements->length > 0) {
            $script = $doc->createElement ('script');
            $script->setAttribute("type", "text/javascript");
            // Creating an empty text node forces <script></script>
            $script->appendChild( $doc->createTextNode ( $this->__toHTML(false) ) );
            $elements->item(0)->appendChild ($script);
        }
        return $doc;
    }
}

