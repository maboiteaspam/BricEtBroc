<?php 

function get_Color_id($this_model, $prop_name) 
                    {return (int)$this_model->_values[$prop_name];}
function get_Color_name($this_model, $prop_name) 
                    {return (string)$this_model->_values[$prop_name];}
function get_Color_color_position($this_model, $prop_name) 
                    {return (int)$this_model->_values[$prop_name];}
function set_Color_id($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = (int)substr((string)$value,0, 11);}
function set_Color_name($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = substr((string)$value,0, 150);}
function set_Color_color_position($this_model, $prop_name) 
                        {$this_model->_values[$prop_name] = (int)substr((string)$value,0, 11);}

return array (
  'create_time' => 1321978180,
  'filemtime' => 1321841484,
  'model_infos' => 
  array (
    'getters' => 
    array (
      'id' => 'get_Color_id',
      'name' => 'get_Color_name',
      'color_position' => 'get_Color_color_position',
    ),
    'setters' => 
    array (
      'id' => 'set_Color_id',
      'name' => 'set_Color_name',
      'color_position' => 'set_Color_color_position',
    ),
    'values' => 
    array (
      'id' => NULL,
      'name' => NULL,
      'color_position' => '0',
    ),
    'ro_db_values' => 
    array (
    ),
    'calls' => 
    array (
    ),
    'virtual_values' => 
    array (
      'products' => NULL,
    ),
    'virutal_prop_infos' => 
    array (
      'products' => 
      array (
        'type' => 'has_many_to_many',
        'class' => 'Product',
        'own' => false,
        'rel_tbl_name' => 'colors_products',
        'rel_fields' => 
        array (
          'color_id' => 'id',
        ),
        'tgt_tbl_name' => 'product',
        'tgt_fields' => 
        array (
          'product_id' => 'id',
        ),
      ),
    ),
    'rel_class_infos' => 
    array (
      'Product' => 
      array (
        'products' => 
        array (
          'type' => 'many_to_many',
          'class' => 'Product',
          'own' => false,
          'fields' => 
          array (
          ),
        ),
      ),
    ),
  ),
  'table_infos' => 
  array (
    'table' => 
    array (
      'name' => 'color',
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
          'product_id' => 
          array (
            'name' => 'product_id',
            'type' => 'int',
            'size' => '11',
          ),
          'color_id' => 
          array (
            'name' => 'color_id',
            'type' => 'int',
            'size' => '11',
          ),
          'color_position' => 
          array (
            'name' => 'color_position',
            'type' => 'int',
            'size' => '11',
            'encoding' => 'utf8_unicode_ci',
            'default_value' => '0',
          ),
        ),
        'shared_fields' => 
        array (
          'color_position' => NULL,
        ),
        'indexs' => 
        array (
          'automatic_pk' => 
          array (
            'name' => 'automatic_pk',
            'type' => 'PK',
            'fields' => 
            array (
              'product_id' => 
              array (
                'size' => '11',
              ),
              'color_id' => 
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