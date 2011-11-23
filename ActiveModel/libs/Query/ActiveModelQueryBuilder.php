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

class ActiveModelSelectBuilder{

    /**
     *
     * @var SelectBuilder
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
                                        "on"        =>null);
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
        $this->_join_sources[$i-1]["on"] = array(   "left"=>$col_left,
                                                    "operator"=>"on",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_egual($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"] = array(   "left"=>$col_left,
                                                    "operator"=>"on_egual",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_not_equal($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"] = array(   "left"=>$col_left,
                                                    "operator"=>"on_not_equal",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_like($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"] = array(   "left"=>$col_left,
                                                    "operator"=>"on_like",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_not_like($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"] = array(   "left"=>$col_left,
                                                    "operator"=>"on_not_like",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_gt($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"] = array(   "left"=>$col_left,
                                                    "operator"=>"on_gt",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_lt($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"] = array(   "left"=>$col_left,
                                                    "operator"=>"on_lt",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_gte($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"] = array(   "left"=>$col_left,
                                                    "operator"=>"on_gte",
                                                    "right"=>$col_right,
                                                );
        return $this;
    }
    public function on_lte($col_left, $col_right) {
        $i = count($this->_join_sources);
        $this->_join_sources[$i-1]["on"] = array(   "left"=>$col_left,
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



    protected function normalizeInputParameterTarget( $input_target ){
        $info = explode(".", $input_target);
        if( trim($input_target) === "" ){
            $retour = array(
                "model"     =>null,
                "property"  =>null,
                "alias"     =>null,
                "table"     =>null,
            );
        }else{
            $info = explode(".", $input_target);
            if( count($info) > 1 ){
                $resolved_alias     = $this->resolve_alias($info[0]);
                if( $resolved_alias === null ){
                    if( $this->isAModel($info[0]) ){
                        $retour = array(
                            "model"     =>$info[0],
                            "property"  =>$info[1],
                            "alias"     =>null,
                            "table"     =>null,
                        );
                    }else{
                        $retour = array(
                            "model"     =>null,
                            "property"  =>$info[1],
                            "alias"     =>null,
                            "table"     =>$info[0],
                        );
                    }
                }else{
                    $retour = array(
                        "model"     =>$resolved_alias,
                        "property"  =>$info[1],
                        "alias"     =>$info[0],
                        "table"     =>null,
                    );
                }
            }else{
                $resolved_alias     = $this->resolve_alias($info[0]);
                if( $resolved_alias === null ){
                    if( $this->isAModel($info[0]) ){
                        $retour = array(
                            "model"     =>$info[0],
                            "property"  =>null,
                            "alias"     =>null,
                            "table"     =>null,
                        );
                    }else{
                        $models = $this->findModelsForAProperty($info[0]);
                        if( count($models) > 0 ){
                            $retour = array(
                                "model"     =>$models[0]["model"],
                                "property"  =>$info[0],
                                "alias"     =>null,
                                "table"     =>null,
                            );
                        }else{
                            $retour = array(
                                "model"     =>null,
                                "property"  =>null,
                                "alias"     =>null,
                                "table"     =>$info[0],
                            );
                        }
                    }
                }else{
                    if( $this->isAModel($resolved_alias) ){
                        $retour = array(
                            "model"     =>$resolved_alias,
                            "property"  =>null,
                            "alias"     =>$info[0],
                            "table"     =>null,
                        );
                    }else{
                        $retour = array(
                            "model"     =>null,
                            "property"  =>null,
                            "alias"     =>null,
                            "table"     =>$info[0],
                        );
                    }
                }
            }
        }

        return $retour;
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

    protected function geLeftKeysOfManyToMany( $model, $property ){
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
        return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["type"] === "has_many";
    }

    protected function isHasOne( $model, $property ){
        return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["type"] === "has_one";
    }

    protected function isHasManyToMany( $model, $property ){
        return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][ $property ]["type"] === "has_many_to_many";
    }

    protected function getTarget( $model, $property ){
        return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][$property]["target"];
    }

    protected function getClassTypeOfAProperty( $model, $property ){
        return $this->_models_infos[$model]["model_infos"]["virutal_prop_infos"][$property]["class"];
    }

    protected function isManyToMany( $from_model, $to_model, $to_property ){
        foreach($this->_models_infos[$from_model]["model_infos"]["virutal_prop_infos"] as $from_v_propinfos ){
            if( $from_v_propinfos["class"]===$to_model && $from_v_propinfos["type"]==='has_many_to_many' ){
                $to_v_propinfos = $this->_models_infos[$to_model]["model_infos"]["virutal_prop_infos"][$to_property];
                if( $to_v_propinfos["class"]===$from_model && $to_v_propinfos["type"]==='has_many_to_many' ){
                    return true;
                }
            }
        }
        return false;
    }

    protected function isOneToMany( $from_model, $to_model, $to_property ){
        foreach($this->_models_infos[$from_model]["model_infos"]["virutal_prop_infos"] as $from_v_propinfos ){
            if( $from_v_propinfos["class"]===$to_model && $from_v_propinfos["type"]==='has_many' ){
                $to_v_propinfos = $this->_models_infos[$to_model]["model_infos"]["virutal_prop_infos"][$to_property];
                if( $to_v_propinfos["class"]===$from_model && $to_v_propinfos["type"]==='has_one' ){
                    return true;
                }
            }
        }
        return false;
    }

    protected function isManyToOne( $from_model, $to_model, $to_property ){
        foreach($this->_models_infos[$from_model]["model_infos"]["virutal_prop_infos"] as $from_v_propinfos ){
            if( $from_v_propinfos["class"]===$to_model && $from_v_propinfos["type"]==='has_one' ){
                $to_v_propinfos = $this->_models_infos[$to_model]["model_infos"]["virutal_prop_infos"][$to_property];
                if( $to_v_propinfos["class"]===$from_model && $to_v_propinfos["type"]==='has_many' ){
                    return true;
                }
            }
        }
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
                $retour[] = array(  "model"=>$model_name,
                                    "property"=>$searched_property_name);
            }
        }
        return $retour;
    }

    public function resolve_alias( $input_target ){
        if( $input_target === $this->_table_alias ){
            return $this->_item_model;
        }else{
            foreach( $this->_join_sources as $join ){
                if( $input_target === $join["as"] ){
                    return $join["model"];
                }
            }
        }
        return null;
    }

    public function resolve_implicit_join( $join_source ){
        if($join_source["model"] !== null
                && $join_source["property"] === null ){
            $models = $this->findPropertiesForAModel($join_source["model"]);
            if( count($models) < 1 ){

            }else{
                $model = $this->reversedTarget($models[0]["model"], $models[0]["property"]);
                return $model;
            }
        }elseif($join_source["model"] === null
                && $join_source["property"] !== null ){
            $models = $this->findModelsForAProperty($join_source["property"]);
            if( count($models) < 1 ){

            }else{
                $model = $this->reversedTarget($models[0]["model"], $models[0]["property"]);
                return $model;
            }
        }else{
            return $this->reversedTarget($join_source["model"], $join_source["property"]);
        }
    }

    public function resolve_joins(){
        foreach( $this->_join_sources as $join_source ){
            $to_join_infos  = $join_source["join"];
            $join_on_infos  = $join_source["on"];

            $to_join_as         = $join_source["as"];
            $join_type          = $join_source["type"];
            $join_on_operator   = isset($join_on_infos["operator"])?$join_on_infos["operator"]:"on";

            if( $join_on_infos === null ){
                $to_join_ = $this->normalizeInputParameterTarget($to_join_infos);
                $to_join = $this->resolve_implicit_join( $to_join_ );
                $to_join["alias"]   = $to_join_["alias"];

                $this->loadModelInfos ($to_join["model"]);

                $join_on = array(   "model"     => $this->getClassTypeOfAProperty($to_join["model"], $to_join["property"]),
                                    "property"  => $to_join_["property"],
                                    "alias"     => $join_source["as"]);
            }else{
                $join_on = $this->normalizeInputParameterTarget($join_on_infos["left"]);
                $to_join = $this->normalizeInputParameterTarget($to_join_infos);
                $to_join["alias"]   = $to_join_as;
                $this->loadModelInfos ($to_join["model"]);
            }
            $this->loadModelInfos ($join_on["model"]);

            if( $to_join["model"] === null ){
                $join_on_right = $this->normalizeInputParameterTarget($join_on_infos["right"]);

                $table_to_join = $to_join["table"];
                $table_left    = $join_on["alias"]===null?$this->getTableOfAModel($join_on["model"]):$join_on["alias"];
                $table_right   = $join_on_right["table"];

                $this->_builder->{$join_type}( $table_to_join, $to_join_as );
                $this->_builder
                    ->{$join_on_operator}(  $table_left.".".$join_on["property"],
                    $table_right.".".$join_on_right["property"]
                );

            }elseif( $this->isHasMany($to_join["model"], $to_join["property"]) ){

                if( $to_join["model"] === $join_on["model"] ){
                    $is_reversed        = false;
                    $to_join_           = $this->getTarget($to_join["model"], $to_join["property"]);
                    $to_join_["alias"]  = $to_join["alias"];
                    $to_join = $to_join_;
                    $this->loadModelInfos ($to_join["model"]);
                }else{
                    $is_reversed    = true;
                    $join_on_       = array(
                        "model"     =>$to_join["model"],
                        "property"  =>null,
                        "alias"     =>$join_on["alias"],
                    );

                    $to_join_ = $this->getTarget($to_join["model"], $to_join["property"]);
                    if( $join_on["property"] !== null )
                        $to_join_["property"] = $join_on["property"];
                    $to_join_["alias"] = $to_join["alias"];

                    $join_on = $join_on_;
                    $to_join = $to_join_;
                    $this->loadModelInfos ($join_on["model"]);
                }


                if( $is_reversed === false ){
                    $table_to_join  = $this->getTableOfAModel($to_join["model"]);
                    $table_on       = $this->getTableOfAModel($join_on["model"]);

                    $pk_table       = $join_on["alias"]===null?$table_on:$join_on["alias"];
                    $fk_table       = $to_join["alias"]===null?$table_to_join:$to_join["alias"];

                    $pks            = $this->getPksOfAModel($join_on["model"]);
                    $fks            = $this->getFksOfAProperty($to_join["model"], $to_join["property"]);
                }else{
                    $table_on       = $this->getTableOfAModel($to_join["model"]);
                    $table_to_join  = $this->getTableOfAModel($join_on["model"]);

                    $pk_table       = $join_on["alias"]===null?$table_to_join:$join_on["alias"];
                    $fk_table       = $to_join["alias"]===null?$table_on:$to_join["alias"];

                    $pks            = $this->getPksOfAModel($join_on["model"]);
                    $fks            = $this->getFksOfAProperty($to_join["model"], $to_join["property"]);
                }

                $this->_builder->{$join_type}( $table_to_join, $to_join_as );
                foreach( $pks as $pk=>$info ){
                    $this->_builder
                            ->{$join_on_operator}(  $fk_table.".".$fks[$pk],
                                                    $pk_table.".".$pk
                                                    );
                }

            }elseif( $this->isHasOne($to_join["model"], $to_join["property"]) ){
                $table_to_join  = $this->getTableOfAModel($to_join["model"]);
                $table_on       = $this->getTableOfAModel($join_on["model"]);

                $pk_table       = $join_on["alias"]===null?$table_on:$join_on["alias"];
                $fk_table       = $to_join["alias"]===null?$table_to_join:$to_join["alias"];


                $pks            = $this->getPksOfAModel($join_on["model"]);
                $fks            = $this->getFksOfAProperty($to_join["model"], $to_join["property"]);

                $this->_builder->{$join_type}( $table_to_join, $to_join_as );
                foreach( $pks as $pk=>$infos ){
                    $this->_builder
                            ->{$join_on_operator}(  $fk_table.".".$fks[$pk],
                                                    $pk_table.".".$pk
                                                    );
                }

            }elseif( $this->isHasManyToMany($to_join["model"], $to_join["property"]) ){

                $left_table_on          = $this->getTableOfAModel($to_join["model"]);
                $middle_table_to_join   = $this->getMiddleTableOfAManyToMany($to_join["model"], $to_join["property"]);
                $right_table_to_join    = $this->getTableOfAModel($join_on["model"]);


                $left_table_keys        = $this->geLeftKeysOfManyToMany($to_join["model"], $to_join["property"]);
                $right_table_keys       = $this->getRightKeysOfManyToMany($to_join["model"], $to_join["property"]);

                // resolve with and shared_with infos
                if( $this->_using_default_result_columns ){
                    if( isset($this->_models_infos[$this->_item_model]["table_infos"]["rel_tables"][$middle_table_to_join]["shared_fields"]) ){
                        $shared_fields = $this->_models_infos[$this->_item_model]["table_infos"]["rel_tables"][$middle_table_to_join]["shared_fields"];
                        if( count($shared_fields) > 0 ){
                            $this->select("$right_table_to_join.*", null);
                        }
                        foreach( $shared_fields as $shared_field => $y ){
                            $this->select($middle_table_to_join.".".$shared_field, null);
                        }
                    }
                }
                // resolve with and shared_with infos

                $this->_builder->inner_join( $middle_table_to_join );
                foreach( $right_table_keys as $fk=>$pk ){
                    $this->_builder
                            ->{$join_on_operator}(  $right_table_to_join.".".$pk,
                                                    $middle_table_to_join.".".$fk
                                                    );
                }
                $this->_builder->{$join_type}( $left_table_on );
                foreach( $left_table_keys as $fk=>$pk ){
                    $this->_builder
                            ->{$join_on_operator}(  $left_table_on.".".$pk,
                                                    $middle_table_to_join.".".$fk
                                                    );
                }

            }
        }
    }

    public function resolve_wheres(){

    }

    public function build(){
        $table_name     = $this->_models_infos[$this->_item_model]["table_infos"]["table"]["name"];

        $this->_builder = $this->_active_query_builder->select($table_name);

        $this->_builder->table_alias($this->_table_alias);

        $this->resolve_joins();
        $this->resolve_wheres();
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

        foreach( $this->_order_by as $o ){
            $this->_builder->order_by_asc($o["column"],$o["direction"]);
        }

        foreach( $this->_group_by as $o ){
            $this->_builder->group_by($o);
        }

        $retour = $this->_builder->build();
        return $retour;
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
