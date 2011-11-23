<?php 

function get_DumbEntity_id($this_model, $prop_name) 
                    {return (int)$this_model->_values[$prop_name];}
function get_DumbEntity_name($this_model, $prop_name) 
                    {return (string)$this_model->_values[$prop_name];}
function get_DumbEntity_dumb_catalog($this_model, $prop_name) {
            $o = $this_model->_virtual_values[$prop_name];
            if( $this_model->_virtual_values[$prop_name] === null ){
                $builder =  Catalog::select()->inner_join("DumbEntity.dumb_catalog");
                $raw_fields = array (
  'id' => 'dumb_catalog_id',
);
                foreach( $raw_fields as $local_fk=>$foreign_pk ){
                    $builder->where("DumbEntity.".$foreign_pk, $this_model->$local_fk );
                }
                
                $this_model->_virtual_values[$prop_name] = $builder->find_one();
            }
            return $this_model->_virtual_values[$prop_name];
        }
function get_DumbEntity_dumb_catalog_id($this_model, $prop_name, $value){
                            }
function set_DumbEntity_id($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = (int)substr((string)$value,0, 11);}
function set_DumbEntity_name($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = substr((string)$value,0, 150);}
function set_DumbEntity_dumb_catalog($this_model, $prop_name, $value) {
            $foreign_object_pks = array (
);
            foreach( $foreign_object_pks as $local_fk=>$foreign_pk ){
                $this->_values[ $local_fk ] = $value->$foreign_pk;
            }
            $this_model->_virtual_values[$prop_name] = $value;
        }
function set_DumbEntity_dumb_catalog_id($this_model, $prop_name, $value){
                            }

return array (
  'create_time' => 1321978180,
  'filemtime' => 1321847578,
  'model_infos' => 
  array (
    'getters' => 
    array (
      'id' => 'get_DumbEntity_id',
      'name' => 'get_DumbEntity_name',
      'dumb_catalog' => 'get_DumbEntity_dumb_catalog',
      'dumb_catalog_id' => 'get_DumbEntity_dumb_catalog_id',
    ),
    'setters' => 
    array (
      'id' => 'set_DumbEntity_id',
      'name' => 'set_DumbEntity_name',
      'dumb_catalog' => 'set_DumbEntity_dumb_catalog',
      'dumb_catalog_id' => 'set_DumbEntity_dumb_catalog_id',
    ),
    'values' => 
    array (
      'id' => NULL,
      'name' => NULL,
    ),
    'ro_db_values' => 
    array (
      'dumb_catalog_id' => NULL,
    ),
    'calls' => 
    array (
    ),
    'virtual_values' => 
    array (
      'dumb_catalog' => NULL,
    ),
    'virutal_prop_infos' => 
    array (
      'dumb_catalog' => 
      array (
        'type' => 'has_one',
        'class' => 'Catalog',
        'own' => false,
        'fields' => 
        array (
          'id' => 'dumb_catalog_id',
        ),
      ),
    ),
    'rel_class_infos' => 
    array (
      'Catalog' => 
      array (
        'dumb_catalog' => 
        array (
          'type' => 'has_one',
          'class' => 'Catalog',
          'own' => false,
          'fields' => 
          array (
            'id' => 'dumb_catalog_id',
          ),
        ),
      ),
    ),
  ),
  'table_infos' => 
  array (
    'table' => 
    array (
      'name' => 'dumbentity',
      'engine' => NULL,
      'encoding' => NULL,
      'comment' => NULL,
    ),
    'fields' => 
    array (
      'id' => 
      array (
        'name' => 'id',
        'type' => 'int',
        'size' => '11',
      ),
      'name' => 
      array (
        'name' => 'name',
        'type' => 'text',
        'size' => '150',
      ),
      'dumb_catalog_id' => 
      array (
        'name' => 'dumb_catalog_id',
        'type' => 'int',
        'size' => '11',
        'encoding' => 'utf8_unicode_ci',
      ),
    ),
    'indexs' => 
    array (
      'my_pk' => 
      array (
        'fields' => 
        array (
          'id' => 
          array (
          ),
        ),
        'name' => 'my_pk',
        'type' => 'pk',
      ),
      'my_index' => 
      array (
        'fields' => 
        array (
          'name' => 
          array (
          ),
        ),
        'name' => 'my_index',
        'type' => 'index',
      ),
    ),
    'rel_tables' => 
    array (
    ),
  ),
);