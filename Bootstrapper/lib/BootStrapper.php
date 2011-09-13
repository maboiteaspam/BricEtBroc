<?php
namespace BricEtBroc\BootStrapper;

/**
 * Description of BootStrapper
 *
 * @author clement
 */
class BootStrapper{
    
    protected $events;
    protected $event_triggers;
    protected $variables;
    
    public function __construct(){
        $this->events           = array();
        $this->event_triggers   = array();
        $this->variables        = array();
    }
    
    /**
     *
     * @param type $event_name
     * @param type $event_handler 
     */
    public function add_event_listener( $event_name, $event_handler_name, $event_handler=null ){
        if( isset($this->events[$event_name]) === false )
            $this->events[$event_name] = array();
        
        // - a two arguments call add_event_listener($event_name, $event_handler)
        if( $event_handler === null ){
            $this->events[$event_name][] = $event_handler_name;
        }else{
            // - a 3 arguments call add_event_listener($event_name, $event_handler_name, $event_handler)
            $this->events[$event_name][$event_handler_name] = $event_handler;
        }
    }
    
    /**
     *
     * @param type $event_name
     * @param type $event_trigger 
     */
    public function add_event_trigger( $event_name, $event_trigger ){
        $this->event_triggers[$event_name] = $event_trigger;
    }
    
    /**
     *
     * @param type $event_name
     * @param type $args
     * @return type 
     */
    public function trigger_event($event_name, $args=array()){
        if( isset($this->events[$event_name]) ){
            if( isset($this->event_triggers[$event_name]) ){
                $trigger = $this->event_triggers[$event_name];
                return call_user_func_array($trigger, array_merge(array($this, $this->events[$event_name]), $args) );
            }else{
                foreach( $this->events[$event_name] as $index=>$event ){
                    call_user_func_array($event, array_merge(array($this), $args) );
                }
                return true;
            }
        }
        return false;
    }
    
    /**
     *
     * @param type $variable_name
     * @param type $default_value 
     */
    public function add_variable( $variable_name, $default_value=null){
        $this->variables[$variable_name] = $default_value;
    }
    
    /**
     *
     * @param type $variable_name
     * @param type $value_name
     * @return type 
     */
    public function get($variable_name, $value_name=null){
        // - a 1 arguments call set($variable_name)
        if( $value_name === null ){
            return ($this->variables[$variable_name]);
        }else{
            // - a 2 arguments call set($variable_name, $value_name)
            return ($this->variables[$variable_name][$value_name]);
        }
    }
    
    /**
     *
     * @param type $variable_name
     * @param type $value_name
     * @param type $value 
     */
    public function set( $variable_name, $value_name, $value=null ){
        // - a two arguments call set($variable_name, $value)
        if( $value === null ){
            $this->variables[$variable_name] = $value_name;
        }else{
            // - a 3 arguments call set($variable_name, $value_name, $value)
            $this->variables[$variable_name][$value_name] = $value;
        }
        $this->trigger_event($variable_name.".on_change");
    }
    
    /**
     *
     * @param type $variable_name
     * @param type $value_name
     * @return bool
     */
    public function has( $variable_name, $value_name=null ){
        // - a two arguments call set($variable_name, $value)
        if( $value_name === null ){
            return isset($this->variables[$variable_name]);
        }else{
            // - a 3 arguments call set($variable_name, $value_name, $value)
            return isset($this->variables[$variable_name][$value_name]);
        }
        return false;
    }
    
    /**
     *
     * @param type $variable_name
     * @param type $value_name 
     */
    public function unset_($variable_name, $value_name=null){
        // - a 1 arguments call set($variable_name)
        if( $value_name === null ){
            unset($this->variables[$variable_name]);
        }else{
            // - a 2 arguments call set($variable_name, $value_name)
            unset($this->variables[$variable_name][$value_name]);
        }
    }
    
    /**
     *
     * @param type $variable_name
     * @param type $event_handler 
     */
    public function on_change($variable_name, $event_handler){
        $this->add_event_listener($variable_name.".on_change", $event_handler);
    }
}