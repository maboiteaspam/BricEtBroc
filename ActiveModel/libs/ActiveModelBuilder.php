<?php

class ActiveModelBuilder{
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
     * @var bool
     */
    protected $use_cache;
    /**
     *
     * @var string
     */
    protected $cache_path;

    public function __construct() {
        $this->known_models         = array();
        $this->known_annotations    = array();
        $this->use_cache            = true;
        $this->cache_path           = "";
    }

    /**
     *
     * @param bool $use_cache_status
     */
    public function useCache( $use_cache_status ){
        $this->use_cache    = (bool)$use_cache_status;
    }

    /**
     *
     * @param string $cache_path
     */
    public function setCachePath( $cache_path ){
        $this->cache_path   = (string)$cache_path;
        if( $this->cache_path!=="" && is_dir($this->cache_path) === false ){
            mkdir($this->cache_path,0,true);
        }
    }

    /**
     *
     * @param string $model_name
     * @return ReflectionAnnotatedClass
     */
    public function readActiveModel( $model_name ){
        if( isset($this->known_annotations[$model_name]) === false )
            $this->known_annotations[$model_name] = new ReflectionAnnotatedClass($model_name);
        return $this->known_annotations[$model_name];
    }

    public function get_pending_models(  ){
        return array_diff(  array_keys($this->known_annotations),
                            array_keys($this->known_models));
    }

    public function is_builded( $model_name ){
        return isset($this->known_models[$model_name]);
    }

    protected function cache_file_name( $file, $for_debug=false ){
        if( $this->cache_path === "" ){
            $retour = dirname($file)."/".($for_debug?"debug.":".").  basename($file);
        }else{
            $retour = $this->cache_path."/".($for_debug?"debug.":""). basename($file);
        }
        return $retour;
    }

    public function build( $file, $model_name ){
        $is_fresh = false;
        if( $this->use_cache ){
            $is_fresh = $this->is_fresh( $file, $model_name );
        }

        if( $is_fresh === false ){
            $model_annots = $this->readActiveModel($model_name);
            $this->known_models[$model_name] = array(
                "create_time"   => time(),
                "filemtime"     => filemtime($file),
                "model_infos"   => $this->buildActiveModelInfos($model_name, $model_annots),
                "table_infos"   => $this->buildActiveTableInfos($model_name, $model_annots),
            );
            if( $this->use_cache )
                $this->cache_model_infos( $file, $model_name );
        }else{
            $this->load_from_cache( $file, $model_name );
        }
        return $is_fresh;
    }

    public function cache_model_infos( $file, $model_name ){
        // to make sure we write by copy....
        $to_cache = new ArrayObject($this->known_models[$model_name]);
        $to_cache = $to_cache->getArrayCopy();
        // to make sure we write by copy....

        $cache_file_name = $this->cache_file_name($file);

        foreach( $to_cache["model_infos"]["getters"] as $p => $g ){
            $to_cache["model_infos"]["getters"][$p] = substr($g,9,strpos($g, "(")-9);
        }
        foreach( $to_cache["model_infos"]["setters"] as $p => $g ){
            $to_cache["model_infos"]["setters"][$p] = substr($g,9,strpos($g, "(")-9);
        }
        file_put_contents($cache_file_name, "<?php \n");
        file_put_contents($cache_file_name, "\n".implode("\n", $this->known_models[$model_name]["model_infos"]["getters"]) , FILE_APPEND);
        file_put_contents($cache_file_name, "\n".implode("\n", $this->known_models[$model_name]["model_infos"]["setters"]) , FILE_APPEND);
        file_put_contents($cache_file_name, "\n\nreturn ".var_export($to_cache, true).";", FILE_APPEND);
        chmod($cache_file_name, 0777);
    }

    public function is_fresh( $file, $model_class ){
        $cache_file_name = $this->cache_file_name($file);
        if(file_exists($cache_file_name) ){
            $cached_infos = $this->load_from_cache($file, $model_class);
            return $cached_infos["filemtime"] === filemtime($file);
        }
        return false;
    }

