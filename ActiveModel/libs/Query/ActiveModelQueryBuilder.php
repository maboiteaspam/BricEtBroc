<?php
/**
 * Description of ActiveModelQueryBuilder
 *
 * @author clement
 */
class ActiveModelQueryBuilder {

    /**
     * @var \ActiveQueryBuilder
     */
    protected $active_query_builder;

    /**
     * @param ActiveQueryBuilder $builder
     */
    public function __construct( ActiveQueryBuilder $builder ) {
        $this->active_query_builder = $builder;
    }

    /**
     * @param $from_model
     * @return ActiveModelSelectBuilder
     */
    public function select($from_model){
        $retour = new ActiveModelSelectBuilder( $this->active_query_builder, $from_model, "ActiveModelCollection" );
        return $retour;
    }

    /**
     * @param $into
     * @return ActiveModelInsertBuilder
     */
    public function insert($into){
        $retour = new ActiveModelInsertBuilder( $this->active_query_builder->insert($into) );
        return $retour;
    }

    /**
     * @param $table
     * @return ActiveModelUpdateBuilder
     */
    public function update($table){
        $retour = new ActiveModelUpdateBuilder( $this->active_query_builder->update($table) );
        return $retour;
    }

    /**
     * @param $into
     * @return ActiveModelReplaceBuilder
     */
    public function replace($into){
        $retour = new ActiveModelReplaceBuilder( $this->active_query_builder->replace($into) );
        return $retour;
    }

    /**
     * @param $from
     * @return ActiveModelDeleteBuilder
     */
    public function delete($from){
        $retour = new ActiveModelDeleteBuilder( $this->active_query_builder->delete($from) );
        return $retour;
    }
}


class InvalidBuildException extends Exception{

}

class ActiveModelSelectBuilder{

    /**
     *
     * @var ActiveSelectBuilder
     */
    protected $_builder;
    protected $_active_query_builder;
    /**
     *
     * @var string
     */
    protected $_item_model;
    /**
     *
     * @var string
     */
    protected $_list_model;
    /**
     *
     * @var array
     */
    protected $_models_infos;



    // Columns to select in the result
    protected $_result_columns = null;
    protected $_using_default_result_columns = true;

    // Join sources
    protected $_join_sources = array();

    // Should the query include a DISTINCT keyword?
    protected $_distinct = false;

    // Is this a raw query?
    protected $_is_raw_query = false;

    // The raw query
    protected $_raw_query = '';

    // Array of WHERE clauses
    protected $_where_conditions = array();

    // LIMIT
    protected $_limit = null;

    // OFFSET
    protected $_offset = null;

    // ORDER BY
    protected $_order_by = array();

    // GROUP BY
    protected $_group_by = array();

    // VALUES
    protected $_values = array();
    protected $_table_alias = null;


    public function __construct( ActiveQueryBuilder $active_query_builder, $item_model, $list_model ){
        $this->_active_query_builder        = $active_query_builder;
        $this->_item_model                  = $item_model;
        $this->_list_model                  = $list_model;
        $this->_models_infos                = array();
        $this->loadModelInfos( $item_model );
    }

    /**
     * Perform a raw query. The query should contain placeholders,
     * in either named or question mark style, and the parameters
     * should be an array of values which will be bound to the
     * placeholders in the query. If this method is called, all
     * other query building methods will be ignored.
     * @param $query
     * @param null $values
     * @return ActiveModelSelectBuilder
     */
    public function raw_query($query, $values=null) {
        $this->_raw_query = $query;
        $this->_values[] = $values;
        return $this;
    }

    /**
     * Add an alias for the main table to be used in SELECT queries
     * @param $alias
     * @return ActiveModelSelectBuilder
     */
    public function table_alias($alias) {
        $this->_table_alias = $alias;
        return $this;
    }
    public function as_($alias) {
        $this->_table_alias = $alias;
        return $this;
    }

    /**
     * Add a column to the list of columns returned by the SELECT
     * query. This defaults to '*'. The second optional argument is
     * the alias to return the column as.
     * @param $column
     * @param null $alias
     * @return ActiveModelSelectBuilder
     */
    public function select($column, $alias=null) {
        if ($this->_using_default_result_columns){
            $this->_result_columns = array();
            $this->_using_default_result_columns = false;
        }
        $this->_result_columns[] = array("expr"=>$column, "as"=>$alias, "type"=>"column");
        return $this;
    }

    /**
     * Add an unquoted expression to the list of columns returned
     * by the SELECT query. The second optional argument is
     * the alias to return the column as.
     *
     * @param $expr
     * @param null $alias
     * @return ActiveModelSelectBuilder
     */
    public function select_expr($expr, $alias=null) {
        if ($this->_using_default_result_columns){
            $this->_result_columns = array();
            $this->_using_default_result_columns = false;
        }
        $this->_result_columns[] = array("expr"=>$expr, "as"=>$alias, "type"=>"expr");
        return $this;
    }

    /**
     * Add a DISTINCT keyword before the list of columns in the SELECT query
     * @return ActiveModelSelectBuilder
     */
    public function distinct() {
        $this->_distinct = true;
        return $this;
    }

    /**
     * Tell the ORM that you wish to execute a COUNT query.
     * Will return an integer representing the number of
     * rows returned.
     * @param null $alias
     * @return ActiveModelSelectBuilder
     */
    public function count($alias=null) {
        if ($this->_using_default_result_columns){
            $this->_result_columns = array();
            $this->_using_default_result_columns = false;
        }
        $this->_result_columns[] = array("expr"=>"COUNT(*)", "as"=>$alias, "type"=>"count");
        return $this;
    }

    /**
     * Add a simple JOIN source to the query
     * @param $model
     * @param null $model_alias
     * @return ActiveModelSelectBuilder
     */
    public function join($model, $model_alias=null) {
        $this->_join_sources[] = array( "type"      =>"inner_join",
                                        "join"      =>$model,
                                        "as"        =>$model_alias,
                                        "on"        =>null);
        return $this;
    }

    /**
     * Add an INNER JOIN souce to the query
     * We can receive model as
     *  - ModelName
     *  - ModelName.property
     *  - property (of one model already selected / joined)
     *  - table name (? to check feasibility)
     * @param $model
     * @param null $model_alias
     * @return ActiveModelSelectBuilder
     */
    public function inner_join($model, $model_alias=null) {
        $this->_join_sources[] = array( "type"      =>"inner_join",
                                        "join"      =>$model,
                                        "as"        =>$model_alias,
                                        "on"        =>null);
        return $this;
    }

