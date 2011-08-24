<?php
namespace BricEtBroc\Config;

class Container extends \ArrayObject{
    
    /**
     *
     * @param string $path
     * @return mixed|null 
     */
    public function getByPath( $path ){
        $path_parts     = explode(".",$path);
        $path_length    = count($path_parts);
        $current_data   = $this;
        foreach( $path_parts as $index => $path_part ){
            if( isset($current_data[$path_part]) === false )
                return NULL;
            elseif( $index+1<$path_length && !is_array($current_data[$path_part]) ){
                return NULL;
            }
            else{
                $current_data = $current_data[$path_part];
            }
        }
        return $current_data;
    }
    
    /**
     *
     * @param string $path
     * @param string $value
     * @return bool 
     */
    public function setByPath( $path, $value ){
        $path_parts     = explode(".",$path);
        $current_data   = &$this;
        foreach( $path_parts as $path_part ){
            if( isset($current_data[$path_part]) === false )
                return false;
            else{
                $current_data = &$current_data[$path_part];
            }
        }
        $current_data = $value;
        return true;
    }
}