    public function load_from_cache( $file, $model_name ){
        if( isset($this->known_models[$model_name]) )
            return $this->known_models[$model_name];
        $cache_file_name    = $this->cache_file_name($file);
        $cache_info        = include($cache_file_name);
        $this->known_models[$model_name] = $cache_info;
        return $this->known_models[$model_name];
    }

    public function get_builded_infos( $model_name ){
        return $this->known_models[$model_name];
    }

    /**
     *
     * @param string $model_name
     * @param ReflectionAnnotatedClass $model_annots
     * @return array
     */
    public function buildActiveModelInfos($model_name, ReflectionAnnotatedClass $model_annots ){
        $retour     = array(
            "getters"               => array(),
            "setters"               => array(),
            "values"                => array(),
            "ro_db_values"          => array(),
            "calls"                 => array(),
            "virtual_values"        => array(),
            "virutal_prop_infos"    => array(),
            "rel_class_infos"       => array(),
        );
        /**
         * go throught all propreties, build getter and setter according to their infos
         */
        foreach( $model_annots->getProperties() as $prop ){
            /* @var ReflectionAnnotatedProperty $prop */
            $prop_name = $prop->name;
            foreach( $prop->getAllAnnotations("Column") as $annot ){
                $getter_info = $this->makeSimpleAccessor($model_name, $prop->name, $annot);
                $retour["getters"][$prop_name]      = $getter_info["getter"];
                $retour["setters"][$prop_name]      = $getter_info["setter"];
                $retour["values"][$prop_name]       = $getter_info["default_value"];
            }

            foreach( $prop->getAllAnnotations("HasMany") as $annot ){
                $target_info           = explode('.', $annot->target);
                $target_class           = $target_info[0];
                $target_prop            = $target_info[1];
                $on_target_prop         = $annot->target;
                //$target_class_annots    = $this->readActiveModel($target_class);

                if( $this->isManyToMany($model_name, $prop->name, $target_info[0], $target_info[1]) ){
                    $info = $this->makeManyToManyAccessor($prop_name, $model_name, $target_info[0], $target_info[1]);
                }else{
                    $info = $this->makeOneToManyAccessor($model_name, $target_class, $target_prop, $on_target_prop);
                }

                $retour["virutal_prop_infos"][$prop_name] = $info["virutal_prop_infos"];
                if( isset($retour["rel_class_infos"][$target_class]) == false )
                    $retour["rel_class_infos"][$target_class] = array();
                $retour["rel_class_infos"][$target_class][$prop_name] = $info["rel_class_infos"];

                $retour["virtual_values"][$prop_name]   = null;
            }

            foreach( $prop->getAllAnnotations("OwnMany") as $annot ){
                $target_info           = explode('.',$annot->target);
                $target_class           = $target_info[0];
                $target_prop            = $target_info[1];
                $on_target_prop         = $annot->target;
                //$target_class_annots    = $this->readActiveModel($target_class);

                if( $this->isManyToMany($model_name, $prop->name, $target_info[0], $target_info[1]) ){
                    $info = $this->makeManyToManyAccessor($prop_name, $model_name, $target_info[0], $target_info[1]);
                }else{
                    $info = $this->makeOneToManyAccessor($model_name, $target_class, $target_prop, $on_target_prop, true);
                }

                $retour["virutal_prop_infos"][$prop_name] = $info["virutal_prop_infos"];
                if( isset($retour["rel_class_infos"][$target_class]) == false )
                    $retour["rel_class_infos"][$target_class] = array();
                $retour["rel_class_infos"][$target_class][$prop_name] = $info["rel_class_infos"];

                $retour["virtual_values"][$prop_name]   = null;
            }

            foreach( $prop->getAllAnnotations("HasOne") as $annot ){
                $target_class = $annot->target;
                $info = $this->makeOneToOneAccessor($prop_name, $model_name, $target_class);
                $retour["virtual_values"][$prop_name]   = null;

                $retour["getters"][$prop_name]      = $info["getter"];
                $retour["setters"][$prop_name]      = $info["setter"];

                /**
                 * To set getters / setters for a foreign key
                 */
                foreach( $info["virutal_prop_infos"]["fields"] as $foreign_field=>$local_field){
                    $foreign_annotation  = $this->getColumnAnnot($target_class, $foreign_field);
                    $getter_info = $this->makeForeignKeyAccessor($model_name, $prop->name, $local_field, $foreign_field, $foreign_annotation);
                    $retour["getters"][$local_field]      = $getter_info["getter"];
                    $retour["setters"][$local_field]      = $getter_info["setter"];
                    $retour["ro_db_values"][$local_field] = $getter_info["default_value"];
                }

                /**
                 * To set description infos used for JIT model manipulation
                 * (for example active model query builder)
                 */
                $retour["virutal_prop_infos"][$prop_name] = $info["virutal_prop_infos"];
                if( isset($retour["rel_class_infos"][$target_class]) == false )
                    $retour["rel_class_infos"][$target_class] = array();
                $retour["rel_class_infos"][$target_class][$prop_name] = $info["rel_class_infos"];
            }

            foreach( $prop->getAllAnnotations("OwnOne") as $annot ){
                $target_class = $annot->target;
                $info = $this->makeOneToOneAccessor($prop_name, $model_name, $annot->target, true);
                $retour["virtual_values"][$prop_name]   = null;

                $retour["getters"][$prop_name]      = $info["getter"];
                $retour["setters"][$prop_name]      = $info["setter"];

                /**
                 * To set getters / setters for a foreign key
                 */
                foreach( $info["virutal_prop_infos"]["fields"] as $foreign_field=>$local_field){
                    $foreign_annotation  = $this->getColumnAnnot($target_class, $foreign_field);
                    $getter_info = $this->makeForeignKeyAccessor($model_name, $prop->name, $local_field, $foreign_field, $foreign_annotation);
                    $retour["getters"][$local_field]      = $getter_info["getter"];
                    $retour["setters"][$local_field]      = $getter_info["setter"];
                    $retour["ro_db_values"][$local_field] = $getter_info["default_value"];
                }

                /**
                 * To set description infos used for JIT model manipulation
                 * (for example active model query builder)
                 */
                $retour["virutal_prop_infos"][$prop_name] = $info["virutal_prop_infos"];
                if( isset($retour["rel_class_infos"][$target_class]) == false )
                    $retour["rel_class_infos"][$target_class] = array();
                $retour["rel_class_infos"][$target_class][$prop_name] = $info["rel_class_infos"];
            }
        }

        return $retour;

    }

