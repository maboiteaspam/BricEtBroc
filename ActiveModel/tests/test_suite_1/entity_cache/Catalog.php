<?php 

function get_Catalog_id($this_model, $prop_name) 
                    {return (int)$this_model->_values[$prop_name];}
function get_Catalog_name($this_model, $prop_name) 
                    {return (string)$this_model->_values[$prop_name];}
function get_Catalog_description($this_model, $prop_name) 
                    {return (string)$this_model->_values[$prop_name];}
function get_Catalog_code($this_model, $prop_name) 
                    {return (string)$this_model->_values[$prop_name];}
function get_Catalog_dumb_entity($this_model, $prop_name) {
            $o = $this_model->_virtual_values[$prop_name];
            if( $this_model->_virtual_values[$prop_name] === null ){
                $builder =  DumbEntity::select()->inner_join("Catalog.dumb_entity");
                $raw_fields = array (
  'id' => 'dumb_entity_id',
);
                foreach( $raw_fields as $local_fk=>$foreign_pk ){
                    $builder->where("Catalog.".$foreign_pk, $this_model->$local_fk );
                }
                
                $this_model->_virtual_values[$prop_name] = $builder->find_one();
            }
            return $this_model->_virtual_values[$prop_name];
        }
function get_Catalog_dumb_entity_id($this_model, $prop_name, $value){
                            }
function set_Catalog_id($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = (int)substr((string)$value,0, 11);}
function set_Catalog_name($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = substr((string)$value,0, 150);}
function set_Catalog_description($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = (string)$value;}
function set_Catalog_code($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = (string)$value;}
function set_Catalog_dumb_entity($this_model, $prop_name, $value) {
            $foreign_object_pks = array (
);
            foreach( $foreign_object_pks as $local_fk=>$foreign_pk ){
                $this->_values[ $local_fk ] = $value->$foreign_pk;
            }
            $this_model->_virtual_values[$prop_name] = $value;
        }
function set_Catalog_dumb_entity_id($this_model, $prop_name, $value){
                            }

return array (
  'create_time' => 1321978180,
  'filemtime' => 1321847583,
  'model_infos' => 
  array (
    'getters' => 
    array (
      'id' => 'get_Catalog_id',
      'name' => 'get_Catalog_name',
      'description' => 'get_Catalog_description',
      'code' => 'get_Catalog_code',
      'dumb_entity' => 'get_Catalog_dumb_entity',
      'dumb_entity_id' => 'get_Catalog_dumb_entity_id',
    ),
    'setters' => 
    array (
      'id' => 'set_Catalog_id',
      'name' => 'set_Catalog_name',
      'description' => 'set_Catalog_description',
      'code' => 'set_Catalog_code',
      'dumb_entity' => 'set_Catalog_dumb_entity',
      'dumb_entity_id' => 'set_Catalog_dumb_entity_id',
    ),
    'values' => 
    array (
      'id' => NULL,
      'name' => NULL,
      'description' => 'null',
      'code' => NULL,
    ),
    'ro_db_values' => 
    array (
      'dumb_entity_id' => NULL,
    ),
    'calls' => 
    array (
    ),
    'virtual_values' => 
    array (
      'products' => NULL,
      'dumb_entity' => NULL,
    ),
    'virutal_prop_infos' => 
    array (
      'products' => 
      array (
        'type' => 'has_many',
        'class' => 'Product',
        'own' => true,
        'target' => 
        array (
          'model' => 'Product',
          'property' => 'catalog',
        ),
        'fields' => 
        array (
          'catalog_id' => 'id',
        ),
      ),
      'dumb_entity' => 
      array (
        'type' => 'has_one',
        'class' => 'DumbEntity',
        'own' => false,
        'fields' => 
        array (
          'id' => 'dumb_entity_id',
        ),
      ),
    ),
    'rel_class_infos' => 
    array (
      'Product' => 
      array (
        'products' => 
        array (
          'type' => 'has_many',
          'class' => 'Product',
          'own' => true,
          'target' => 
          array (
            'model' => 'Product',
            'property' => 'catalog',
          ),
          'fields' => 
          array (
            'catalog_id' => 'id',
          ),
        ),
      ),
      'DumbEntity' => 
      array (
        'dumb_entity' => 
        array (
          'type' => 'has_one',
          'class' => 'DumbEntity',
          'own' => false,
          'fields' => 
          array (
            'id' => 'dumb_entity_id',
          ),
        ),
      ),
    ),
  ),
  'table_infos' => 
  array (
    'table' => 
    array (
      'name' => 'catalog',
      'engine' => NULL,
      'encoding' => 'utf8_unicode_ci',
      'comment' => NULL,
    ),
    'fields' => 
    array (
      'id' => 
      array (
        'name' => 'id',
        'type' => 'int',
        'size' => '11',
        'encoding' => 'utf8_unicode_ci',
      ),
      'name' => 
      array (
        'name' => 'name',
        'type' => 'text',
        'size' => '150',
        'encoding' => 'utf8_unicode_ci',
      ),
      'description' => 
      array (
        'name' => 'description',
        'type' => 'text',
        'encoding' => 'utf8_unicode_ci',
        'default_value' => 'null',
        'nullable' => 'true',
      ),
      'code' => 
      array (
        'name' => 'code',
        'type' => 'text',
        'encoding' => 'utf8_unicode_ci',
        'nullable' => 'false',
      ),
      'dumb_entity_id' => 
      array (
        'name' => 'dumb_entity_id',
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
          'description' => 
          array (
            'size' => '15',
            'order' => 'ASC',
          ),
        ),
        'name' => 'my_index',
        'type' => 'index',
        'engine' => 'BTREE',
      ),
      'my_unique_index' => 
      array (
        'fields' => 
        array (
          'code' => 
          array (
            'size' => '25',
          ),
        ),
        'name' => 'my_unique_index',
        'type' => 'unique',
      ),
    ),
    'rel_tables' => 
    array (
    ),
  ),
);