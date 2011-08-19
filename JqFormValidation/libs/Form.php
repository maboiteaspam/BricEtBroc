<?php

namespace BricEtBroc\Form;

class Form{
    public $targetElement;
    public $options;
    public $has_parsed;
    public $input_values;
    
    public $components_ref = array(
        "filtration"=>"BricEtBroc\Form\FormFilter",
        "validation" =>"BricEtBroc\Form\FormValidator",
    );
    
    public $components;
    public $listeners;
    
    /**
     *
     * @param string $targetElement
     * @param array $options 
     */
    public function __construct( $targetElement, $method, $options ){
        $this->targetElement= $targetElement;
        $this->options      = $options;
        $this->has_parsed   = false;
        $this->components   = array();
        $this->input_values = new InputValues( strtolower($method)==="post"?$_POST:$_GET );
        $this->input_values->setDataSource($_FILES, "files");
        
        $components         = isset($options["components"])? $options["components"] : array();
        unset( $this->options["components"]);
        
        $this->components_ref = array_merge( $this->components_ref, $components );
        
    }
    
    /**
     *
     * @return bool
     */
    public function initComponents(){
        $this->has_parsed   = true;
        
        foreach( $this->components_ref as $component_name => $component_class ){
            $component = new $component_class( $targetElement, $options );
            $component->attachTo( $this );
            $this->components[ $component_name ] = $component;
        }
        
        return true;
    }
    
    public function listenTo( $listened_event, $listener_component, $component_call ){
        $component_name = array_shift( array_keys($this->components, $listener_component) );
        
        if( isset($this->listeners[$listened_event]) === false )
            $this->listeners[$listened_event] = array();
        $this->listeners[$listened_event][$component_name] = $component_call;
    }
    
    public function __call($method_name, $method_arguments){
        $retour = array();
        if( isset($this->listeners["before_".$name]) ){
            foreach( $this->listeners["before_".$name] as $component_name => $component_call ){
                $component = $this->components[ $component_name ];
                call_user_func_array(array($component, $component_call), array());
            }
        }
        
        foreach( $this->components as $component_name => $component ){
            if(method_exists($component, $method_name) ){
                $retour[$component_name] = call_user_func_array(array($component, $method_name), $method_arguments);
            }
        }
        
        
        if( isset($this->listeners["after_".$name]) ){
            foreach( $this->listeners["before_".$name] as $component_name => $component_call ){
                $component = $this->components[ $component_name ];
                call_user_func_array(array($component, $component_call), array());
            }
        }
        
        if( count($retour) === 1 ) $retour = array_shift ($retour);
        return $retour;
    }
}