    /**
     *
     * @param string $model_name
     * @param ReflectionAnnotatedClass $model_annots
     * @return array
     */
    public function buildActiveTableInfos( $model_name, ReflectionAnnotatedClass $model_annots ){
        $retour = array(
            "table"         =>array(),
            "fields"        =>array(),
            "indexs"        =>array(),
            "rel_tables"    =>array(),
        );

        /**
         * build the table
         */
        $table_name     = array_pop(explode("\\", $model_name));
        $table_encoding = null;
        $table_engine   = null;
        $table_comment  = null;

        $annot      = $model_annots->getAnnotation('Table');
        if( $annot !== false ){
            if( $annot->engine !== null )       $table_engine = $annot->engine;
            if( $annot->encoding !== null )     $table_encoding = $annot->encoding;
            if( $annot->comment !== null )      $table_comment = $annot->comment;
        }

        $retour["table"]["name"]        = $this->getTableNameFromModelName($model_name);
        $retour["table"]["engine"]      = $table_engine;
        $retour["table"]["encoding"]    = $table_encoding;
        $retour["table"]["comment"]     = $table_comment;

        /**
         * build the fields
         */
        foreach( $model_annots->getProperties() as $prop ){
            // build colums according to their infos
            foreach( $prop->getAllAnnotations("Column") as $annot ){
                if( $annot->shared_with === NULL ){
                    $field_name     = $prop->name;
                    $field_infos    = $this->buildFieldInfos( $field_name, $annot, $table_encoding );
                    $retour["fields"][$field_name]    = $field_infos;
                }
            }


            // build indexs according to their infos
            foreach( $prop->getAllAnnotations("Index") as $annot ){

                $field_name = $prop->name;
                $index_name = $annot->name;

                if( isset($retour["indexs"][$index_name]) == false )
                    $retour["indexs"][$index_name] = array();
                if( isset($retour["indexs"][$index_name]["fields"]) == false )
                    $retour["indexs"][$index_name]["fields"] = array();
                if( isset($retour["indexs"][$index_name]["fields"][$field_name]) == false )
                    $retour["indexs"][$index_name]["fields"][$field_name] = array();

                $retour["indexs"][$index_name]["name"] = $index_name;
                if( $annot->type !== null )
                    $retour["indexs"][$index_name]["type"] = $annot->type;
                if( $annot->engine !== null )
                    $retour["indexs"][$index_name]["engine"] = $annot->engine;


                if( $annot->size !== null )
                    $retour["indexs"][$index_name]["fields"][$field_name]["size"] = $annot->size;
                if( $annot->order !== null )
                    $retour["indexs"][$index_name]["fields"][$field_name]["order"] = $annot->order;
            }

            // build relations schip columns
            foreach( $prop->getAllAnnotations("HasMany") as $annot ){
                $target_infos           = explode('.',$annot->target);
                $target_class           = $target_infos[0];
                $with_prop              = $annot->with;
                $target_class_annots    = $this->readActiveModel($target_class);

                $target_table_name = $this->getTableNameFromModelName($target_class);

                if( $this->isManyToMany($model_name, $prop->name, $target_infos[0], $target_infos[1]) ){
                    $rel_table_name = $this->makeRelTableName($prop->name, $target_infos[1]);
                    $rel_fields     = array();
                    $rel_pk         = array();
                    // find the pk to make the new table
                    // from foreign class
                    $foreign_fields = $this->makeForeignFields($target_class, $target_class, $table_encoding);
                    foreach( $foreign_fields as $foreign_field_name => $foreign_field_infos ){
                        $rel_fields[$foreign_field_name]    = $foreign_field_infos;
                        $rel_pk[$foreign_field_name] = array();
                        if( isset($foreign_field_infos["size"]) )
                            $rel_pk[$foreign_field_name]["size"] = $foreign_field_infos["size"];
                    }
                    // from foreign class
                    // from current class
                    $foreign_fields = $this->makeForeignFields($model_name, $model_name, $table_encoding);
                    foreach( $foreign_fields as $foreign_field_name => $foreign_field_infos ){
                        $rel_fields[$foreign_field_name]    = $foreign_field_infos;
                        $rel_pk[$foreign_field_name] = array();
                        if( isset($foreign_field_infos["size"]) )
                            $rel_pk[$foreign_field_name]["size"] = $foreign_field_infos["size"];
                    }
                    // from current class
                    // find the pk to make the new table

                    // find the shared properties to export
                    $shared_fields = array();
                    foreach( $model_annots->getProperties() as $prop_ ){
                        foreach( $prop_->getAllAnnotations("Column") as $annot_ ){
                            if( $annot_->shared_with === $target_infos[0] ){
                                $rel_fields[$prop_->name]       = $this->buildFieldInfos( $prop_->name, $annot_, $table_encoding );
                                $shared_fields[$prop_->name]    = null;
                            }
                        }
                    }
                    // find the shared properties to export

                    $retour["rel_tables"][$rel_table_name] = array(
                        "fields"        =>$rel_fields,
                        "shared_fields" =>$shared_fields,
                        "indexs" => array(
                            "automatic_pk"=>array(
                                "name"      =>"automatic_pk",
                                "type"      =>"PK",
                                "fields"    =>$rel_pk,
                            )
                        )
                    );
                }
            }

            foreach( $prop->getAllAnnotations("OwnMany") as $annot ){
                $target_infos = explode('.',$annot->target);
                $target_class = $target_infos[0];
                $target_class_annots    = $this->readActiveModel($target_class);

            }

            foreach( $prop->getAllAnnotations("HasOne") as $annot ){
                $target_class           = $annot->target;
                $target_class_annots    = $this->readActiveModel($target_class);

                // find the pk
                $foreign_fields = $this->makeForeignFields($target_class, $prop->name, $table_encoding);
                foreach( $foreign_fields as $foreign_field_name => $foreign_field_infos ){
                    $retour["fields"][$foreign_field_name]    = $foreign_field_infos;
                }
                // find the pk
            }

            foreach( $prop->getAllAnnotations("OwnOne") as $annot ){
                $target_class           = $annot->target;
                $target_class_annots    = $this->readActiveModel($target_class);

                // find the pk
                $foreign_fields = $this->makeForeignFields($target_class, $prop->name, $table_encoding);
                foreach( $foreign_fields as $foreign_field_name => $foreign_field_infos ){
                    $retour["fields"][$foreign_field_name]    = $foreign_field_infos;
                }
                // find the pk
            }
        }

        return $retour;
    }

