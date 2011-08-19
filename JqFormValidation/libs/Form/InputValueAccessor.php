<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\InputValues as InputValues;

class InputValueAccessor{
    /**
     *
     * @var InputValues
     */
    protected $input_values;
    /**
     *
     * @var string
     */
    protected $input_values_source;
    /**
     *
     * @var string
     */
    protected $data_source_target;
    /**
     *
     * @var string
     */
    protected $resolved_data_source_target;
    /**
     *
     * @var bool
     */
    protected $is_list;
    /**
     *
     * @var string
     */
    protected $list_name;
    
    public function __construct( $data_source_target ) {
        $this->data_source_target           = $data_source_target;
        $this->resolved_data_source_target  = NULL;
        $this->is_list                      = false;
        $this->list_name                    = NULL;
        $this->input_values_source          = InputValues::DFT_SRCE;
    }
    
    /**
     *
     * @param InputValues $data_source 
     */
    public function setInputValues( InputValues $input_values ){
        $this->input_values = $input_values;
    }
    
    /**
     *
     * @param string $input_values_source 
     */
    public function setInputValuesSource( $input_values_source ){
        $this->input_values_source = $input_values_source;
    }
    
    public function resolve_jq_expr(){
        if( $this->resolved_data_source_target !== NULL )
            return $this->resolved_data_source_target;
        $jq_pattern = '~^([a-z]*[.#])?([a-z0-9-_]+)(\[{1}[a-z-]+="?([a-z0-9-_]+)"?\])?(:([a-z]+))?$~i';
        $matches    = array();
        $found      = preg_match_all($jq_pattern, $this->data_source_target, $matches);
        
        $resolved   = NULL;
        $is_list    = false;
        $list_name  = NULL;
        if( $found > 0 ){
            if( isset($matches[4]) ){
                if( isset($matches[4][0])){
                    if( $matches[4][0] !== "" )
                        $resolved = $matches[4][0];
                }
            }
            if( $resolved === NULL ){
                if( isset($matches[2]) ){
                    if( isset($matches[2][0])){
                        if( $matches[2][0] !== "" )
                        $resolved = $matches[2][0];
                    }
                }
            }
            if( $resolved === NULL ){
                if( isset($matches[0]) ){
                    if( isset($matches[0][0])){
                        if( $matches[0][0] !== "" )
                        $resolved = $matches[0][0];
                    }
                }
            }
        }else{
            $jq_pattern = '~^([a-z]*[.#])?([a-z0-9-_]+)[\\\]?\[(([a-z0-9-_]+)|(\v*))[\\\]?\](:([a-z]+))?$~i';
            $matches    = array();
            $found      = preg_match_all($jq_pattern, $this->data_source_target, $matches);
            if( isset($matches[2]) ){
                if( isset($matches[2][0])){
                    if( $matches[2][0] !== "" )
                        $resolved   = $matches[2][0];
                        $is_list    = true;
                }
            }
            
            if( isset($matches[3]) ){
                if( isset($matches[3][0])){
                    if( $matches[3][0] !== "" )
                        $list_name  = $matches[3][0];
                }
            }
        }
        
        if( $resolved === NULL )
            $resolved = $this->data_source_target;
        
        $this->resolved_data_source_target = $resolved;
        $this->is_list      = $is_list;
        $this->list_name    = $list_name;
        return $this->resolved_data_source_target;
    }
    
    /**
     * Return true if target looks like
     *  name[] or name\[\] or name[test] or name\[test\]
     *
     * @return bool
     */
    public function is_list( ){
        $this->resolve_jq_expr();
        //- doit retourner true si la valeur est ciblée comme une liste
        //- par exemple name (n'est pas une liste) // name[] (est une liste)
        return $this->is_list;
    }
    
    public function list_name(){
        return $this->list_name;
    }
    
    public function is_textual( ){
        $this->input_values->isTextual($this->input_values_source);
    }
    
    public function is_set( ){
        $this->resolve_jq_expr();
        $r = $this->input_values->getDataSource($this->input_values_source);
        return isset( $r[$this->resolved_data_source_target] );
    }
    
    public function set($value){
        $r = $this->input_values->getDataSource($this->input_values_source);
        $r[$this->resolved_data_source_target] = $value;
        $this->input_values->setDataSource($r, $this->input_values_source);
    }
    
    public function read( ){
        $this->resolve_jq_expr();
        $r = $this->input_values->getDataSource($this->input_values_source);
        if( $this->is_set($this->input_values_source) )
            return $r[$this->resolved_data_source_target];
        throw new \Exception("Key ".$this->resolved_data_source_target." doesnt exists in ".$this->input_values_source." data source");
    }
}