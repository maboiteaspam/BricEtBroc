<?php
namespace BricEtBroc\Form;

class ValidatorFinder{
    public $validators_ref = array(
        "required"  =>"BricEtBroc\Form\RequiredValidator",
        "email"     =>"BricEtBroc\Form\EmailValidator",
        "minlength" =>"BricEtBroc\Form\MinLengthValidator",
        "maxlength" =>"BricEtBroc\Form\MaxLengthValidator",
        "ajax"      =>"BricEtBroc\Form\AjaxValidator",
        "regex"     =>"BricEtBroc\Form\RegexValidator",
        "mincount"  =>"BricEtBroc\Form\MinCountValidator",
        "maxcount"  =>"BricEtBroc\Form\MaxCountValidator",
    );
    public $dependencies_ref = array(
        "checked"   =>"BricEtBroc\Form\CheckedDependency",
        "selected"  =>"BricEtBroc\Form\SelectedDependency",
        "unchecked" =>"BricEtBroc\Form\UncheckedDependency",
        "notblank"  =>"BricEtBroc\Form\NotBlankDependency",
        "blank"     =>"BricEtBroc\Form\BlankDependency",
        );
    
    /**
     *
     * @param array|null $validators_refs
     * @param array|null $dependencies_ref 
     */
    public function __construct($validators_refs = null, $dependencies_ref=null) {
        if( $validators_refs !== NULL)
            $this->validators_ref = array_merge($this->validators_ref, $validators_refs );
        if( $dependencies_ref !== NULL)
            $this->dependencies_ref = array_merge($this->dependencies_ref, $dependencies_ref );
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
    
    /**
     *
     * @param string $dependency_expr
     * @return dep_ref 
     */
    public function find_dependency( $dependency_expr ){
        foreach( $this->dependencies_ref as $dep_id => $dep_ref ){
            // we must keep semi colon here.
            if( substr($dependency_expr, -strlen(":".$dep_id)) === ":".$dep_id ){
                return new $dep_ref( );
            }
        }
        return null;
    }
}