<?php
class ActiveModelController{
    /**
     *
     * @var bool
     */
    protected $is_frozen;
    /**
     *
     * @var ActiveModelBuilder 
     */
    protected $builder;
    /**
     *
     * @var ITableModeler
     */
    protected $table_modeler;
    /**
     *
     * @var ActiveModelQueryBuilder
     */
    protected $model_query_builder;
    /**
     *
     * @var array
     */
    protected $known_models;
    /**
     *
     * @var array
     */
    protected $known_annotations;
    /**
     *
     * @var string 
     */
    protected $__autoload_path;
    
    public function __construct() {
        $this->is_frozen            = false;
        $this->known_models         = array();
        $this->known_annotations    = array();
        $this->builder              = new ActiveModelBuilder();
    }
    
    /**
     *
     * @var ActiveModelController 
     */
    protected static $instance;
    
    /**
     *
     * @return ActiveModelController 
     */
    public static function factory( ){
        return new ActiveModelController();
    }
    
    /**
     *
     * @param ActiveModelController $instance 
     * @return ActiveModelController 
     */
    public static function set_active_instance( ActiveModelController $instance ){
        $previous = self::$instance;
        self::$instance = $instance;
        return $previous;
    }

    /**
     * @static
     * @return ActiveModelController
     */
    public static function get_instance( ){
        if( self::$instance === null )
            self::set_active_instance(self::factory());
        return self::$instance;
    }

    /**
     * @static
     * @param $from_entities_path
     * @return ActiveModelController
     */
    public static function autoload( $from_entities_path ){
        $controller = self::get_instance();
        $controller->__autoload_path = $from_entities_path;
        spl_autoload_register(array($controller,"__autoload"));
        return $controller;
    }
    
    /**
     *
     * @param ActiveModel $model 
     */
    public static function register( ActiveModel $model ){
        $controller = self::get_instance();
        $model_infos = $controller->builder->get_builded_infos(get_class($model));
        $controller->applyToModel($model, $model_infos["model_infos"]);
    }

    /**
     * @static
     * @param $model_class
     * @return mixed
     */
    public static function get_model_infos( $model_class ){
        if( self::is_a_model($model_class) ){
            return self::get_instance()->builder->get_builded_infos( $model_class );
        }
        return false;
    }

    /**
     * @static
     * @param $model_class
     * @return bool
     */
    public static function is_a_model( $model_class ){
        if( isset(self::$known_models_class[$model_class]) )
            return self::$known_models_class[$model_class];
        
        if(class_exists($model_class) === false )
            return false;
        
        $r = new ReflectionClass( $model_class );
        if( $r->name !== $model_class ) return false;
        unset( $r );
        $retour = is_subclass_of($model_class, 'ActiveModel');
        
        self::$known_models_class[$model_class] = $retour;
        
        return $retour;
    }
    protected static $known_models_class = array();
    
    public function __autoload( $class_name ){
        if( class_exists($class_name, false) === false ){
            $file = $this->__autoload_path.'/'.$class_name.".php";
            if( file_exists($file) ){
                include($file);
                if( is_subclass_of( $class_name, "ActiveModel" ) )
                    $this->prepare_model_class($file, $class_name);
                elseif( is_subclass_of( $class_name, "ActiveModelCollection" ) )
                    $this->prepare_model_class($file, substr($class_name, 0,-strlen("Collection")) );
            }
        }
    }
    
    /**
     *
     * @param bool $frozen 
     */
    public function setFrozen( $frozen ){
        $this->is_frozen = (bool)$frozen;
    }
    
    /**
     *
     * @param string $cache_path 
     */
    public function setCachePath( $cache_path ){
        $this->builder->setCachePath( $cache_path );
    }
    
    /**
     *
     * @param bool $use_cache_status
     */
    public function useCache( $use_cache_status ){
        $this->builder->useCache( $use_cache_status );
    }
    
    /**
     *
     * @param ITableModeler $modeler 
     */
    public function setModeler( ITableModeler $modeler ){
        $this->table_modeler = $modeler;
    }
    
    /**
     *
     * @param ActiveModelQueryBuilder $model_query_builder 
     */
    public function setModelQueryBuilder( ActiveModelQueryBuilder $model_query_builder ){
        $this->model_query_builder = $model_query_builder;
    }
    
    /**
     *
     * @return ActiveModelQueryBuilder 
     */
    public function getModelQueryBuilder( ){
        return $this->model_query_builder;
    }

    /**
     * @param $file
     * @param $model_class
     * @return mixed
     */
    public function prepare_model_class( $file, $model_class ){
        $is_fresh       = $this->builder->build( $file, $model_class );
        $model_info     = $this->builder->get_builded_infos($model_class);
        
        if( $this->is_frozen === false ){
            if( !$is_fresh ){
                $this->applyToTable($model_info["table_infos"]);
            }
        }
        
        return $model_info;
    }
    
