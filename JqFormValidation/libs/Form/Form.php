<?php

namespace BricEtBroc\Form;

class Form{
    public $targetElement;
    public $options;
    public $has_parsed;
    public $input_values;
    
    public $default_components_ref = array(
        "postsubmit"    =>"BricEtBroc\Form\FormPostSubmit",
        "csrf"          =>"BricEtBroc\Form\FormSeaSurf",
        "filtration"    =>"BricEtBroc\Form\FormFilter",
        "validation"    =>"BricEtBroc\Form\FormValidator",
        //"error"         =>"BricEtBroc\Form\FormTooltip",
    );
    
    public $components_to_load;
    public $components;
    public $pending_listeners;
    public $listeners;
    
    /**
     *
     * @param string $targetElement
     * @param array $options 
     */
    public function __construct( $targetElement, $method, $options, $components_ref=array() ){
        $this->targetElement= $targetElement;
        $this->options      = $options;
        $this->has_parsed   = false;
        $this->components   = array();
        $this->input_values = new InputValues( strtolower($method)==="post"?$_POST:$_GET );
        $this->input_values->setDataSource($_FILES, "files");
        
        $this->setComponentsRef( array_merge( $this->default_components_ref, $components_ref ) );
    }
    
    public function setOptions( $options ){
        $this->options = $options;
    }
    
    public function setComponentsRef( $components_ref ){
        $this->components_to_load   = $components_ref;
        $this->components           = array();
        $this->has_parsed           = false;
        $this->pending_listeners    = array();
        $this->listeners            = array();
    }
    
    /**
     *
     * @return bool
     */
    public function initComponents(){
        $this->has_parsed   = true;
        
        foreach( $this->components_to_load as $component_name => $component_class ){
            $component = new $component_class( $this->targetElement, $this->options );
            $component->attachTo( $this );
            $this->components[ $component_name ] = $component;
        }
        
        foreach( $this->pending_listeners as $listened_event=> $components_invocation ){
            foreach( $components_invocation as $component_invocation ){
                if( isset($this->listeners[$listened_event]) === false )
                    $this->listeners[$listened_event] = array();
                $this->listeners[$listened_event][$component_name] = $component_invocation;
            }
        }
        $this->pending_listeners = array();
        
        return true;
    }
    
    /**
     *
     * @param string $listened_event
     * @param IFormComponent $listener_component
     * @param string $component_call 
     */
    public function listenTo( $listened_event, IFormComponent $listener_component, $component_call ){
        if( isset($this->pending_listeners[$listened_event]) === false )
            $this->pending_listeners[$listened_event] = array();
        $this->pending_listeners[$listened_event][] = array($listener_component, $component_call);
    }
    
    /**
     *
     * @param string $method_name
     * @param array $method_arguments
     * @return mixed 
     */
    public function __call($method_name, $method_arguments){
        if( $this->has_parsed === false ) $this->initComponents ();
        
        $cancel = false;
        if( isset($this->listeners["required_to_".$method_name]) ){
            foreach( $this->listeners["required_to_".$method_name] as $component_name => $component_invocation ){
                $required_status = call_user_func_array($component_invocation, array());
                if( $required_status === false ){
                    $cancel = true;
                    break;
                }
            }
        }
        
        if( $cancel ){
            return false;
        }
        
        
        $retour = array();
        if( isset($this->listeners["before_".$method_name]) ){
            foreach( $this->listeners["before_".$method_name] as $component_name => $component_invocation ){
                call_user_func_array($component_invocation, array());
            }
        }
        
        
        
        foreach( $this->components as $component_name => $component ){
            if(method_exists($component, $method_name) ){
                $retour[$component_name] = call_user_func_array(array($component, $method_name), $method_arguments);
            }
        }
        
        
        if( isset($this->listeners["after_".$method_name]) ){
            foreach( $this->listeners["after_".$method_name] as $component_name => $component_invocation ){
                call_user_func_array($component_invocation, array());
            }
        }
        
        if( count($retour) === 1 ) $retour = array_shift ($retour);
        return $retour;
    }
}