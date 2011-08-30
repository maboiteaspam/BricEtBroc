<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\ValidatorFinder as ValidatorFinder;
use BricEtBroc\Form\InputValues as InputValues;
use BricEtBroc\Form\Validator as Validator;
use BricEtBroc\Form\CallbackValidator as CallbackValidator;
use BricEtBroc\Form\Dependency as Dependency;
use BricEtBroc\Form\Message as Message;
use BricEtBroc\Form\Messages as Messages;
use BricEtBroc\Form\RuleValidator as RuleValidator;
use BricEtBroc\Form\Form as Form;
use BricEtBroc\Form\IFormComponent as IFormComponent;

class FormValidator implements IFormComponent, IHtmlWriter{
    public $targetElement;
    public $options;
    
    public $rules;
    public $rules_errors;
    public $input_values;
    public $validator_finder;
    
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
     * @param Form $Form 
     */
    public function attachTo( Form $Form ){
        $Form->listenTo("before_validate", $this, "filter");
        $this->setInputValues($Form->input_values);
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
        $this->has_parsed = true;
        $rules      = isset($this->options["rules"])? $this->options["rules"] : array();
        $messages   = isset($this->options["messages"])? $this->options["messages"] : array();
        
        foreach( $rules as $elementTarget => $validators ){
            if( isset( $this->rules[$elementTarget]) ) $oRule = $this->rules[$elementTarget];
            else{ $oRule = new RuleValidator($elementTarget); $this->rules[$elementTarget] = $oRule; }
            
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
                    }elseif( $oValidator !== NULL){
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
                            $validator_assertion->setAccessor( $this->input_values->getAccessor($validator_assertion) );
                        }
                    }else{
                        //- no validator found..
                        throw new \Exception("Unknown validator for $validator_name=>".  var_export($validator_assertion, true));
                    }
                    
                }
                
                $oRule->addValidator($validator_name, $oValidator);
            }
        }
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
    public function getErrors(){
        $retour = new Messages();
        foreach( $this->rules_errors as $rule_name=>$rule ){
            /* @var $rule RuleValidator */
            foreach( $rule->getErroneousValidator() as $v_name=> $validator ){
                $message = new Message();
                $message->target    = $rule->elementTarget;
                if( $rule->getAccessor()->is_set()
                        && $rule->getAccessor()->is_textual()
                        && $rule->getAccessor()->is_list() === false )
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
                
                $retour[$rule->elementTarget] = $message;
            }
        }
        return $retour;
    }
    
    public function __optionsToJavascript(){
        if( ! $this->has_parsed )
            $this->parseOptions ();
        
        $temp = "";
        if( count($this->rules) > 0 ){
            $temp .= "'rules':{ ";
            foreach( $this->rules as $rule ){
                $temp .= "\n".$rule->__toJavascript().",\n";
            }
            $temp = substr($temp, 0, -strlen(",\n"));
            $temp .= " }\n";
        }
        
        $retour = $temp;
        if( isset($this->options["messages"]) ){
            $retour .= ",".json_encode($this->options["messages"])."\n";
        }
        $retour .= ",'errorPlacement':function(error, element){
                var el = $('#error-' + element.attr('id') );
                if( el.length > 0 ){
                    error.appendTo( el );
                }
            }";
        $retour .= ",'debug':true";
        
        
        /*
        $orules = $this->options["rules"];
        $this->options["rules"] = "----";
        $retour = json_encode($this->options);
        $this->options["rules"] = $orules;
        $retour = str_replace('"rules":"----"', $temp, $retour);
         */
        
        return "{".$retour."}";
    }
    
    public function __toHTML( $surrounded = true ){
        $retour = "";
        
        $options = $this->__optionsToJavascript();
        $retour = '
            $("form[name='.$this->targetElement.']").validate('.$options.');
            ';
        $retour = '
            $(document).ready(function(){'.$retour.'});
            ';
        
        if( $surrounded ){
            $retour = '<script type="text/javascript">'.$retour.'</script>';
        }
        
        return $retour;
    }
    
    public function render( $has_validated, \DOMDocument $doc ){
        $xpath      = new \DOMXpath($doc);
        $elements   = $xpath->query("/html/head");
        
        $messages   = new Messages();
        $has_errors = false;
        if( $has_validated ){
            $has_errors = $this->hasErrors();
            $messages   = $this->getErrors();
        }

        if ( $elements->length > 0 ) {
            //$elements
            $script = $doc->createElement ('script');
            // Creating an empty text node forces <script></script>
            $script->appendChild( $doc->createTextNode ( $this->__toHTML(false) ) );
            $elements->item(0)->appendChild ($script);
            
            if( count($messages)> 0 ){
                $no_script_styles = "";
                foreach( $messages as $elementTarget=>$message ){
                    $no_script_styles .= "#error-".$elementTarget."{display:block;}";
                }
                $no_script = $doc->createElement ('noscript');
                $no_script->appendChild( $doc->createTextNode ( $no_script_styles ) );
                $elements->item(0)->appendChild ($no_script);
            }
        }
        
        foreach( $this->rules as $elementTarget => $rule ){
            $error_el   = $xpath->query("//*[@id='error-".$elementTarget."']");
            $el         = null;
            if( $error_el === false ){
                $el = $doc->createElement ('span');
                $el->setAttribute( "id", "error-".$elementTarget );
                $el->setAttribute( "class", "error-element");
                
                $input_el   = $xpath->query("//form[@name='".$this->targetElement."']//[@name='".$elementTarget."']");
                if( $input_el !== false ){
                    if( $input_el->length > 0 ){
                        $input_el->item(0)->parentNode->insertBefore($el, $input_el->item(0));
                    }
                }
            }else if( $error_el->length === 0 ){
                $el = $doc->createElement ('span');
                $el->setAttribute( "id", "error-".$elementTarget );
                $el->setAttribute( "class", "error-element");
                $input_el   = $xpath->query("//form[@name='".$this->targetElement."']//*[@name='".$elementTarget."']");
                if( $input_el !== false ){
                    if( $input_el->length > 0 ){
                        $input_el->item(0)->parentNode->insertBefore($el, $input_el->item(0));
                    }
                }
            }
            
            if( $el === null ){
                $el = $error_el->item(0);
            }
            
            if( $has_validated && $has_errors && isset($messages[$elementTarget]) ){
                $el->appendChild( $doc->createTextNode ( $messages[$elementTarget]->value ) );
            }
        }
        
        return $doc;
    }
}