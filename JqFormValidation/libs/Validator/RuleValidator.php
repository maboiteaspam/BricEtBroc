<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\Validator as Validator;
use BricEtBroc\Form\InputValueAccessor as InputValueAccessor;

class RuleValidator{
    public $elementTarget;
    public $validators;
    public $validators_errors;
    
    public function __construct( $elementTarget ) {
        $this->elementTarget        = $elementTarget;
        $this->validators           = array();
        $this->validators_errors    = array();
        $this->accessor             = NULL;
    }
    
    
    /**
     *
     * @var InputValueAccessor 
     */
    protected $accessor;
    
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
     * @param Validator $Validator 
     */
    public function addValidator( $name, Validator $Validator ){
        $this->validators[$name] = $Validator;
    }
    
    /**
     *
     * @param array|null $remote_validators_id
     * @return bool
     */
    public function validate($remote_validators_id=NULL){
        $validators = array();
        if( $remote_validators_id === NULL ){
            $validators = $this->validators;
        }else{
            foreach( $this->validators as $name=>$validator ){
                if( in_array($validator->getIdentifier( ), $remote_validators_id) ){
                    $validators[$name] = $validator;
                }
            }
        }
        foreach( $validators as $name=>$validator ){
            if( $validator->validate( ) === false ){
                $this->validators_errors[$name] = $validator;
            }
        }
        return $this->hasErrors();
    }
    
    /**
     *
     * @return bool
     */
    public function hasErrors(){
        return count($this->validators_errors) === 0;
    }
    
    /**
     *
     * @return array
     */
    public function getErroneousValidator(){
        return $this->validators_errors;
    }
    
    
    public function reset(){
        $this->validators           = array();
        $this->validators_errors    = array();
    }
    
    public function __toJavascript(){
        $retour = "";
        $retour .= "'".$this->elementTarget."':";
        $retour .= "{";
            foreach( $this->validators as $v_name=>$v )
            $retour .= "'".$v_name."':".$v->__toJavascript().", ";
        $retour = substr($retour,0,-2);
        $retour .= "}";
        
        return $retour;
    }
}
