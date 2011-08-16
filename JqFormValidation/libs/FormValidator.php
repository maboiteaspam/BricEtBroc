<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\ValidatorFinder as ValidatorFinder;
use BricEtBroc\Form\InputValues as InputValues;
use BricEtBroc\Form\Validator as Validator;
use BricEtBroc\Form\CallbackValidator as CallbackValidator;
use BricEtBroc\Form\Dependency as Dependency;
use BricEtBroc\Form\Message as Message;
use BricEtBroc\Form\Messages as Messages;

class FormValidator{
    public $targetElement;
    public $options;
    
    public $rules;
    public $rules_errors;
    public $input_values;
    
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
        
        $val_ref = isset($options["validators"])?$options["validators"]:NULL;
        $dep_ref = isset($options["dependencies"])?$options["dependencies"]:NULL;
        $this->validator_finder = new ValidatorFinder($val_ref, $dep_ref);
        $this->has_parsed       = false;
    }
    
    /**
     *
     * @param array $input_values 
     */
    public function setDataSource( array $input_values, $data_source_name=InputValues::DFT_SRCE ){
        $this->input_values->setDataSource($input_values, $data_source_name );
    }
    
    /**
     *
     * @return bool
     */
    public function parseOptions(){
        $rules      = isset($this->options["rules"])? $this->options["rules"] : array();
        $messages   = isset($this->options["messages"])? $this->options["messages"] : array();
        
        foreach( $rules as $elementTarget => $validators ){
            if( isset( $this->rules[$elementTarget]) ) $oRule = $this->rules[$elementTarget];
            else{ $oRule = new Rule($elementTarget); $this->rules[$elementTarget] = $oRule; }
            
            $oRule->setAccessor( $this->input_values->getAccessor($elementTarget) );
            
            if( is_array($validators) == false ) $validators = array($validators=>true);
            
            foreach( $validators as $validator_name => $validator_assertion ){
                
                if( $validator_assertion instanceof Validator ){
                    $oValidator = $validator_assertion;
                }else{
                    $oValidator = $this->validator_finder->find($validator_name);
                    
                    if( $oValidator === NULL
                        && is_callable($validator_assertion) ){
                        $oValidator = new CallbackValidator();
                        $oValidator->setAccessor( $oRule->getAccessor() );
                        $oValidator->setAssertInformation($validator_assertion );
                    }else{
                        $oValidator->setAccessor( $oRule->getAccessor() );
                        $oValidator->setAssertInformation($validator_assertion );

                        if( is_string($validator_assertion) ){
                            $dep = $this->validator_finder->find_dependency($validator_assertion);
                            if( $dep !== NULL ){
                                $dep->setAccessor( $this->input_values->getAccessor($validator_assertion) );
                                $oValidator->setAssertInformation(NULL);
                                $oValidator->setDependency($dep);
                            }
                        }elseif( $validator_assertion instanceof Dependency ){
                            $oValidator->setAssertInformation(NULL);
                            $oValidator->setDependency($validator_assertion);
                        }
                    }
                    
                }
                
                $oRule->addValidator($validator_name, $oValidator);
            }
        }
        $this->has_parsed = true;
        return true;
    }
    
    /**
     *
     * @param array|string|null $remote_validators_id
     * @return type 
     */
    public function validate( $remote_validators_id=NULL ){
        if( ! $this->has_parsed )
            $this->parseOptions ();
        
        if( $remote_validators_id!==NULL 
            && is_array($remote_validators_id) === false )
            $remote_validators_id = array($remote_validators_id);
        
        foreach( $this->rules as $name=>$rule ){
            if( $rule->validate( $remote_validators_id ) === false ){
                $this->rules_errors[$name] = $rule;
            }
        }
        
        return !$this->hasErrors();
    }
    
    /**
     *
     * @return bool
     */
    public function hasErrors(){
        return count( $this->rules_errors) > 0;
    }
    
    /**
     *
     * @return Messages
     */
    public function getMessages(){
        $retour = new Messages();
        foreach( $this->rules_errors as $rule_name=>$rule ){
            /* @var $rule Rule */
            foreach( $rule->getErroneousValidator() as $v_name=> $validator ){
                $message = new Message();
                $message->target    = $rule->elementTarget;
                if( $rule->getAccessor()->is_set() )
                    $message->value     = $rule->getAccessor()->read();
                $message->message   = "";
                if( isset($this->options["messages"]) ){
                    if( isset($this->options["messages"][$rule_name]) ){
                        if( isset($this->options["messages"][$rule_name][$v_name])
                                && is_array($this->options["messages"][$rule_name]) ){
                            $message->message   = $this->options["messages"][$rule_name][$v_name];
                        }elseif( is_string($this->options["messages"][$rule_name]) ){
                            $message->message   = $this->options["messages"][$rule_name];
                        }
                    }
                }
                
                $retour[] = $message;
            }
        }
        return $retour;
    }
    
    public function __toJavascript(){
        if( ! $this->has_parsed )
            $this->parseOptions ();
        $temp = "";
        $temp .= "'rules':{ ";
        foreach( $this->rules as $rule ){
            $temp .= "\n".$rule->__toJavascript().",\n";
        }
        $temp = substr($temp,0,-strlen(",\n"));
        $temp .= " }\n";
        
        $orules = $this->options["rules"];
        $this->options["rules"] = "----";
        $retour = json_encode($this->options);
        $this->options["rules"] = $orules;
        $retour = str_replace('"rules":"----"', $temp, $retour);
        return $retour;
    }
    
    public function __toHTML( $surrounded = true ){
        $retour = "";
        
        $options = $this->__toJavascript();
        $retour = '
            $("form[name='.$this->targetElement.']").validate('.$options.');
            ';
        
        if( $surrounded ){
            $retour = '<script type="text/javascript">'.$retour.'</script>';
        }
        
        return $retour;
    }
}