    /**
     *
     * @param ActiveModel $model 
     */
    public function prepare_model( ActiveModel $model ){
        $model_info = $this->builder->get_builded_infos(get_class($model));
        $this->applyToModel($model, $model_info["model_infos"]);
    }
    
    /**
     *
     * @param ActiveModel $model
     * @param array $model_infos 
     */
    public function applyToModel( ActiveModel $model, $model_infos ){
        foreach( $model_infos["values"] as $prop_name => $default_value ){
            unset($model->{$prop_name});
            $model->_values[$prop_name]         = $default_value;
            if( isset($model_infos["getters"][$prop_name]) )
            $model->hidden_getters[$prop_name]  = $model_infos["getters"][$prop_name];
            if( isset($model_infos["setters"][$prop_name]) )
            $model->hidden_setters[$prop_name]  = $model_infos["setters"][$prop_name];
            // @todo $model->hidden_calls[$prop_name]  = $model_infos["calls"][$prop_name];
        }
        foreach( $model_infos["virtual_values"] as $prop_name => $default_value ){
            unset($model->{$prop_name});
            $model->_virtual_values[$prop_name]         = $default_value;
            if( isset($model_infos["getters"][$prop_name]) )
            $model->hidden_getters[$prop_name]  = $model_infos["getters"][$prop_name];
            if( isset($model_infos["setters"][$prop_name]) )
            $model->hidden_setters[$prop_name]  = $model_infos["setters"][$prop_name];
            // @todo $model->hidden_calls[$prop_name]  = $model_infos["calls"][$prop_name];
        }
        foreach( $model_infos["ro_db_values"] as $prop_name => $default_value ){
            $model->_ro_db_values[$prop_name]         = $default_value;
            if( isset($model_infos["getters"][$prop_name]) )
            $model->hidden_getters[$prop_name]  = $model_infos["getters"][$prop_name];
            if( isset($model_infos["setters"][$prop_name]) )
            $model->hidden_setters[$prop_name]  = $model_infos["setters"][$prop_name];
        }
    }

    /**
     * @param $table_infos
     * @return bool
     */
    public function applyToTable( $table_infos ){
        if( $this->table_modeler === null )
            return false;
        
        /**
         * build table
         */
        $is_new_table   = false;
        $table_name     = $table_infos["table"]["name"];
        if( $this->table_modeler->hasTable($table_name) === false ){
            $this->table_modeler->createTable($table_name, $table_infos["table"]);
            $is_new_table = true;
        }
        
        /**
         * build fields
         */
        foreach( $table_infos["fields"] as $field_name=>$field_infos ){
            if( $this->table_modeler->hasField($table_name, $field_name) ){
            }else{
                $this->table_modeler->createField($table_name, $field_name, $field_infos);
            }
        }
        
        /**
         * build indexs
         */
        foreach( $table_infos["indexs"] as $index_name=>$index_infos ){
            if( $this->table_modeler->hasIndex($table_name, $index_name) ){
            }else{
                $this->table_modeler->createIndex( $table_name, $index_name, $index_infos );
            }
        }
        
        if( $is_new_table )
            $this->table_modeler->clean($table_name);
        
        /**
         * build relations table
         */
        foreach( $table_infos["rel_tables"] as $rel_table_name =>$rel_table_infos ){
            
            $is_new_table = false;
            if( $this->table_modeler->hasTable($rel_table_name) === false ){
                $this->table_modeler->createTable($rel_table_name, $rel_table_infos);
                $is_new_table = true;
            }
            foreach( $rel_table_infos["fields"] as $rel_field_name=>$rel_field_infos ){
                if( $this->table_modeler->hasField($rel_table_name, $rel_field_name) ){
                }else{
                    $this->table_modeler->createField($rel_table_name, $rel_field_name, $rel_field_infos);
                }
            }
            foreach( $rel_table_infos["indexs"] as $rel_index_name=>$rel_index_infos ){
                if( $this->table_modeler->hasIndex($rel_table_name, $rel_index_name) ){
                }else{
                    $this->table_modeler->createIndex( $rel_table_name, $rel_index_name, $rel_index_infos );
                }
            }
            
            if( $is_new_table )
                $this->table_modeler->clean($rel_table_name);
        }
        return true;
    }
}
class Table extends Annotation {
    public $name;
    public $encoding;
    public $engine;
    public $comment;
}
class Column extends Annotation {
    public $autoincrement;
    public $type;
    public $size;
    public $encoding;
    public $default_value;
    public $nullable;
    public $shared_with;
}
class Index extends Annotation {
    // for a same index name, this must be identic
    public $name;
    public $type;
    public $engine;
    // specific to each fields
    public $size;
    public $order;
}
class HasMany extends Annotation {
    public $target;
    public $with;
    public $own;
}
class OwnMany extends Annotation {
    public $target;
}
class HasOne extends Annotation {
    public $target;
}
class OwnOne extends Annotation {
    public $target;
}
