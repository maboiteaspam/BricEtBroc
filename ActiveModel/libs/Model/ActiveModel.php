<?php
class ActiveModel implements IteratorAggregate {
    
    private $_values;
    private $_ro_db_values;
    private $_virtual_values;
    private $hidden_getters;
    private $hidden_setters;
    private $hidden_calls;
    
    public final function __construct() {
        $this->_values              = new ArrayObject();
        $this->_ro_db_values        = new ArrayObject();
        $this->_virtual_values      = new ArrayObject();
        $this->hidden_getters       = new ArrayObject();
        $this->hidden_setters       = new ArrayObject();
        $this->hidden_calls         = new ArrayObject();
        ActiveModelController::register($this);
    }
    
    public function __get( $prop ){
        if( $prop === "hidden_getters" ){
            return $this->hidden_getters;
        }elseif( $prop === "hidden_setters" ){
            return $this->hidden_setters;
        }elseif( $prop === "hidden_calls" ){
            return $this->hidden_calls;
        }elseif( $prop === "_values" ){
            return $this->_values;
        }elseif( $prop === "_ro_db_values" ){
            return $this->_ro_db_values;
        }elseif( $prop === "_virtual_values" ){
            return $this->_virtual_values;
        }elseif( isset($this->hidden_getters[$prop]) ){
            return call_user_func_array($this->hidden_getters[$prop], array($this, $prop));
        }
        throw new Exception("There is no GET property with name '".$prop."'");
    }
    public function __set( $prop, $value ){
        if( isset($this->hidden_setters[$prop]) ){
            return call_user_func_array($this->hidden_setters[$prop], array($this, $prop, $value));
        }
        throw new Exception("There is no SET property with name '".$prop."'");
    }
    public function __call( $method, $args ){
        if( isset($this->hidden_calls[$method]) ){
            return call_user_func_array($this->hidden_calls[$method], $args);
        }
    }

    /**
     * @return ArrayObject
     */
    public function getIterator() {
        return new ArrayObject( array_merge($this->_values->getArrayCopy(), 
                                            $this->_virtual_values->getArrayCopy()//,
                                            //$this->_ro_db_values->getArrayCopy()
                                            ) );
    }
    public function __toString(  ){
       $retour = get_class($this)."\n";
       foreach( $this->getIterator() as $p_n => $p ){
           $retour .= $p_n." => ".$p."\n";
       }
       return $retour;
    }
    
    /**
     * @static
     * @return ActiveModelSelectBuilder
     */
    public static function select($expr=null, $as=null){
        $retour = ActiveModelController::get_instance()
                ->getModelQueryBuilder()
                ->select(get_called_class());
        if( $expr !== null )
            $retour->select ($expr, $as);
        return $retour;
    }
    
    
    public function save(){
        
    }
    public function delete(){
        
    }
    public function attach( ActiveModel $o ){
        
    }
}