<?php
namespace BricEtBroc\Form;

class FilterFinder{
    public $filters_ref = array(
        "trim"      =>"BricEtBroc\Form\TrimFilter",
        "nohtml"    =>"BricEtBroc\Form\NoHTMLFilter",
        "nochars"   =>"BricEtBroc\Form\NoCharsFilter",
    );
    
    /**
     *
     * @param array|null $validators_refs
     */
    public function __construct($filters_ref = null) {
        if( $filters_ref !== NULL)
            $this->filters_ref = array_merge($this->filters_ref, $filters_ref );
    }
    
    /**
     *
     * @param string $filter_name
     * @return Filter
     */
    public function find( $filter_name ){
        if( isset($this->filters_ref[$filter_name]) === false ){
            return NULL;
        }
        
        $filterClassName = $this->filters_ref[$filter_name];
        return new $filterClassName();
    }
    
}