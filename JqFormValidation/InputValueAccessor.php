<?php
namespace BricEtBroc;

use BricEtBroc\InputValues as InputValues;

class InputValueAccessor{
    /**
     *
     * @var InputValues
     */
    public $data_source;
    /**
     *
     * @var string
     */
    public $data_source_target;
    /**
     *
     * @var string
     */
    public $resolved_data_source_target;
    
    public function __construct( $data_source_target ) {
        $this->data_source_target = $data_source_target;
        $this->resolved_data_source_target = NULL;
    }
    
    public function setDataSource( InputValues $data_source ){
        $this->data_source = $data_source;
    }
    
    protected function resolve_jq_expr(){
        if( $this->resolved_data_source_target !== NULL )
            return $this->resolved_data_source_target;
        $jq_pattern = '~^([a-z]*[.#])?([a-z0-9-_]+)(\[{1}[a-z-]+="?([a-z0-9-_]+)"?\])?(:([a-z]+))?$~i';
        $matches    = array();
        preg_match_all($jq_pattern, $this->data_source_target, $matches);
        $resolved   = NULL;
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
        
        if( $resolved === NULL )
            $resolved = $this->data_source_target;
        
        $this->resolved_data_source_target = $resolved;
        return $this->resolved_data_source_target;
    }
    
    public function is_set( $data_source_name=InputValues::DFT_SRCE ){
        $this->resolve_jq_expr();
        $r = $this->data_source->getDataSource($data_source_name);
        return isset( $r[$this->resolved_data_source_target] );
    }
    
    public function read( $data_source_name=InputValues::DFT_SRCE ){
        $this->resolve_jq_expr();
        $r = $this->data_source->getDataSource($data_source_name);
        if( $this->is_set($data_source_name) )
            return $r[$this->resolved_data_source_target];
        throw new Exception("Key ".$this->resolved_data_source_target." doesnt exists in $data_source_name data source");
    }
}