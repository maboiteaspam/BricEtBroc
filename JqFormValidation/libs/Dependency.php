<?php
namespace BricEtBroc\Form;

abstract class Dependency{
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
    public abstract function is_confirmed();
    public function __toJavascript(){
        return "'".$this->accessor->data_source_target."'";
    }
}
