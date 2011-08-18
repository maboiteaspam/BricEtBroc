<?php

class RuleFilter{
    public $elementTarget;
    public $filters;
    /**
     *
     * @var InputValueAccessor 
     */
    protected $accessor;
    
    public function __construct( $elementTarget ) {
        $this->elementTarget    = $elementTarget;
        $this->filters          = array();
        $this->accessor         = NULL;
    }
    
    public function setAccessor( InputValueAccessor $accessor ) {
        $this->accessor = $accessor;
    }
    
    /**
     *
     * @return InputValueAccessor 
     */
    public function getAccessor( ) {
        return $this->accessor;
    }
    
    /**
     *
     * @param Filter $filter
     */
    public function addFilter( $name, Filter $filter ){
        $this->filters[$name] = $filter;
    }
    
    /**
     *
     * @param array|null $remote_filters_id
     * @return bool
     */
    public function filter($remote_filters_id=NULL){
        $filters = array();
        if( $remote_filters_id === NULL ){
            $filters = $this->filters;
        }else{
            foreach( $this->filters as $name=>$validator ){
                if( in_array($validator->getIdentifier( ), $remote_filters_id) ){
                    $filters[$name] = $validator;
                }
            }
        }
        foreach( $filters as $name=>$filter ){
            $filter->filter();
        }
        return true;
    }
    
    public function __toJavascript(){
        $retour = "";
        
        
        return $retour;
    }
}