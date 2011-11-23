<?php 

function get_Product_id($this_model, $prop_name) 
                    {return (int)$this_model->_values[$prop_name];}
function get_Product_name($this_model, $prop_name) 
                    {return (string)$this_model->_values[$prop_name];}
function get_Product_position($this_model, $prop_name) 
                    {return (int)$this_model->_values[$prop_name];}
function get_Product_catalog($this_model, $prop_name) {
            $o = $this_model->_virtual_values[$prop_name];
            if( $this_model->_virtual_values[$prop_name] === null ){
                $builder =  Catalog::select()->inner_join("Product.catalog");
                $raw_fields = array (
  'id' => 'catalog_id',
);
                foreach( $raw_fields as $local_fk=>$foreign_pk ){
                    $builder->where("Product.".$foreign_pk, $this_model->$local_fk );
                }
                
                $this_model->_virtual_values[$prop_name] = $builder->find_one();
            }
            return $this_model->_virtual_values[$prop_name];
        }
function get_Product_catalog_id($this_model, $prop_name, $value){
                            }
function get_Product_tomate($this_model, $prop_name) {
            $o = $this_model->_virtual_values[$prop_name];
            if( $this_model->_virtual_values[$prop_name] === null ){
                $builder =  Catalog::select()->inner_join("Product.tomate");
                $raw_fields = array (
  'id' => 'tomate_id',
);
                foreach( $raw_fields as $local_fk=>$foreign_pk ){
                    $builder->where("Product.".$foreign_pk, $this_model->$local_fk );
                }
                
                $this_model->_virtual_values[$prop_name] = $builder->find_one();
            }
            return $this_model->_virtual_values[$prop_name];
        }
function get_Product_tomate_id($this_model, $prop_name, $value){
                            }
function set_Product_id($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = (int)substr((string)$value,0, 11);}
function set_Product_name($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = substr((string)$value,0, 150);}
function set_Product_position($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = (int)substr((string)$value,0, 11);}
function set_Product_catalog($this_model, $prop_name, $value) {
            $foreign_object_pks = array (
);
            foreach( $foreign_object_pks as $local_fk=>$foreign_pk ){
                $this->_values[ $local_fk ] = $value->$foreign_pk;
            }
            $this_model->_virtual_values[$prop_name] = $value;
        }
function set_Product_catalog_id($this_model, $prop_name, $value){
                            }
function set_Product_tomate($this_model, $prop_name, $value) {
            $foreign_object_pks = array (
);
            foreach( $foreign_object_pks as $local_fk=>$foreign_pk ){
                $this->_values[ $local_fk ] = $value->$foreign_pk;
            }
            $this_model->_virtual_values[$prop_name] = $value;
        }
function set_Product_tomate_id($this_model, $prop_name, $value){
                            }

return array (
  'create_time' => 1321978180,
  'filemtime' => 1321845138,
  'model_infos' => 
  array (
    'getters' => 
    array (
      'id' => 'get_Product_id',
      'name' => 'get_Product_name',
      'position' => 'get_Product_position',
      'catalog' => 'get_Product_catalog',
      'catalog_id' => 'get_Product_catalog_id',
      'tomate' => 'get_Product_tomate',
      'tomate_id' => 'get_Product_tomate_id',
    ),
    'setters' => 
    array (
      'id' => 'set_Product_id',
      'name' => 'set_Product_name',
      'position' => 'set_Product_position',
      'catalog' => 'set_Product_catalog',
      'catalog_id' => 'set_Product_catalog_id',
      'tomate' => 'set_Product_tomate',
      'tomate_id' => 'set_Product_tomate_id',
    ),
    'values' => 
    array (
      'id' => NULL,
      'name' => 'null',
      'position' => NULL,
    ),
    'ro_db_values' => 
    array (
      'catalog_id' => NULL,
      'tomate_id' => NULL,
    ),
    'calls' => 
    array (
    ),
    'virtual_values' => 
    array (
      'colors' => NULL,
      'catalog' => NULL,
      'tomate' => NULL,
    ),
    'virutal_prop_infos' => 
    array (
      'colors' => 
      array (
        'type' => 'has_many_to_many',
        'class' => 'Color',
        'own' => false,
        'rel_tbl_name' => 'colors_products',
        'rel_fields' => 
        array (
          'product_id' => 'id',
        ),
        'tgt_tbl_name' => 'color',
        'tgt_fields' => 
        array (
          'color_id' => 'id',
        ),
      ),
      'catalog' => 
      array (
        'type' => 'has_one',
        'class' => 'Catalog',
        'own' => false,
        'fields' => 
        array (
          'id' => 'catalog_id',
        ),
      ),
      'tomate' => 
      array (
        'type' => 'has_one',
        'class' => 'Catalog',
        'own' => false,
        'fields' => 
        array (
          'id' => 'tomate_id',
        ),
      ),
    ),
    'rel_class_infos' => 
    array (
      'Color' => 
      array (
        'colors' => 
        array (
          'type' => 'many_to_many',
          'class' => 'Color',
          'own' => false,
          'fields' => 
          array (
          ),
        ),
      ),
      'Catalog' => 
      array (
        'catalog' => 
        array (
          'type' => 'has_one',
          'class' => 'Catalog',
          'own' => false,
          'fields' => 
          array (
            'id' => 'catalog_id',
          ),
        ),
        'tomate' => 
        array (
          'type' => 'has_one',
          'class' => 'Catalog',
          'own' => false,
          'fields' => 
          array (
            'id' => 'tomate_id',
          ),
        ),
      ),
    ),
  ),
  'table_infos' => 
  array (
    'table' => 
    array (
      'name' => 'product',
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
        'encoding' => 'utf8_unicode_ci',
        'default_value' => 'null',
        'nullable' => 'true',
      ),
      'position' => 
      array (
        'name' => 'position',
        'type' => 'int',
        'size' => '11',
        'encoding' => 'utf8_unicode_ci',
      ),
      'catalog_id' => 
      array (
        'name' => 'catalog_id',
        'type' => 'int',
        'size' => '11',
        'encoding' => 'utf8_unicode_ci',
      ),
      'tomate_id' => 
      array (
        'name' => 'tomate_id',
        'type' => 'int',
        'size' => '11',
        'encoding' => 'utf8_unicode_ci',
      ),
    ),
    'indexs' => 
    array (
      '' => 
      array (
        'fields' => 
        array (
          'id' => 
          array (
          ),
        ),
        'name' => NULL,
        'type' => 'pk',
      ),
    ),
    'rel_tables' => 
    array (
      'colors_products' => 
      array (
        'fields' => 
        array (
          'color_id' => 
          array (
            'name' => 'color_id',
            'type' => 'int',
            'size' => '11',
          ),
          'product_id' => 
          array (
            'name' => 'product_id',
            'type' => 'int',
            'size' => '11',
          ),
        ),
        'shared_fields' => 
        array (
        ),
        'indexs' => 
        array (
          'automatic_pk' => 
          array (
            'name' => 'automatic_pk',
            'type' => 'PK',
            'fields' => 
            array (
              'color_id' => 
              array (
                'size' => '11',
              ),
              'product_id' => 
              array (
                'size' => '11',
              ),
            ),
          ),
        ),
      ),
    ),
  ),
);