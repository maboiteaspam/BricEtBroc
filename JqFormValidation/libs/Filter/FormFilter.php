<?php
namespace BricEtBroc\Form;


use BricEtBroc\Form\RuleFilter as RuleFilter;
use BricEtBroc\Form\FilterFinder as FilterFinder;
use BricEtBroc\Form\InputValues as InputValues;
use BricEtBroc\Form\CallbackFilter as CallbackFilter;
use BricEtBroc\Form\Form as Form;

use BricEtBroc\Form\IHtmlWriter as IHtmlWriter;
use BricEtBroc\Form\IFormComponent as IFormComponent;

class FormFilter implements IFormComponent, IHtmlWriter{
    public $targetElement;
    public $options;
    
    public $rules;
    public $input_values;
    public $filter_finder;
    
    public $has_parsed;
    
    /**
     *
     * @param string $targetElement
     * @param array $options 
     */
    public function __construct( $targetElement, $options ){
        $this->targetElement    = $targetElement;
        $this->input_values     = new InputValues( array() );
        $this->rules            = array();
        
        $filters_ref            = isset($options["filters"])?$options["filters"]:NULL;
        $this->filter_finder    = new FilterFinder($filters_ref);
        unset( $options["filters"] );
        $this->options          = $options;
        $this->has_parsed       = false;
    }
    
    /**
     *
     * @param Form $Form 
     */
    public function attachTo( Form $Form ){
        $Form->listenTo("before_validate", $this, "filter");
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
        if( isset($this->options["filter"]) === false ){
            return;
        }
        $rules      = $this->options["filter"];
        
        foreach( $rules as $elementTarget => $filters ){
            if( isset( $this->rules[$elementTarget]) ) $oRule = $this->rules[$elementTarget];
            else{ $oRule = new RuleFilter($elementTarget); $this->rules[$elementTarget] = $oRule; }
            
            $oRule->setAccessor( $this->input_values->getAccessor($elementTarget) );
            
            if( is_array($filters) == false ) $filters = array($filters=>true);
            
            foreach( $filters as $filter_name => $filter_assertion ){
                
                if( $filter_assertion instanceof Filter ){
                    $oFilter = $filter_assertion;
                }else{
                    $oFilter = $this->filter_finder->find($filter_name);
                    
                    if( $oFilter === NULL
                        && is_callable($filter_assertion) ){
                        $oFilter = new CallbackFilter();
                        $oFilter->setAccessor( $oRule->getAccessor() );
                        $oFilter->setAssertInformation( $filter_assertion );
                    }elseif( $oFilter !== NULL){
                        $oFilter->setAccessor( $oRule->getAccessor() );
                        $oFilter->setAssertInformation($filter_assertion );
                    }else{
                        //- no validator found..
                        throw new \Exception("Unknown filter for $filter_name=>".  var_export($filter_assertion, true));
                    }
                    
                }
                
                $oRule->addFilter($filter_name, $oFilter);
            }
        }
        return true;
    }
    
    
    /**
     *
     * @return type 
     */
    public function filter( ){
        if( ! $this->has_parsed )
            $this->parseOptions ();
        
        foreach( $this->rules as $name=>$rule ){
            $rule->filter(  );
        }
        
        return true;
    }
    
    public function __optionsToJavascript(){
        if( ! $this->has_parsed )
            $this->parseOptions ();
        $retour = "";
        $retour .= "{ ";
        
        foreach( $this->rules as $rule ){
            $retour .= "\n".$rule->__toJavascript().",\n";
        }
        $retour = substr($retour,0,-strlen(",\n"));
        $retour .= " }\n";
        
        return $retour;
    }
    
    public function __toHTML( $surrounded = true ){
        $retour = "";
        if( isset($this->options["filter"]) === false ){
            return $retour;
        }
        
        $options = $this->__optionsToJavascript();
        $retour = '
            $("form[name='.$this->targetElement.']").filtertext('.$options.');
            ';
        $retour = '
            $(document).ready(function(){'.$retour.'});
            ';
        
        if( $surrounded ){
            $retour = '<script type="text/javascript">'.$retour.'</script>';
        }
        
        return $retour;
    }
    
    public function render( $is_submitted, $has_validated, \DOMDocument $doc ){
        if( isset($this->options["filter"]) === false ){
            return $doc;
        }
        $xpath      = new \DOMXpath($doc);
        $elements   = $xpath->query("/html/head");

        if ( $elements->length > 0 ) {
            //$elements
            $script = $doc->createElement ('script');
            $script->setAttribute("type", "text/javascript");
            // Creating an empty text node forces <script></script>
            $script->appendChild( $doc->createTextNode ( $this->__toHTML(false) ) );
            $elements->item(0)->appendChild ($script);
        }
        return $doc;
    }
}