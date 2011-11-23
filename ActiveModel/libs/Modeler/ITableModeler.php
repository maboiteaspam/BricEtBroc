<?php
/**
 *
 * @author clement
 */
interface ITableModeler {
    
    function setContainerName( $name );
    
    function hasTable( $name );
    function hasField( $raw_table_name, $field_name );
    function hasIndex( $raw_table_name, $index_name );
    
    function createTable( $raw_table_name, $options=array() );
    function createField( $raw_table_name, $field_name, $options=array() );
    function createIndex( $raw_table_name, $index_name, $options=array() );
    
    function removeTable( $raw_table_name );
    function removeField( $raw_table_name, $field_name  );
    
    function clean( $raw_table_name );
}

