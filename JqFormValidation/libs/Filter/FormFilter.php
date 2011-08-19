<?php
namespace BricEtBroc\Form;

class FormFilter{
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
        $this->options          = $options;
        $this->input_values     = new InputValues( array() );
        $this->rules            = array();
        $this->rules_errors     = array();
        
        $filters_ref            = isset($options["filters"])?$options["filters"]:NULL;
        $this->filter_finder    = new FilterFinder($filters_ref);
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
        $rules      = $this->options;
        unset( $rules["filters"] );
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
        
        $options = $this->__optionsToJavascript();
        $retour = '
            $("form[name='.$this->targetElement.']").filtertext('.$options.');
            ';
        
        if( $surrounded ){
            $retour = '<script type="text/javascript">'.$retour.'</script>';
        }
        
        return $retour;
    }
    
}