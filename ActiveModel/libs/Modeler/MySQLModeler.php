<?php

class MySQLModeler implements ITableModeler{
    
    protected $exec_callback;
    protected $read_callback;
    
    protected $database_name;
    protected $tables_list;
    protected $columns_list;
    protected $indexs_list;
    
    protected function exec($sql){
        $retour = call_user_func_array($this->exec_callback, array($sql));
        if( $retour === false ){
            echo $sql;
            echo "<br/>\n";
        }
        return $retour;
    }
    
    protected function query($sql){
        $retour = call_user_func_array($this->read_callback, array($sql));
        if( $retour === false ){
            echo $sql;
            echo "<br/>\n";
        }
        return $retour;
    }
    
    protected function list_tables(){
        $this->tables_list  = array();
        $sql = "SHOW FULL TABLES FROM ".$this->proper_name( $this->database_name )."";
        foreach ($this->query($sql) as $row) {
            $this->tables_list[] = $row[0];
        }
        return $this->tables_list;
    }
    
    protected function list_columns( $table_name ){
        $this->columns_list = array();
        $sql = "SHOW FULL COLUMNS FROM ".$this->proper_name( $table_name )." FROM `".$this->database_name."`";
        $this->columns_list[$table_name] = array();
        foreach ($this->query($sql) as $row) {
            $this->columns_list[$table_name][] = $row[0];
        }
        return $this->columns_list[$table_name];
    }
    
    protected function list_index( $table_name ){
        $this->indexs_list = array();
        $sql = "SHOW INDEX FROM ".$this->proper_name( $table_name )." FROM `".$this->database_name."`; ";
        $this->indexs_list[$table_name] = array();
        foreach ($this->query($sql) as $row) {
            $this->indexs_list[$table_name][] = $row["Key_name"];
        }
        return $this->indexs_list[$table_name];
    }
    
    protected function proper_name( $value_name, $with_quoter=true ){
        $value_name = strtolower($value_name);
        return $with_quoter?"`".$value_name."`" : $value_name;
    }
    
    public function setCallback( $exec_callback, $read_callback ){
        $this->exec_callback = $exec_callback;
        $this->read_callback = $read_callback;
    }
    
    public function setContainerName( $name ){
        $this->database_name = $name;
    }
    
    public function hasTable( $raw_table_name ){
        return in_array($this->proper_name( $raw_table_name, false ),
                        $this->list_tables());
    }
    public function hasField( $raw_table_name, $field_name ){
        return in_array($this->proper_name( $field_name, false ),
                        $this->list_columns( $raw_table_name ));
    }
    public function hasIndex( $raw_table_name, $index_name ){
        return in_array($index_name,
                        $this->list_index( $raw_table_name ));
    }
    
