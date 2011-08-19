<?php
namespace BricEtBroc\Form;
use BricEtBroc\Form\IJavascripter as IJavascripter;

abstract class Dependency implements IJavascripter{
    /**
     *
     * @var InputValueAccessor 
     */
    protected $accessor;
    
    public function __construct( ) {
    }
    
    public function setAccessor( InputValueAccessor $accessor ) {
        $this->accessor = $accessor;
    }
    public function confirm(){
        return $this->is_confirmed( $this->accessor );
    }
    public abstract function is_confirmed( InputValueAccessor $accessor );
    public function __toJavascript(){
        return "'".$this->accessor->data_source_target."'";
    }
}
