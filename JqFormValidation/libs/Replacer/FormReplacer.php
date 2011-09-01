<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\IFormComponent as IFormComponent;

/**
 * Description of FormSearSurf
 *
 * @author clement
 */
class FormReplacer implements IFormComponent {
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
        $this->replace_content  = "";
        $this->has_parsed       = false;
    }
    
    /**
     *
     * @param Form $Form 
     */
    public function attachTo( Form $Form ){
        //$Form->listenTo("after_render", $this, "render");
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
        $this->replace_content = isset($this->options["replace_content"])?$this->options["replace_content"]:"";
        return true;
    }
    
    public function render( $is_submitted, $has_validated, \DOMDocument $doc ){
        if( ! $this->has_parsed )
            $this->parseOptions ();
            $xpath      = new \DOMXpath($doc);
        
            $elements   = $xpath->query("//form[@name='".$this->targetElement."']");
            if( $elements !== false && $elements->length > 0 ){
                $form_node = $elements->item(0);
                
                $orgdoc = new \DOMDocument;
                /**
                 * @todo check that is correct .... Or implement some more complicated logic
                 */
                $orgdoc->loadHTML(utf8_decode($this->replace_content));
                $p_node     = $form_node->parentNode;
                $c_nodes    = $orgdoc->documentElement->firstChild->childNodes;
                for( $i=0; $i< $c_nodes->length; $i++ ){
                    $new_node = $doc->importNode($c_nodes->item( $i ), true);
                    if( $i === 0 ){
                        $p_node->replaceChild( $new_node, $form_node );
                        $nextNode = $new_node->nextSibling;
                    }else{
                        $p_node->insertBefore($new_node, $nextNode );
                    }
                }
            }
        
        return $doc;
    }
}