    public function createTable( $raw_table_name, $options=array() ){
        $sql = "CREATE TABLE ".$this->proper_name( $raw_table_name )."";
        /* Create a default required field, will be removed in later time */
        $sql .= " (`required_first_field` INT) ";
        if( isset($options["engine"]) )
            $sql .= " ENGINE = " . $options["engine"];
        if( isset($options["encoding"]) ){
            $char_set = substr($options["encoding"], 0, strpos($options["encoding"], "_"));
            $sql .= " DEFAULT CHARACTER SET = " . $char_set;
            $sql .= " COLLATE = " . $options["encoding"];
        }
        if( isset($options["comment"]) )
            $sql .= " COMMENT = '" . addslashes($options["comment"]) . "'";
        $sql .= ";";
        
        return $this->exec($sql);
    }
    public function createField( $raw_table_name, $field_name, $options=array() ){
        
        $mysql_type = null;
        $size       = isset($options["size"]) ? $options["size"] : null;
        $nullable   = isset($options["nullable"]) ? $options["nullable"] : false;
        $default    = isset($options["default_value"]) ? $options["default_value"] : false;
        $comment    = isset($options["comment"]) ? $options["comment"] : null;
        $char_set   = null;
        $collate    = null;
        if( isset($options["encoding"]) ){
            $char_set   = substr($options["encoding"], 0, strpos($options["encoding"], "_"));
            $collate    = $options["encoding"];
        }
        
        if( isset($options["type"]) ){
            switch( $options["type"] ){
                case "text":
                    if( $size === null ) $mysql_type = "TEXT";
                    else $mysql_type = "VARCHAR";
                    break;
                default:
                    $mysql_type = strtoupper($options["type"]);
                    break;
            }
        }
        
        $sql = "ALTER TABLE ".$this->proper_name( $raw_table_name )."";
        $sql .= " ADD ".$this->proper_name( $field_name )."";
        $sql .= " ".$mysql_type."";
        if( $size !== NULL )            $sql .= " (".$size.") ";
        
        if(in_array($mysql_type, array("INT","FLOAT")) === false ){
        if( $char_set !== NULL )        $sql .= " CHARACTER SET " . $char_set;
        if( $collate !== NULL )         $sql .= " COLLATE " . $collate;
        }
        
        if( $nullable  )                $sql .= " NULL ";
        if( $default !== false  ){
            if( $default === "null" )   $sql .= " DEFAULT NULL ";
            else                        $sql .= " DEFAULT '".$default."' ";
        }
        if( $comment !== NULL )         $sql .= " COMMENT '" .  addslashes($comment) . "' ";
        
        $sql .= ";";
        
        return $this->exec($sql);
    }
    public function createIndex( $raw_table_name, $index_name, $options=array() ){
        
        $type       = isset($options["type"]) ? strtoupper($options["type"]) : null;
        $engine     = isset($options["engine"]) ? $options["engine"] : null;
        
        if( $type !== "PK" ){
            switch( $type ){
                case "UNIQUE":
                    $type = "UNIQUE INDEX";
                    break;
                case "FULLTEXT":
                    $type = "FULLTEXT INDEX";
                    break;
                case "SPATIAL":
                    $type = "SPATIAL INDEX";
                    break;
            }

            $sql = "CREATE ".$type." ".$this->proper_name( $index_name )." ";
            if( $engine !== null )
                $sql .= " USING ".$engine." ";
            $sql .= " ON ".$this->proper_name( $raw_table_name )." ";
            $sql .= " ( ";
            foreach( $options["fields"] as $field_name=>$field_options ){
                $sql .= " ".$this->proper_name( $field_name )." ";
                if( isset($field_options["size"]) )
                    $sql .= "(".$field_options["size"].") ";
                if( isset($field_options["order"]) )
                    $sql .= " ".$field_options["order"]." ";
                $sql .= ", ";
            }
            $sql = substr($sql,0,-2);
            $sql .= " ) ";
            $sql .= ";";
        }else{
            if( $this->hasIndex($raw_table_name, "PRIMARY") ){
                $this->removeIndex($raw_table_name, "PRIMARY");
            }
            $sql = "ALTER TABLE ".$this->proper_name( $raw_table_name )." ";
            $sql .= "ADD PRIMARY KEY";
            $sql .= "( ";
            foreach( $options["fields"] as $field_name=>$field_options ){
                $sql .= " ".$this->proper_name( $field_name )." ";
                $sql .= ", ";
            }
            $sql = substr($sql,0,-2);
            $sql .= ") ";
            $sql .= "; ";
        }
        
        return $this->exec($sql);
    }
    
    public function removeTable( $raw_table_name ){
        $sql    = "DROP TABLE ".$this->proper_name( $raw_table_name )." ; ";
        
        return $this->exec($sql);
    }
    public function removeField( $raw_table_name, $field_name ){
        $sql    = "ALTER TABLE ".$this->proper_name( $raw_table_name )." DROP ".$this->proper_name( $field_name )." ; ";
        
        return $this->exec($sql);
        
    }
    public function removeIndex( $raw_table_name, $index_name ){
        $sql    = "DROP INDEX ".$this->proper_name( $index_name )." ON ".$this->proper_name( $raw_table_name )." ; ";
        
        return $this->exec($sql);
    }
    
    public function updateField( $raw_table_name, $field_name, $options=array() ){
        
    }
    
    public function clean( $raw_table_name ){
        $this->removeField( $raw_table_name, "required_first_field" );
    }
}