    protected function getTableNameFromModelName($model_name){
        $model_annots     = $this->readActiveModel($model_name);
        $model_name       = array_pop(explode("\\", $model_name));
        $table_name       = strtolower($model_name);
        $table_annot      = $model_annots->getAnnotation('Table');
        if( $table_annot !== false ){
            if( $table_annot->name !== null )
                $table_name = $table_annot->name;
        }

        return $table_name;
    }

    protected function getColumnAnnot($model_name, $prop_name){
        $model_annots     = $this->readActiveModel($model_name);
        foreach( $model_annots->getProperties() as $prop ){
            if( $prop_name == $prop->name ){
                return $prop->getAnnotation("Column");
            }
        }
        return null;
    }

    protected function makeRelTableName($table_left, $table_right){
        $_tables        = array(strtolower($table_left), strtolower($table_right) );
        sort($_tables);
        return $_tables[0]."_".$_tables[1];
    }

    /**
     *
     * @param type $field_name
     * @param Column $annot
     * @param type $table_encoding
     * @return type
     */
    protected function buildFieldInfos( $field_name, Column $annot, $table_encoding=null ){
        $retour = array();
        $retour["name"] = $field_name;
        if( $annot->type !== null )
            $retour["type"] = $annot->type;
        if( $annot->size !== null )
            $retour["size"] = $annot->size;
        if( $annot->encoding !== null )
            $retour["encoding"] = $annot->encoding;
        elseif( $table_encoding !== null )
            $retour["encoding"] = $table_encoding;
        if( $annot->default_value !== null )
            $retour["default_value"] = $annot->default_value;
        if( $annot->nullable !== null )
            $retour["nullable"] = $annot->nullable;

        return $retour;
    }

