<?php

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
        
        $filters_ref = isset($options["filters"])?$options["filters"]:NULL;
        $this->filter_finder    = new ValidatorFinder($filters_ref);
        $this->has_parsed       = false;
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
        $rules      = isset($this->options["filter"])? $this->options["filter"] : array();
        
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
        $this->has_parsed = true;
        return true;
    }
    
    
    /**
     *
     * @param array|string|null $remote_filters_id
     * @return type 
     */
    public function filter( $remote_filters_id=NULL ){
        if( ! $this->has_parsed )
            $this->parseOptions ();
        
        if( $remote_filters_id!==NULL 
            && is_array($remote_filters_id) === false )
            $remote_filters_id = array($remote_filters_id);
        
        foreach( $this->rules as $name=>$rule ){
            $rule->filter( $remote_filters_id );
        }
        
        return true;
    }
    
    public function __toHTML( $surrounded = true ){
        $retour = "";
        
        if( $surrounded ){
            $retour = '<script type="text/javascript">'.$retour.'</script>';
        }
        
        return $retour;
    }
    
}