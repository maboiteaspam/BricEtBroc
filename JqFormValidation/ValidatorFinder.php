<?php

class ValidatorFinder{
    public $validators_ref = array(
        "required"  =>"RequiredValidator",
        "email"     =>"EmailValidator",
        "minlength" =>"MinLengthValidator",
        "maxlength" =>"MaxLengthValidator",
        "ajax"      =>"AjaxValidator",
        "regex"     =>"RegexValidator",
        "mincount"  =>"MinCountValidator",
        "maxcount"  =>"MaxCountValidator",
    );
    public $dependencies_ref = array(
        "checked"   =>"CheckedDependency",
        "selected"  =>"SelectedDependency",
        "unchecked" =>"UncheckedDependency",
        "notblank"  =>"NotBlankDependency",
        "blank"     =>"BlankDependency",
        );
    
    public function __construct($validators_refs = null, $dependencies_ref=null) {
        if( $validators_refs !== NULL)
            $this->validators_ref = $validators_refs;
        if( $dependencies_ref !== NULL)
            $this->dependencies_ref = $dependencies_ref;
    }
    
    /**
     *
     * @param type $validator_name
     * @return Validator
     */
    public function find( $validator_name ){
        if( isset($this->validators_ref[$validator_name]) === false ){
            return NULL;
        }
        
        $validatorClassName = $this->validators_ref[$validator_name];
        return new $validatorClassName();
    }
    
    public function find_dependency( $dependency_expr ){
        foreach( $this->dependencies_ref as $dep_id => $dep_ref ){
            // we must keep semi colon here.
            if( substr($dependency_expr,-strlen(":".$dep_id)) === ":".$dep_id ){
                return new $dep_ref( );
            }
        }
        return null;
    }
}