    /**
     *
     * @param string $target_class
     * @return array
     */
    protected function makeForeignFields( $target_class, $prop_name_as, $default_table_encoding=null ){
        $retour = array();
        $target_class_annots = $this->readActiveModel($target_class);
        foreach( $target_class_annots->getProperties() as $target_prop){
            foreach( $target_prop->getAllAnnotations("Index") as $target_annot ){
                if( $target_annot->type == "pk" ){
                    // find default encoding from foreign table
                    $target_table_annot         = $target_class_annots->getAnnotation('Table');
                    $target_table_encoding      = $default_table_encoding;
                    if( $target_table_annot !== false ){
                        if( $target_table_annot->encoding !== null )
                            $target_table_encoding = $target_table_annot->encoding;
                    }
                    $field_name             = strtolower($prop_name_as)."_".strtolower($target_prop->name);
                    $retour[$field_name]    = $this->buildFieldInfos(
                                                        $field_name,
                                                        $target_prop->getAnnotation("Column"),
                                                        $target_table_encoding );
                }
            }
        }
        return $retour;
    }

    /**
     *
     * @param string $left_class_name
     * @param string $right_class_name
     * @return bool
     */
    protected function isManyToMany( $left_class_name, $left_prop_name, $right_class_name, $right_prop_name ){
        $is_many_left_to_right  = false;
        $is_many_right_to_left  = false;

        $left_class_annots    = $this->readActiveModel($left_class_name);
        $right_class_annots   = $this->readActiveModel($right_class_name);

        foreach( $left_class_annots->getProperties() as $left_prop){
            if( $left_prop->name == $left_prop_name ){
                foreach( $left_prop->getAllAnnotations("HasMany") as $target_annot ){
                    $t = explode(".",$target_annot->target);
                    $t = $t[0];
                    if( $t == $right_class_name ){
                        $is_many_left_to_right = true;
                        break;
                    }
                }
            }
        }

        foreach( $right_class_annots->getProperties() as $right_prop){
            if( $right_prop->name == $right_prop_name ){
                foreach( $right_prop->getAllAnnotations("HasMany") as $target_annot ){
                    $t = explode(".",$target_annot->target);
                    $t = $t[0];
                    if( $t == $left_class_name ){
                        $is_many_right_to_left = true;
                        break;
                    }
                }
            }
        }

        return $is_many_left_to_right && $is_many_right_to_left;
    }