    /**
     * Add a LEFT OUTER JOIN souce to the query
     * @param $model
     * @param null $model_alias
     * @return ActiveModelSelectBuilder
     */
    public function left_outer_join($model, $model_alias=null) {
        $this->_join_sources[] = array( "type"      =>"left_outer_join",
                                        "join"      =>$model,
                                        "as"        =>$model_alias,
                                        "on"        =>null);
        return $this;
    }

    /**
     * Add an RIGHT OUTER JOIN souce to the query
     * @param $model
     * @param null $model_alias
     * @return ActiveModelSelectBuilder
     */
    public function right_outer_join($model, $model_alias=null) {
        $this->_join_sources[] = array( "type"      =>"right_outer_join",
                                        "join"      =>$model,
                                        "as"        =>$model_alias,
                                        "on"        =>null);
        return $this;
    }

    /**
     * Add an FULL OUTER JOIN souce to the query
     * @param $model
     * @param null $model_alias
     * @return ActiveModelSelectBuilder
     */
    public function full_outer_join($model, $model_alias=null) {
        $this->_join_sources[] = array( "type"      =>"full_outer_join",
                                        "join"      =>$model,
                                        "as"        =>$model_alias,
                                        "on"        =>array());
        return $this;
    }
    /**
     * Add an ON clause for a constraint
     * @param $col_left
     * @param null $col_right
     * @return ActiveModelSelectBuilder
     */
    public function on($col_left, $col_right=null) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"][] = array( "left"=>$col_left,
                                                    "operator"=>"on",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_equal($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"][] = array( "left"=>$col_left,
                                                    "operator"=>"on_equal",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_not_equal($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"][] = array( "left"=>$col_left,
                                                    "operator"=>"on_not_equal",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_like($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"][] = array( "left"=>$col_left,
                                                    "operator"=>"on_like",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_not_like($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"][] = array( "left"=>$col_left,
                                                    "operator"=>"on_not_like",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_gt($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"][] = array( "left"=>$col_left,
                                                    "operator"=>"on_gt",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_lt($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"][] = array( "left"=>$col_left,
                                                    "operator"=>"on_lt",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_gte($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"][] = array( "left"=>$col_left,
                                                    "operator"=>"on_gte",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_lte($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"][] = array( "left"=>$col_left,
                                                    "operator"=>"on_lte",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }

    /**
     * Add a WHERE column = value clause to your query. Each time
     * this is called in the chain, an additional WHERE will be
     * added, and these will be ANDed together when the final query
     * is built.
     * @param $column_name
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where($column_name, $value) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * More explicitly named version of for the where() method.
     * Can be used if preferred.
     * @param $column_name
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where_equal($column_name, $value) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_equal",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * Add a WHERE column != value clause to your query.
     * @param $column_name
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where_not_equal($column_name, $value) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_not_equal",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * Add a WHERE ... LIKE clause to your query.
     * @param $column_name
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where_like($column_name, $value) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_like",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * Add where WHERE ... NOT LIKE clause to your query.
     * @param $column_name
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where_not_like($column_name, $value) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_not_like",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * Add a WHERE ... > clause to your query
     * @param $column_name
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where_gt($column_name, $value) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_gt",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * Add a WHERE ... < clause to your query
     * @param $column_name
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where_lt($column_name, $value) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_lt",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * Add a WHERE ... >= clause to your query
     * @param $column_name
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where_gte($column_name, $value) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_gte",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * Add a WHERE ... <= clause to your query
     * @param $column_name
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where_lte($column_name, $value) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_lte",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * Add a WHERE ... IN clause to your query
     * @param $column_name
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where_in($column_name, $value) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_in",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * Add a WHERE ... NOT IN clause to your query
     * @param $column_name
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where_not_in($column_name, $value) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_not_in",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * Add a WHERE column IS NULL clause to your query
     * @param $column_name
     * @return ActiveModelSelectBuilder
     */
    public function where_null($column_name) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_null",
            "value"=>null,
        );
        return $this;
    }

    /**
     * Add a WHERE column IS NOT NULL clause to your query
     * @param $column_name
     * @return ActiveModelSelectBuilder
     */
    public function where_not_null($column_name) {
        $this->_where_conditions[] = array(
            "what"=>$column_name,
            "operator"=>"where_not_null",
            "value"=>null,
        );
        return $this;
    }

    /**
     * Add a raw WHERE clause to the query. The clause should
     * contain question mark placeholders, which will be bound
     * to the parameters supplied in the second argument.
     * @param $clause
     * @param $value
     * @return ActiveModelSelectBuilder
     */
    public function where_raw($clause, $value) {
        $this->_where_conditions[] = array(
            "what"=>$clause,
            "operator"=>"where_raw",
            "value"=>$value,
        );
        return $this;
    }

    /**
     * Add a LIMIT to the query
     * @param $limit
     * @return ActiveModelSelectBuilder
     */
    public function limit($limit) {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Add an OFFSET to the query
     * @param $offset
     * @return ActiveModelSelectBuilder
     */
    public function offset($offset) {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * Add an ORDER BY column DESC clause
     * @param $column_name
     * @return ActiveModelSelectBuilder
     */
    public function order_by_desc($column_name) {
        $this->_order_by[] = array("column"=>$column_name,"direction"=>"DESC");
        return $this;
    }

    /**
     * Add an ORDER BY column ASC clause
     * @param $column_name
     * @return ActiveModelSelectBuilder
     */
    public function order_by_asc($column_name) {
        $this->_order_by[] = array("column"=>$column_name,"direction"=>"ASC");
        return $this;
    }

    /**
     * Add a column to the list of columns to GROUP BY
     * @param $column_name
     * @return ActiveModelSelectBuilder
     */
    public function group_by($column_name) {
        $this->_group_by[] = $column_name;
        return $this;
    }

    protected function isAModel( $model_name ){
        return ActiveModelController::is_a_model($model_name);
    }

    protected function getPksOfAModel( $model ){
        foreach( $this->_models_infos[$model]["table_infos"]["indexs"] as $index ){
            if( $index["type"] === "pk" ){
                return $index["fields"];
            }
        }
        return null;
    }

    protected function getFksOfAProperty( $model, $property ){
        return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["fields"];
    }

    protected function getLeftKeysOfManyToMany( $model, $property ){
        return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["rel_fields"];
    }

    protected function getRightKeysOfManyToMany( $model, $property ){
        return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["tgt_fields"];
    }

    protected function getTableOfAModel( $model ){
        return $this->_models_infos[$model]["table_infos"]["table"]["name"];
    }

    protected function getMiddleTableOfAManyToMany( $model, $property ){
        return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["rel_tbl_name"];
    }

    protected function getTableOfAProperty( $model, $property ){
        return $this->getTableOfAModel(
                    $this->getModelOfAProperty( $model, $property )
                );
    }

    protected function getModelOfAProperty( $model, $property ){
        return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["class"];
    }

    protected function reversedTarget( $model, $property ){
        if( isset($this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["target"]) ){
            return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["target"];
        }else{
            $target_class = $this->getClassTypeOfAProperty($model, $property);
            if( $target_class === false )
                return false;
            $this->loadModelInfos($target_class);
            // if target is precise
            foreach( $this->_models_infos[$target_class]["model_infos"]["virutal_prop_infos"] as $prop => $v_infos ){
                if( isset($v_infos["target"]) ){
                    if( $v_infos["target"]["model"] === $model
                        && $v_infos["target"]["property"] === $property){
                        return array("model"=>$target_class,"property"=>$prop);
                    }
                }
            }
            // otherwise try to guess it using class only
            foreach( $this->_models_infos[$target_class]["model_infos"]["virutal_prop_infos"] as $prop => $v_infos ){
                if( isset($v_infos["target"]) ){
                    if( $v_infos["target"]["model"] === $model){
                        return array("model"=>$target_class,"property"=>$prop);
                    }
                }
            }
        }
        return false;
    }

    protected function isHasMany( $model, $property ){
        if( isset($this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["type"]) )
            return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["type"] === "has_many";
        return false;
    }

    protected function isHasOne( $model, $property ){
        if( isset($this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["type"]) )
            return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["type"] === "has_one";
        return false;
    }

    protected function isHasManyToMany( $model, $property ){
        if( isset($this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["type"]) )
            return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["type"] === "has_many_to_many";
        return false;
    }

    protected function getTarget( $model, $property ){
        return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][$property]["target"];
    }

    protected function getClassTypeOfAProperty( $model, $property ){
        if( isset($this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][$property]["class"]) )
            return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][$property]["class"];
        return false;
    }

    protected function loadModelInfos( $model ){
        if( isset($this->_models_infos[$model]) === false )
            $this->_models_infos[$model] = ActiveModelController::get_model_infos ($model);
        return $this->_models_infos[$model];
    }

    protected function findPropertiesForAModel( $searched_model_name ){
        $retour = array();
        foreach( $this->_models_infos as $model_name => $model_infos ){
            if( isset($model_infos["model_infos"]["rel_class_infos"][$searched_model_name]) ){
                foreach( $model_infos["model_infos"]["rel_class_infos"][$searched_model_name] as $prop_name => $prop_infos ){
                    $retour[] = array("model"=>$model_name, "property"=>$prop_name);
                }
            }
        }
        return $retour;
    }

    protected function findModelsForAProperty( $searched_property_name ){
        $retour = array();
        foreach( $this->_models_infos as $model_name => $model_infos ){
            if( isset($model_infos["model_infos"]["virutal_prop_infos"][$searched_property_name]) ){
                $retour[] = array(  "model"     =>$model_name,
                    "property"  =>$searched_property_name);
            }elseif( isset($model_infos["model_infos"]["getters"][$searched_property_name]) ){
                $retour[] = array(  "model"     =>$model_name,
                                    "property"  =>$searched_property_name);
            }
        }
        return $retour;
    }

    public function resolve_alias( $input_alias ){
        if( $input_alias === $this->_table_alias ){
            return $this->_item_model;
        }else{
            foreach( $this->_join_sources as $join ){
                if( $input_alias === $join["as"] ){
                    return $join["join"];
                }
            }
        }
        return null;
    }
    public function get_alias_of_a_model( $model ){
        if( $model === $this->_item_model ){
            return $this->_table_alias;
        }else{
            foreach( $this->_join_sources as $join ){
                if( $model === $join["join"] ){
                    return $join["as"];
                }
            }
        }
        return null;
    }

    public function normalizeInput( $input_target ){
        $info = explode(".", $input_target);
        if( trim($input_target) === "" ){
            $retour = array(
                "model"     =>null,
                "property"  =>null,
                "alias"     =>null,
                "table"     =>null,
                "as"        =>null,
            );
        }else{
            $info = explode(".", $input_target);
            if( count($info) > 1 ){
                $retour = array(
                    "model"     =>$info[0],
                    "property"  =>$info[1],
                    "alias"     =>null,
                    "table"     =>null,
                    "as"        =>null,
                );
            }else{
                $retour = array(
                    "model"     =>$info[0],
                    "property"  =>null,
                    "alias"     =>null,
                    "table"     =>null,
                    "as"        =>null,
                );
            }
        }
        return $retour;
    }

    public function getTypeOfAssociation($join_on_left, $join_on_right){
        $retour = null;
        if( $this->isHasOne($join_on_left["model"], $join_on_left["property"])
            && $this->isHasMany($join_on_right["model"], $join_on_right["property"]) ){
            $retour = "one_to_many";
        }elseif( $this->isHasMany($join_on_left["model"], $join_on_left["property"])
            && $this->isHasOne($join_on_right["model"], $join_on_right["property"]) ){
            $retour = "many_to_one";
        }elseif( $this->isHasManyToMany($join_on_left["model"], $join_on_left["property"])
            && $this->isHasManyToMany($join_on_right["model"], $join_on_right["property"]) ){
            $retour = "many_to_many";
        }elseif( $this->isHasOne($join_on_left["model"], $join_on_left["property"])
            && $this->isHasOne($join_on_right["model"], $join_on_right["property"]) ){
            $retour = "one_to_one";
        }
        return $retour;
    }

    public function resolve_joins(){
        $data_to_join   = array();
        $alias_table    = array();
        $alias_class    = array();
        if( $this->_table_alias !== null ){
            $alias_table[ $this->_table_alias ] = $this->_item_model;
            $alias_class[ $this->_item_model ]  = $this->_table_alias;
        }

        foreach( $this->_join_sources as $index=>$join_source ){

            $to_join = $this->normalizeInput($join_source["join"]);
            // if is an alias -> resolve it !
            $resolved_alias = $this->resolve_alias($to_join["model"]);
            if( $resolved_alias !== null ){
                $alias_table[ $to_join["model"] ]   = $resolved_alias;
                $alias_class[ $resolved_alias ]     = $to_join["model"];
                $to_join["alias"]  = $to_join["model"];
                $to_join["model"]  = $resolved_alias;
            }

            if( $this->isAModel($to_join["model"]) === false ){
                if( $to_join["property"] === null ){
                    if( count($join_source["on"]) === 0 ){
                        $models = $this->findModelsForAProperty($to_join["model"]);
                        if( count($models) > 0 ){
                            $property = $this->reversedTarget($models[0]["model"], $models[0]["property"]);

                            $model_alias     = $this->get_alias_of_a_model($models[0]["model"]);
                            if( $model_alias !== null ){
                                $right = $model_alias.".".$models[0]["property"];
                            }else{
                                $right = $models[0]["model"].".".$models[0]["property"];
                            }

                            if( $join_source["as"] !== null ){
                                $left = $join_source["as"].".".$property["property"];
                            }else{
                                $left = $property["model"].".".$property["property"];
                            }
                            $join_source["on"][] = array(
                                "left"      =>$left,
                                "operator"  =>"on",
                                "right"     =>$right,
                            );
                            $to_join["model"]       = $property["model"];
                            $to_join["property"]    = null;
                        }else{
                            // INCONNU !!!
                        }
                    }elseif( count($join_source["on"]) > 0 ){
                        // c'est bizarre....
                        if( $join_source["on"][0]["right"] !== null ){
                            // c'est étrange !!!
                            // let's assume this is a property
                            $models = $this->findModelsForAProperty($to_join["model"]);
                            if( count($models) > 0 ){
                                $property               = $this->reversedTarget($models[0]["model"], $models[0]["property"]);
                                $to_join["model"]       = $property["model"];
                                $to_join["property"]    = null;
                            }else{
                                // INCONNU !!!
                            }
                        }else{
                            $models = $this->findModelsForAProperty($to_join["model"]);
                            if( count($models) > 0 ){
                                $property = $this->reversedTarget($models[0]["model"], $models[0]["property"]);
                                $join_source["on"][0]["right"] = $models[0]["model"].".".$models[0]["property"];
                                $to_join["model"]       = $property["model"];
                                $to_join["property"]    = null;
                            }else{
                                // INCONNU !!!
                            }
                        }
                    }
                }else{

                    if( count($join_source["on"]) === 0 ){
                        $property = $this->reversedTarget($to_join["model"], $to_join["property"]);

                        if( $to_join["alias"] !== null ){
                            $right = $to_join["alias"].".".$to_join["property"];
                        }else{
                            $right = $to_join["model"].".".$to_join["property"];
                        }

                        $join_source["on"][] = array(
                            "left"      =>$property["model"].".".$property["property"],
                            "operator"  =>"on",
                            "right"     =>$right,
                        );
                        $to_join["model"]       = $property["model"];
                        $to_join["property"]    = null;

                    }elseif( count($join_source["on"]) > 0 ){
                        /*
                        // c'est bizarre....
                        if( $join_source["on"][0]["right"] !== null ){
                            // c'est étrange !!!
                            // let's assume this is a property
                            $models = $this->findModelsForAProperty($to_join["model"]);
                            if( count($models) > 0 ){
                                $property               = $this->reversedTarget($models[0]["model"], $models[0]["property"]);
                                $to_join["model"]       = $property["model"];
                                $to_join["property"]    = null;
                            }else{
                                // INCONNU !!!
                            }
                        }else{
                            $models = $this->findModelsForAProperty($to_join["model"]);
                            if( count($models) > 0 ){
                                $property = $this->reversedTarget($models[0]["model"], $models[0]["property"]);
                                $join_source["on"][0]["right"] = $models[0]["model"].".".$models[0]["property"];
                                $to_join["model"]       = $property["model"];
                                $to_join["property"]    = null;
                            }else{
                                // INCONNU !!!
                            }
                        }*/
                    }

                }
            }elseif($to_join["property"] !== null ){
                $this->loadModelInfos($to_join["model"]);
                if( $this->isHasOne($to_join["model"], $to_join["property"]) ){
                    if( count($join_source["on"]) === 0 ){
                        //-
                        $right_join     = $this->getClassTypeOfAProperty($to_join["model"], $to_join["property"]);
                        $left_join      = $to_join["model"].".".$to_join["property"];
                        $to_join["model"]       = $right_join;
                        $to_join["property"]    = null;
                        $join_source["on"][] = array(
                            "left"      =>$left_join,
                            "operator"  =>"on",
                            "right"     =>$right_join,
                        );
                    }
                }elseif(  $this->isHasMany($to_join["model"], $to_join["property"]) ){
                    $property = $this->reversedTarget($to_join["model"], $to_join["property"]);
                    $to_join["model"]       = $property["model"];
                    $to_join["property"]    = null;

                }elseif(  $this->isHasManyToMany($to_join["model"], $to_join["property"]) ){
                    $property = $this->reversedTarget($to_join["model"], $to_join["property"]);
                    $to_join["model"]       = $property["model"];
                    $to_join["property"]    = null;

                }else{
                }
            }

            if( $this->isAModel($to_join["model"]) ){
                $this->loadModelInfos($to_join["model"]);
                $to_join["as"]      = $join_source["as"];
                $to_join["table"]   = $this->getTableOfAModel($to_join["model"]);
            }else{
                $to_join["as"]      = $join_source["as"];
                $to_join["table"]   = $to_join["model"];
            }

            if( $to_join["as"] !== null ){
                $alias_table[ $to_join["as"] ] = $to_join["model"];
                $alias_class[ $to_join["model"] ] = $to_join["as"];
            }

            $curr_data_to_join  = array(
                "join"  =>$to_join,
                "on"    =>array(),
                "type"  =>$join_source["type"],
            );


            if( count($join_source["on"]) > 0 ){
                foreach( $join_source["on"] as $on ){
                    $join_on_left   = $this->normalizeInput($on["left"]);

                    if( isset($alias_table[ $join_on_left["model"] ]) ){
                        $join_on_left["as"]     = $join_on_left["model"];
                        $join_on_left["model"]  = $alias_table[ $join_on_left["model"] ];
                    }elseif( isset($alias_class[ $join_on_left["model"] ]) ){
                        $join_on_left["as"]    = $alias_class[ $join_on_left["model"] ];
                    }
                    /*
                    $resolved_alias = $this->resolve_alias($join_on_left["model"]);
                    if( $resolved_alias !== null ){
                        $join_on_left["as"]     = $join_on_left["model"];
                        $join_on_left["model"]  = $resolved_alias;
                    }*/

                    if( $this->isAModel($join_on_left["model"]) ){
                        $join_on_left["table"]  = $this->getTableOfAModel($join_on_left["model"]);
                    }else{
                        $join_on_left["table"]  = $join_on_left["model"];
                    }

                    if( $join_on_left["property"] === null ){
                        $r = $this->findPropertiesForAModel($to_join["model"]); // @todo to check because i think this is not sufficient
                        $join_on_left["property"] = $r[0]["property"];
                    }

                    if( $on["right"] === null ){
                        if( $this->isHasOne($join_on_left["model"], $join_on_left["property"]) ){
                            $join_on_right              = array();
                            $join_on_right["model"]     = $this->getClassTypeOfAProperty($join_on_left["model"], $join_on_left["property"]);
                            $join_on_right["property"]  = null;
                            $join_on_right["as"]        = null;

                            if( isset($alias_class[ $join_on_right["model"] ]) ){
                                $join_on_right["as"]     = $alias_class[ $join_on_right["model"] ];
                            }
                            $join_on_right["table"]  = $this->getTableOfAModel($join_on_right["model"]);
                        }else{
                            $join_on_right          = $this->reversedTarget($join_on_left["model"], $join_on_left["property"]);
                            $join_on_right["as"]    = null;

                            if( isset($alias_class[ $join_on_right["model"] ]) ){
                                $join_on_right["as"]     = $alias_class[ $join_on_right["model"] ];
                            }

                            /*
                            $resolved_alias         = $this->resolve_alias($join_on_right["model"]);
                            if( $resolved_alias !== null ){
                                $join_on_right["as"]     = $join_on_right["model"];
                                $join_on_right["model"]  = $resolved_alias;
                            }*/
                            $join_on_right["table"]  = $this->getTableOfAModel($join_on_right["model"]);
                        }

                    }else{
                        $join_on_right  = $this->normalizeInput($on["right"]);

                        if( isset($alias_table[ $join_on_right["model"] ]) ){
                            $join_on_right["as"]    = $join_on_right[ "model" ];
                            $join_on_right["model"] = $alias_table[ $join_on_right["model"] ];
                        }elseif( isset($alias_class[ $join_on_right["model"] ]) ){
                            $join_on_right["as"]    = $alias_class[ $join_on_right["model"] ];
                        }
                        $this->loadModelInfos($join_on_right["model"]);
                        /*
                        $resolved_alias = $this->resolve_alias($join_on_right["model"]);
                        if( $resolved_alias !== null ){
                            $join_on_right["as"]     = $join_on_right["model"];
                            $join_on_right["model"]  = $resolved_alias;
                        }*/

                        $join_on_right["table"]  = $this->getTableOfAModel($join_on_right["model"]);
                    }

                    if( $join_on_right["property"] === null ){
                        if( $this->isHasOne($join_on_left["model"], $join_on_left["property"]) ){
                            $assoc = "one_to_one";
                        }elseif( $this->isHasMany($join_on_left["model"], $join_on_left["property"]) ){
                            $assoc = "many_to_one";
                        }elseif( $this->isHasManyToMany($join_on_left["model"], $join_on_left["property"]) ){
                            $assoc = "many_to_many";
                        }
                    }else{
                        $assoc = $this->getTypeOfAssociation($join_on_left, $join_on_right);
                        if( $assoc === null ){
                            $assoc = "raw";
                            if( $this->isAModel($join_on_left["model"]) === false ){
                                $join_on_left["table"] = $join_on_left["model"];
                            }
                            if( $this->isAModel($join_on_right["model"]) === false ){
                                $join_on_right["table"] = $join_on_right["model"];
                            }
                        }
                    }

                    $curr_data_to_join["on"][] = array(
                        "left"      =>$join_on_left,
                        "right"     =>$join_on_right,
                        "assoc"     =>$assoc,
                        "operator"  =>$on["operator"],
                        "keys"      => array(),
                    );
                }
            }else{

                if($to_join["model"] !== null && $to_join["property"] !== null ){
                    //
                    if( $this->isHasMany($to_join["model"], $to_join["property"]) ){
                        $left_property  = $to_join;
                        $right_property = $this->reversedTarget($left_property["model"], $left_property["property"]);
                        $this->loadModelInfos($right_property["model"]);

                        $left_property["table"]     = $this->getTableOfAModel($left_property["model"]);
                        $left_property["as"]        = null;
                        $left_property["alias"]     = null;
                        if( isset($alias_class[ $left_property["model"] ]) ){
                            $left_property["as"]     = $alias_class[ $left_property["model"] ];
                        }

                        $right_property["table"]    = $this->getTableOfAModel($right_property["model"]);
                        $right_property["as"]       = null;
                        $right_property["alias"]    = null;
                        if( isset($alias_class[ $right_property["model"] ]) ){
                            $right_property["as"]     = $alias_class[ $right_property["model"] ];
                        }

                        $curr_data_to_join["on"][] = array(
                            "left"      =>$left_property,
                            "right"     =>$right_property,
                            "assoc"     =>$this->getTypeOfAssociation($left_property, $right_property),
                            "operator"  =>"on",
                            "keys"      => array(),
                        );
                    }elseif( $this->isHasManyToMany($to_join["model"], $to_join["property"]) ){
                        $left_property  = $to_join;
                        $right_property = $this->reversedTarget($left_property["model"], $left_property["property"]);
                        $this->loadModelInfos($right_property["model"]);

                        $left_property["table"]     = $this->getTableOfAModel($left_property["model"]);
                        $left_property["as"]        = null;
                        $left_property["alias"]     = null;
                        if( isset($alias_class[ $left_property["model"] ]) ){
                            $left_property["as"]     = $alias_class[ $left_property["model"] ];
                        }

                        $right_property["table"]    = $this->getTableOfAModel($right_property["model"]);
                        $right_property["as"]       = null;
                        $right_property["alias"]    = null;
                        if( isset($alias_class[ $right_property["model"] ]) ){
                            $right_property["as"]     = $alias_class[ $right_property["model"] ];
                        }

                        $curr_data_to_join["on"][] = array(
                            "left"      =>$left_property,
                            "right"     =>$right_property,
                            "assoc"     =>"many_to_many",
                            "operator"  =>"on",
                            "keys"      => array(),
                        );
                    }else{
                        throw new InvalidBuildException("Unknown property {$to_join["property"]} on model {$to_join["model"]}");
                    }



                }elseif( $this->isAModel($to_join["model"]) ){
                    $properties     = $this->findPropertiesForAModel($to_join["model"]);
                    $left_property  = $properties[0];
                    $right_property = $this->reversedTarget($left_property["model"], $left_property["property"]);

                    $left_property["table"]     = $this->getTableOfAModel($left_property["model"]);
                    $left_property["as"]        = null;
                    $left_property["alias"]     = null;
                    if( isset($alias_class[ $left_property["model"] ]) ){
                        $left_property["as"]     = $alias_class[ $left_property["model"] ];
                    }

                    $right_property["table"]    = $this->getTableOfAModel($right_property["model"]);
                    $right_property["as"]       = null;
                    $right_property["alias"]    = null;
                    if( isset($alias_class[ $right_property["model"] ]) ){
                        $right_property["as"]     = $alias_class[ $right_property["model"] ];
                    }

                    $curr_data_to_join["on"][] = array(
                        "left"      =>$left_property,
                        "right"     =>$right_property,
                        "assoc"     =>$this->getTypeOfAssociation($left_property, $right_property),
                        "operator"  =>"on",
                        "keys"      => array(),
                    );
                }else{
                }
            }
            $data_to_join[] = $curr_data_to_join;
        }

        foreach( $data_to_join as $index =>$to_join ){
            foreach( $to_join["on"] as $i => $on ){
                if( $on["assoc"] === "one_to_many" ){
                    $join_on_left   = $on["left"];
                    $join_on_right  = $on["right"];

                    $fks           = $this->getFksOfAProperty($join_on_left["model"], $join_on_left["property"]);
                    $right_table   = $join_on_left["as"]===null?$join_on_left["table"]:$join_on_left["as"];
                    $left_table    = $join_on_right["as"]===null?$join_on_right["table"]:$join_on_right["as"];
                    $keys_on = array();
                    foreach( $fks as $left_key => $right_key )
                        $keys_on[ $left_table.".".$left_key ] = $right_table.".".$right_key;

                    $data_to_join[$index]["on"][$i]["keys"] = $keys_on;

                }elseif( $on["assoc"] === "many_to_one" ){
                    $join_on_left   = $on["left"];
                    $join_on_right  = $on["right"];

                    $fks            = $this->getFksOfAProperty($join_on_right["model"], $join_on_right["property"]);
                    $left_table     = $join_on_left["as"]===null?$join_on_left["table"]:$join_on_left["as"];
                    $right_table    = $join_on_right["as"]===null?$join_on_right["table"]:$join_on_right["as"];
                    $keys_on = array();
                    foreach( $fks as $left_key => $right_key )
                        $keys_on[ $left_table.".".$left_key ] = $right_table.".".$right_key;

                    $data_to_join[$index]["on"][$i]["keys"] = $keys_on;

                }elseif( $on["assoc"] === "many_to_many" ){
                    $join_on_left   = $on["left"];
                    $join_on_right  = $on["right"];
                    $left_table     = $join_on_left["as"]===null?$join_on_left["table"]:$join_on_left["as"];
                    $right_table    = $join_on_right["as"]===null?$join_on_right["table"]:$join_on_right["as"];
                    $middle_table   = $this->getMiddleTableOfAManyToMany($join_on_left["model"], $join_on_left["property"]);

                    if( $to_join["join"]["model"] === $join_on_left["model"] ){
                        $fks = $this->getLeftKeysOfManyToMany($join_on_right["model"], $join_on_right["property"]);
                        $mid_keys_on = array();
                        foreach( $fks as $left_key => $right_key )
                            $mid_keys_on[ $middle_table.".".$left_key ] = $right_table.".".$right_key;


                        $fks = $this->getLeftKeysOfManyToMany($join_on_left["model"], $join_on_left["property"]);
                        $keys_on = array();
                        foreach( $fks as $left_key => $right_key )
                            $keys_on[ $middle_table.".".$left_key ] = $left_table.".".$right_key;
                    }else{
                        $fks = $this->getLeftKeysOfManyToMany($join_on_left["model"], $join_on_left["property"]);
                        $mid_keys_on = array();
                        foreach( $fks as $left_key => $right_key )
                            $mid_keys_on[ $middle_table.".".$left_key ] = $left_table.".".$right_key;


                        $fks = $this->getLeftKeysOfManyToMany($join_on_right["model"], $join_on_right["property"]);
                        $keys_on = array();
                        foreach( $fks as $left_key => $right_key )
                            $keys_on[ $middle_table.".".$left_key ] = $right_table.".".$right_key;
                    }

                    /**
                     * shared fields to select
                     */
                    if( isset($this->_models_infos[ $this->_item_model ]["table_infos"]["rel_tables"][$middle_table]["shared_fields"]) ){
                        $shared_fields = $this->_models_infos[ $this->_item_model ]["table_infos"]["rel_tables"][$middle_table]["shared_fields"];
                        if( count($shared_fields) > 0 ){
                            if( $this->_using_default_result_columns ){
                                $t = $this->getTableOfAModel($this->_item_model);
                                $this->select($t.".*");
                            }
                            foreach( $shared_fields as $shared_field=>$s ){
                                $this->select($middle_table.".".$shared_field);
                            }
                        }
                    }
                    /**
                     * shared fields to select
                     */


                    $data_to_join[$index]["on"][$i]["keys"]         = $keys_on;
                    $data_to_join[$index]["on"][$i]["middle"]       = $middle_table;
                    $data_to_join[$index]["on"][$i]["middle_keys"]  = $mid_keys_on;

                }elseif( $on["assoc"] === "one_to_one" ){
                    $join_on_left   = $on["left"];
                    $join_on_right  = $on["right"];

                    if( $join_on_left["property"] !== null ){
                        $fks = $this->getFksOfAProperty($join_on_left["model"], $join_on_left["property"]);
                        $left_table     = $join_on_right["as"]===null?$join_on_right["table"]:$join_on_right["as"];
                        $right_table    = $join_on_left["as"]===null?$join_on_left["table"]:$join_on_left["as"];
                    }elseif( $join_on_right["property"] !== null ){
                        $fks = $this->getFksOfAProperty($join_on_right["model"], $join_on_right["property"]);
                        $left_table     = $join_on_left["as"]===null?$join_on_left["table"]:$join_on_left["as"];
                        $right_table    = $join_on_right["as"]===null?$join_on_right["table"]:$join_on_right["as"];
                    }else{

                    }
                    $keys_on = array();
                    foreach( $fks as $left_key => $right_key )
                        $keys_on[ $left_table.".".$left_key ] = $right_table.".".$right_key;

                    $data_to_join[$index]["on"][$i]["keys"] = $keys_on;
                }else{
                    $join_on_left   = $on["left"];
                    $join_on_right  = $on["right"];

                    $left_table     = $join_on_left["as"]===null?$join_on_left["table"]:$join_on_left["as"];
                    $right_table    = $join_on_right["as"]===null?$join_on_right["table"]:$join_on_right["as"];

                    $keys_on = array();
                    $keys_on[ $left_table.".".$join_on_left["property"] ] = $right_table.".".$join_on_right["property"];

                    $data_to_join[$index]["on"][$i]["keys"] = $keys_on;
                }
            }
        }


        foreach ($data_to_join as $index=>$join) {
            foreach( $join["on"] as $i=>$on ){
                if( $i === 0 ){
                    if( isset($on["middle"]) ){
                        $this->_builder->inner_join( $on["middle"] );
                        foreach( $on["middle_keys"] as $left => $right ){
                            $this->_builder
                                ->on_equal( $left, $right );
                        }
                    }
                    $this->_builder->{$join["type"]}( $join["join"]["table"], $join["join"]["as"] );
                }
                foreach( $on["keys"] as $left => $right ){
                    $this->_builder
                        ->{$on["operator"]}( $left, $right );
                }
            }
        }
    }

    public function resolve_wheres(){
        foreach( $this->_where_conditions as $index => $where_condition ){
            $r = $this->normalizeInput($where_condition["what"]);
            if( $r["property"] === null ){
                if( $this->isAModel($r["model"]) === false ){
                    $r["property"]  = $r["model"];
                    $r["model"]     = null;
                    $properties = $this->findModelsForAProperty($r["property"]);
                    if( count($properties) > 0 ){
                        $r["model"] = $properties[0]["model"];
                    }else{
                        //-
                    }
                }
            }

            if( $this->isAModel($r["model"]) ){
                $r["table"] = $this->getTableOfAModel($r["model"]);
            }

            $this->_builder->{$where_condition["operator"]}($r["table"].".".$r["property"], $where_condition["value"]);
        }
    }

    public function resolve_order_by(){
        foreach( $this->_order_by as $o ){
            $this->_builder->order_by_asc($o["column"],$o["direction"]);
        }
    }

    public function resolve_group_by(){
        foreach( $this->_group_by as $o ){
            $this->_builder->group_by($o);
        }
    }

    public function build(){
        $table_name     = $this->_models_infos[$this->_item_model]["table_infos"]["table"]["name"];

        $this->_builder = $this->_active_query_builder->select($table_name);

        $this->_builder->table_alias($this->_table_alias);

        $this->resolve_joins();
        $this->resolve_wheres();
        $this->resolve_order_by();
        $this->resolve_group_by();

        if( ! $this->_using_default_result_columns ){
            foreach( $this->_result_columns as $i => $res ){
                if( $res["type"] === "column" ){
                    $this->_builder->select($res["expr"], $res["as"]);
                }elseif( $res["type"] === "expr" ){
                    $this->_builder->select_expr($res["expr"], $res["as"]);
                }elseif( $res["type"] === "count" ){
                    $this->_builder->count($res["expr"], $res["as"]);
                }
            }
        }

        $this->_builder->limit($this->_limit);
        $this->_builder->offset($this->_offset);


        $retour = $this->_builder->build();
        return $retour;
    }

    public function _print(){
        $this->build();
        return $this->_builder->_print();
    }

    public function compile(){
        $this->build();
        return $this->_builder->compile();
    }

    public function execute(){
        return $this->_builder->execute();
    }

    public function find_many(){
        $retour = new $this->_list_model();
        foreach( $this->execute() as $r ){
            $o = new $this->_item_model();
            $o->_values->exchangeArray( $r );
            $retour->append( $o );
        }
        return $retour;
    }

    public function find_one(){
        $o = null;
        $r = $this->execute();
        if( isset($r[0]) ){
            $o = new $this->_item_model();
            $o->_values->exchangeArray( $r[0] );
        }
        return $o;
    }

    public function iterate(){
        $next_result = $this->execute();
        if( $next_result === false ){
            return false;
        }
        $o = new $this->_item_model();
        $o->_values->exchangeArray( $next_result );
        return $o;
    }

}

class ActiveModelInsertBuilder{
    /**
     *
     * @var InsertBuilder
     */
    protected $_builder;


    public function __construct( ActiveInsertBuilder $builder ){
        $this->_builder = $builder;
    }

    public function fields( $fields, $values=null ){
        $this->_builder->fields($fields);
        return $this;
    }

    public function build(){
        return $this->_builder->build();
    }

    public function _print(){
        return $this->_builder->_print();
    }

    public function compile(){
        return $this->_builder->compile();
    }

    public function execute(){
        return $this->_builder->execute();
    }


}

class ActiveModelReplaceBuilder extends ActiveModelInsertBuilder{

    public function __construct( ActiveReplaceBuilder $builder ){
        $this->_builder = $builder;
    }
}

class ActiveModelUpdateBuilder{
    /**
     *
     * @var UpdateBuilder
     */
    protected $_builder;

    public function __construct( ActiveUpdateBuilder $builder ){
        $this->_builder = $builder;
    }

    /**
     * Perform a raw query. The query should contain placeholders,
     * in either named or question mark style, and the parameters
     * should be an array of values which will be bound to the
     * placeholders in the query. If this method is called, all
     * other query building methods will be ignored.
     */
    public function raw_query($query, $values=null) {
        $this->_builder->raw_query($query);
        return $this;
    }


    public function fields( $fields, $values ){
        $this->_builder->fields($fields);
        return $this;
    }

    /**
     * Add a WHERE column = value clause to your query. Each time
     * this is called in the chain, an additional WHERE will be
     * added, and these will be ANDed together when the final query
     * is built.
     */
    public function where($column_name, $value) {
        $this->_builder->where($column_name);
        return $this;
    }

    /**
     * More explicitly named version of for the where() method.
     * Can be used if preferred.
     */
    public function where_equal($column_name, $value) {
        $this->_builder->where_equal($column_name);
        return $this;
    }

    /**
     * Add a WHERE column != value clause to your query.
     */
    public function where_not_equal($column_name, $value) {
        $this->_builder->where_not_equal($column_name);
        return $this;
    }

    /**
     * Add a WHERE ... LIKE clause to your query.
     */
    public function where_like($column_name, $value) {
        $this->_builder->where_like($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add where WHERE ... NOT LIKE clause to your query.
     */
    public function where_not_like($column_name, $value) {
        $this->_builder->where_not_like($column_name);
        return $this;
    }

    /**
     * Add a WHERE ... > clause to your query
     */
    public function where_gt($column_name, $value) {
        $this->_builder->where_gt($column_name);
        return $this;
    }

    /**
     * Add a WHERE ... < clause to your query
     */
    public function where_lt($column_name, $value) {
        $this->_builder->where_lt($column_name);
        return $this;
    }

    /**
     * Add a WHERE ... >= clause to your query
     */
    public function where_gte($column_name, $value) {
        $this->_builder->where_gte($column_name);
        return $this;
    }

    /**
     * Add a WHERE ... <= clause to your query
     */
    public function where_lte($column_name, $value) {
        $this->_builder->where_lte($column_name);
        return $this;
    }

    /**
     * Add a WHERE ... IN clause to your query
     */
    public function where_in($column_name, $values) {
        $this->_builder->where_in($column_name, count($values));
        return $this;
    }

    /**
     * Add a WHERE ... NOT IN clause to your query
     */
    public function where_not_in($column_name, $values_count) {
        $this->_builder->where_not_in($column_name, count($values_count));
        return $this;
    }

    /**
     * Add a WHERE column IS NULL clause to your query
     *
     * @param $column_name
     * @return ActiveModelUpdateBuilder
     */
    public function where_null($column_name) {
        $this->_builder->where_null($column_name);
        return $this;
    }

    /**
     * Add a WHERE column IS NOT NULL clause to your query
     *
     * @param $column_name
     * @return ActiveModelUpdateBuilder
     */
    public function where_not_null($column_name) {
        $this->_builder->where_not_null($column_name);
        return $this;
    }

    /**
     * Add a raw WHERE clause to the query. The clause should
     * contain question mark placeholders, which will be bound
     * to the parameters supplied in the second argument.
     *
     * @param $clause
     * @param $values
     * @return ActiveModelUpdateBuilder
     */
    public function where_raw($clause, $values) {
        $this->_builder->where_raw($clause);
        return $this;
    }

    /**
     * Add a LIMIT to the query
     *
     * @param $limit
     * @return ActiveModelUpdateBuilder
     */
    public function limit($limit) {
        $this->_builder->limit($limit);
        return $this;
    }

    /**
     * Add an OFFSET to the query
     */
    public function offset($offset) {
        $this->_builder->offset($offset);
        return $this;
    }

    public function build(){
        return $this->_builder->build();
    }

    public function _print(){
        return $this->_builder->_print();
    }

    public function compile(){
        return $this->_builder->compile();
    }

    public function execute(){
        return $this->_builder->execute();
    }
}

class ActiveModelDeleteBuilder{
    /**
     *
     * @var ActiveDeleteBuilder
     */
    protected $_builder;

    public function __construct( ActiveDeleteBuilder $builder ){
        $this->_builder = $builder;
    }

    /**
     * Perform a raw query. The query should contain placeholders,
     * in either named or question mark style, and the parameters
     * should be an array of values which will be bound to the
     * placeholders in the query. If this method is called, all
     * other query building methods will be ignored.
     */
    public function raw_query($query, $values=null) {
        $this->_builder->raw_query($query);
        return $this;
    }


    /**
     * Add a WHERE column = value clause to your query. Each time
     * this is called in the chain, an additional WHERE will be
     * added, and these will be ANDed together when the final query
     * is built.
     */
    public function where($column_name, $value) {
        $this->_builder->where($column_name, $value);
        return $this;
    }

    /**
     * More explicitly named version of for the where() method.
     * Can be used if preferred.
     */
    public function where_equal($column_name, $value) {
        $this->_builder->where_equal($column_name, $value);
        return $this;
    }

    /**
     * Add a WHERE column != value clause to your query.
     */
    public function where_not_equal($column_name, $value) {
        $this->_builder->where_not_equal($column_name, $value);
        return $this;
    }

    /**
     * Add a WHERE ... LIKE clause to your query.
     */
    public function where_like($column_name, $value) {
        $this->_builder->where_like($column_name, $value);
        return $this;
    }

    /**
     * Add where WHERE ... NOT LIKE clause to your query.
     */
    public function where_not_like($column_name, $value) {
        $this->_builder->where_not_like($column_name, $value);
        return $this;
    }

    /**
     * Add a WHERE ... > clause to your query
     */
    public function where_gt($column_name, $value) {
        $this->_builder->where_gt($column_name, $value);
        return $this;
    }

    /**
     * Add a WHERE ... < clause to your query
     */
    public function where_lt($column_name, $value) {
        $this->_builder->where_lt($column_name, $value);
        return $this;
    }

    /**
     * Add a WHERE ... >= clause to your query
     */
    public function where_gte($column_name, $value) {
        $this->_builder->where_gte($column_name, $value);
        return $this;
    }

    /**
     * Add a WHERE ... <= clause to your query
     */
    public function where_lte($column_name, $value) {
        $this->_builder->where_lte($column_name, $value);
        return $this;
    }

    /**
     * Add a WHERE ... IN clause to your query
     */
    public function where_in($column_name, $values) {
        $this->_builder->where_in($column_name, count($values));
        return $this;
    }

    /**
     * Add a WHERE ... NOT IN clause to your query
     */
    public function where_not_in($column_name, $values_count) {
        $this->_builder->where_not_in($column_name, count($values_count));
        return $this;
    }

    /**
     * Add a WHERE column IS NULL clause to your query
     */
    public function where_null($column_name) {
        $this->_builder->where_null($column_name);
        return $this;
    }

    /**
     * Add a WHERE column IS NOT NULL clause to your query
     */
    public function where_not_null($column_name) {
        $this->_builder->where_not_null($column_name);
        return $this;
    }

    /**
     * Add a raw WHERE clause to the query. The clause should
     * contain question mark placeholders, which will be bound
     * to the parameters supplied in the second argument.
     */
    public function where_raw($clause, $values) {
        $this->_builder->where_raw($clause, $values);
        return $this;
    }

    /**
     * Add a LIMIT to the query
     */
    public function limit($limit) {
        $this->_builder->limit($limit);
        return $this;
    }

    /**
     * Add an OFFSET to the query
     */
    public function offset($offset) {
        $this->_builder->offset($offset);
        return $this;
    }

    public function build(){
        return $this->_builder->build();
    }

    public function _print(){
        return $this->_builder->_print();
    }
    public function compile(){
        return $this->_builder->compile();
    }

    public function execute(){
        return $this->_builder->execute();
    }
}
