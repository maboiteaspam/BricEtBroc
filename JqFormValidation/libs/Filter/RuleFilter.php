<?php
namespace BricEtBroc\Form;

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
     * @return bool
     */
    public function filter(){
        $filters = $this->filters;
        foreach( $filters as $name=>$filter ){
            $filter->filter();
        }
        return true;
    }
    
    public function __toJavascript(){
        $retour = "";
        $retour .= "'".$this->elementTarget."':";
        $retour .= "{";
            foreach( $this->filters as $v_name=>$v )
            $retour .= "'".$v_name."':".$v->__toJavascript().", ";
        $retour = substr($retour,0,-2);
        $retour .= "}";
        
        return $retour;
    }
}