    /**
     *
     */
    protected function makeForeignKeyAccessor($model_name, $local_object_prop_name, $local_prop_name, $foreign_prop_name, $foreign_annot ){
        //$retour = $this->makeSimpleAccessor($foreign_annot, "_ro_db_values");

        $retour = array();
        // $setter = $retour["setter"];
        // $getter = $retour["getter"];
        $retour["default_value"]= $this->getDefaultValueFromPropAnnot( $foreign_annot );

        $retour["setter"] = 'function set_'.$model_name.'_'.$local_prop_name.'($this_model, $prop_name, $value){
                            }';
        $retour["getter"] = 'function get_'.$model_name.'_'.$local_prop_name.'($this_model, $prop_name, $value){
                            }';
        /*
        $retour["setter"] = function ($this_model, $prop_name, $value)
                                use($setter, $local_object_prop_name){
                                $setter($this_model, $prop_name, $value);
                                $this_model->_virtual_values[$local_object_prop_name] = null;
                            };
        $retour["getter"] = function($this_model, $prop_name)
                                use($getter, $local_object_prop_name, $foreign_prop_name){
                                $o = $this_model->_virtual_values[$local_object_prop_name];
                                if( $o === null ){
                                    return $getter($this_model, $prop_name);
                                }else{
                                    return $o->{$foreign_prop_name};
                                }
                            };*/
        return $retour;
    }

    protected function makeObjectAccessor( $object_class_type, $current_object_class_type, $current_object_prop_name, $local_fks ){
        $retour = array(
            "setter"=>null,
            "getter"=>null,
        );

        $foreign_object_pks = array();
        foreach( $local_fks as $local_fk ){
            $foreign_object_pks[] = substr($local_fk, strlen($local_fk)+1);
        }

        $retour["getter"] = function ($this_model, $prop_name)
                        use($object_class_type, $current_object_class_type, $current_object_prop_name, $local_fks) {
            $o = $this_model->_virtual_values[$prop_name];
            if( $this_model->_virtual_values[$prop_name] === null ){
                $builder =  $object_class_type::inner_join($current_object_class_type.$current_object_prop_name);
                foreach( $local_fks as $current_fk=>$local_fk ){
                    $builder->where($current_object_class_type.$current_fk, $this_model->$local_fk );
                }

                $this_model->_virtual_values[$prop_name] = $builder->find_one();
            }
            return $this_model->_virtual_values[$prop_name];
        };

        $retour["setter"] = function ($this_model, $prop_name, $value)
                        use($local_fks, $foreign_object_pks) {
            foreach( $foreign_object_pks as $index=>$foreign_object_pk ){
                $this->_values[ $local_fks[$index] ] = $value->$foreign_object_pk;
            }
            $this_model->_virtual_values[$prop_name] = $value;
        };

        return $retour;
    }

    protected function makeCollectionAccessor( /*....*/ ){

    }

    protected function getDefaultValueFromPropAnnot( $annot ){
        $default_value  = $annot->default_value;
        $nullable       = $annot->nullable===null?true:(bool)$annot->nullable;

        $reset_default_value = false;
        if( !$nullable ){
            if( $default_value === null ){
                switch( $annot->type ){
                    case "text":
                    $default_value = "";
                        break;
                    case "int":
                    case "float":
                    $default_value = 0;
                        break;
                }
            }
        }
        return $default_value;
    }

    /**
     *
     */
    protected function makeSimpleAccessor($model_name, $prop_name, $annot, $from_property="_values"){
        // grab infos from annots
        $size           = $annot->size;

        $type_guessed   = null;
        $type_infos     = array();

        switch( $annot->type ){
            case "text":
            $type_guessed   = "string";
            if( $size !== null )
                $type_infos     = array("size"=>(int)$size);
                break;
            case "int":
            case "float":
            $type_guessed   = $annot->type;
            if( $size !== null )
                $type_infos     = array("size"=>(int)$size);
                break;
        }

        $retour = array();
        $retour["getter"]       = $this->makeAGetter( $model_name, $prop_name, $from_property, $type_guessed, $type_infos );
        $retour["setter"]       = $this->makeASetter( $model_name, $prop_name, $from_property, $type_guessed, $type_infos );
        $retour["default_value"]= $this->getDefaultValueFromPropAnnot( $annot );

        return $retour;
    }

    /**
     *
     */
    protected function makeManyToManyAccessor($prop_name, $from, $to, $to_prop_name ){
        $getter = function (){

        };

        $model_table    = $this->getTableNameFromModelName($from);
        $target_table   = $this->getTableNameFromModelName($to);
        $rel_table_name = $this->makeRelTableName($prop_name, $to_prop_name);
        $rel_fields     = array();
        $tgt_fields     = array();
        $table_encoding = null; /** @todo update this value .... */
        // find the pk to make the rel
        // from foreign class
        $foreign_fields = $this->makeForeignFields($to, $to, $table_encoding);
        foreach( $foreign_fields as $foreign_field_name => $foreign_field_infos ){
            $tgt_fields[$foreign_field_name]    = substr($foreign_field_name, strlen($to)+1);
        }
        // from foreign class
        // from current class
        $foreign_fields = $this->makeForeignFields($from, $from, $table_encoding);
        foreach( $foreign_fields as $foreign_field_name => $foreign_field_infos ){
            $rel_fields[$foreign_field_name]    = substr($foreign_field_name, strlen($from)+1);
        }
        // from current class

        $retour = array();
        $retour["virutal_prop_infos"] = array(
            "type"          =>"has_many_to_many",
            "class"         =>$to,
            "own"           =>false,
            "rel_tbl_name"  =>$rel_table_name,
            "rel_fields"    =>$rel_fields,
            "tgt_tbl_name"  =>$target_table,
            "tgt_fields"    =>$tgt_fields,
        );
        $retour["rel_class_infos"] = array(
            "type"  =>"many_to_many",
            "class" =>$to,
            "own"   =>false,
            "fields"=>array(),
            );

        return $retour;
    }

    protected function makeOneToManyAccessor( $from, $to, $to_prop, $target_prop, $own=false ){
        $getter = function (){

        };

        $table_encoding = null; /** @todo update this value .... */

        $pks        = $this->makeForeignFields($to, $to_prop);

        $raw_fields = array();
        foreach( $pks as $pk_field=>$pk_infos ){
            $raw_fields[$pk_field] = substr($pk_field, strlen(strtolower($to_prop)."_"));
        }

        $arr_target_prop = explode(".",$target_prop);
        $target_prop = array(
            "model"     =>$arr_target_prop[0],
            "property"  =>$arr_target_prop[1],
        );

        $retour = array();
        $retour["virutal_prop_infos"] = array(
            "type"      =>"has_many",
            "class"     =>$to,
            "own"       =>$own,
            "target"    =>$target_prop,
            "fields"    =>$raw_fields,
        );
        $retour["rel_class_infos"] = array(
            "type"      =>"has_many",
            "class"     =>$to,
            "own"       =>$own,
            "target"    =>$target_prop,
            "fields"    =>$raw_fields,
            );

        return $retour;
    }

    protected function makeOneToOneAccessor( $prop_name, $from, $to, $own=false ){
        $retour     = array();
        $pks        = $this->makeForeignFields($to, $prop_name);

        $raw_fields = array();
        foreach( $pks as $pk_field=>$pk_infos ){
            $raw_fields[substr($pk_field, strlen(strtolower($prop_name)."_"))] = $pk_field;
        }

        $current_object_class_type  = $from;
        $object_class_type          = $to;
        $current_object_prop_name   = $prop_name;
        $foreign_object_pks         = array();

        $retour["getter"] = 'function get_'.$current_object_class_type.'_'.$prop_name.'($this_model, $prop_name) {
            $o = $this_model->_virtual_values[$prop_name];
            if( $this_model->_virtual_values[$prop_name] === null ){
                $builder =  '.$object_class_type.'::select()->inner_join("'.$current_object_class_type.'.'.$current_object_prop_name.'");
                $raw_fields = '.var_export($raw_fields, true).';
                foreach( $raw_fields as $local_fk=>$foreign_pk ){
                    $builder->where("'.$current_object_class_type.'.".$foreign_pk, $this_model->$local_fk );
                }

                $this_model->_virtual_values[$prop_name] = $builder->find_one();
            }
            return $this_model->_virtual_values[$prop_name];
        }';

        $retour["setter"] = 'function set_'.$current_object_class_type.'_'.$prop_name.'($this_model, $prop_name, $value) {
            $foreign_object_pks = '.var_export($foreign_object_pks, true).';
            foreach( $foreign_object_pks as $local_fk=>$foreign_pk ){
                $this->_values[ $local_fk ] = $value->$foreign_pk;
            }
            $this_model->_virtual_values[$prop_name] = $value;
        }';


        $retour["virutal_prop_infos"] = array(
            "type"  =>"has_one",
            "class" =>$to,
            "own"   =>$own,
            "fields"=>$raw_fields
        );
        $retour["rel_class_infos"] = array(
            "type"  =>"has_one",
            "class" =>$to,
            "own"   =>$own,
            "fields"=>$raw_fields,
            );
        return $retour;
    }

    /**
     *
     * @param string $type
     * @return Closure
     */
    protected function makeAGetter($model_name, $prop_name, $from_property, $type=null){
        if($type === null ){
            return 'function get_'.$model_name.'_'.$prop_name.'($this_model, $prop_name)
                    {return $this_model->'.$from_property.'[$prop_name];}';
        }elseif( $type == "string" ){
            return 'function get_'.$model_name.'_'.$prop_name.'($this_model, $prop_name)
                    {return (string)$this_model->'.$from_property.'[$prop_name];}';
        }elseif( $type == "int" ){
            return 'function get_'.$model_name.'_'.$prop_name.'($this_model, $prop_name)
                    {return (int)$this_model->'.$from_property.'[$prop_name];}';
        }elseif( $type == "float" ){
            return 'function get_'.$model_name.'_'.$prop_name.'($this_model, $prop_name)
                    {return (float)$this_model->'.$from_property.'[$prop_name];}';
        }
    }

    /**
     *
     * @param string $type
     * @param array $params
     * @return Closure
     */
    protected function makeASetter($model_name, $prop_name, $from_property, $type=null, $params=array()){
        if($type === null ){
            return 'function set_'.$model_name.'_'.$prop_name.'($this_model, $prop_name)
                    {$this_model->'.$from_property.'[$prop_name] = $value;}';
        }elseif( $type == "string" ){
            if( isset($params["size"]) ){
                $size = $params["size"];
                return 'function set_'.$model_name.'_'.$prop_name.'($this_model, $prop_name)
                        {$this_model->'.$from_property.'[$prop_name] = substr((string)$value,0, '.$size.');}';
            }else{
                return 'function set_'.$model_name.'_'.$prop_name.'($this_model, $prop_name)
                        {$this_model->'.$from_property.'[$prop_name] = (string)$value;}';
            }
        }elseif( $type == "int" ){
            if( isset($params["size"]) ){
                $size = $params["size"];
                return 'function set_'.$model_name.'_'.$prop_name.'($this_model, $prop_name)
                        {$this_model->'.$from_property.'[$prop_name] = (int)substr((string)$value,0, '.$size.');}';
            }else{
                return 'function set_'.$model_name.'_'.$prop_name.'($this_model, $prop_name)
                        {$this_model->'.$from_property.'[$prop_name] = (int)$value;}';
            }
        }elseif( $type == "float" ){
            if( isset($params["size"]) ){
                $size = $params["size"];
                return 'function set_'.$model_name.'_'.$prop_name.'($this_model, $prop_name)
                        {$this_model->'.$from_property.'[$prop_name] = (float)substr((string)$value,0, '.$size.');}';
            }else{
                return 'function set_'.$model_name.'_'.$prop_name.'($this_model, $prop_name)
                        {$this_model->'.$from_property.'[$prop_name] = (float)$value;}';
            }
        }
    }

}