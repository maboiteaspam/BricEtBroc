<?php

class ActiveQueryBuilder{

    // The name of the table the current ORM instance is associated with
    protected $_identifier_quote_character;
    protected $cmpl_callback;
    protected $exec_callback;

    protected $query_builder;

    /**
     * @param QueryBuilder $builder
     */
    public function __construct( QueryBuilder $builder ) {
        $this->query_builder = $builder;
    }

    /**
     * @param $cmpl_callback
     * @param $exec_callback
     */
    public function setCallback( $cmpl_callback, $exec_callback ){
        $this->cmpl_callback = $cmpl_callback;
        $this->exec_callback = $exec_callback;
    }

    /**
     * @param $from
     * @return ActiveSelectBuilder
     */
    public function select($from){
        $retour = new ActiveSelectBuilder( $this->query_builder->select($from) );
        $retour->setCallback($this->cmpl_callback,$this->exec_callback);
        return $retour;
    }

    /**
     * @param $into
     * @return ActiveInsertBuilder
     */
    public function insert($into){
        $retour = new ActiveInsertBuilder( $this->query_builder->insert($into) );
        $retour->setCallback($this->cmpl_callback,$this->exec_callback);
        return $retour;
    }

    /**
     * @param $table
     * @return ActiveUpdateBuilder
     */
    public function update($table){
        $retour = new ActiveUpdateBuilder( $this->query_builder->update($table) );
        $retour->setCallback($this->cmpl_callback,$this->exec_callback);
        return $retour;
    }

    /**
     * @param $into
     * @return ActiveReplaceBuilder
     */
    public function replace($into){
        $retour = new ActiveReplaceBuilder( $this->query_builder->replace($into) );
        $retour->setCallback($this->cmpl_callback,$this->exec_callback);
        return $retour;
    }

    /**
     * @param $from
     * @return ActiveDeleteBuilder
     */
    public function delete($from){
        $retour = new ActiveDeleteBuilder( $this->query_builder->delete($from) );
        $retour->setCallback($this->cmpl_callback,$this->exec_callback);
        return $retour;
    }
}

class ActiveSelectBuilder{

    /**
     *
     * @var SelectBuilder
     */
    protected $_builder;
    protected $_values = array();

    protected $exec_callback;
    protected $cmpl_callback;

    public function __construct( SelectBuilder $builder ){
        $this->_builder = $builder;
    }

    public function setCallback( $cmpl_callback, $exec_callback ){
        $this->cmpl_callback = $cmpl_callback;
        $this->exec_callback = $exec_callback;
    }

    /**
     * Perform a raw query. The query should contain placeholders,
     * in either named or question mark style, and the parameters
     * should be an array of values which will be bound to the
     * placeholders in the query. If this method is called, all
     * other query building methods will be ignored.
     * @param $query
     * @param null $values
     * @return ActiveSelectBuilder
     */
    public function raw_query($query, $values=null) {
        $this->_builder->raw_query($query);
        if( $values !== null ){
            if(is_array($values) )
                $this->_values = array_merge($this->_values, $values);
            else
                $this->_values[] = $values;
        }
        return $this;
    }

    /**
     * Add an alias for the main table to be used in SELECT queries
     * @param $alias
     * @return ActiveSelectBuilder
     */
    public function table_alias($alias) {
        $this->_builder->table_alias($alias);
        return $this;
    }

    /**
     * Add a column to the list of columns returned by the SELECT
     * query. This defaults to '*'. The second optional argument is
     * the alias to return the column as.
     * @param $column
     * @param null $alias
     * @return ActiveSelectBuilder
     */
    public function select($column, $alias=null) {
        $this->_builder->select($column, $alias);
        return $this;
    }

    /**
     * Add an unquoted expression to the list of columns returned
     * by the SELECT query. The second optional argument is
     * the alias to return the column as.
     * @param $expr
     * @param null $alias
     * @return ActiveSelectBuilder
     */
    public function select_expr($expr, $alias=null) {
        $this->_builder->select_expr($expr, $alias);
        return $this;
    }

    /**
     * Add a DISTINCT keyword before the list of columns in the SELECT query
     * @return ActiveSelectBuilder
     */
    public function distinct() {
        $this->_builder->distinct();
        return $this;
    }

    /**
     * Tell the ORM that you wish to execute a COUNT query.
     * Will return an integer representing the number of
     * rows returned.
     * @return ActiveSelectBuilder
     */
    public function count() {
        $this->_builder->count();
        return $this;
    }

    /**
     * Add a simple JOIN source to the query
     * @param $table
     * @param null $table_alias
     * @return ActiveSelectBuilder
     */
    public function join($table, $table_alias=null) {
        $this->_builder->join($table, $table_alias);
        return $this;
    }

    /**
     * Add an INNER JOIN souce to the query
     * @param $table
     * @param null $table_alias
     * @return ActiveSelectBuilder
     */
    public function inner_join($table, $table_alias=null) {
        $this->_builder->inner_join($table, $table_alias);
        return $this;
    }

    /**
     * Add a LEFT OUTER JOIN souce to the query
     * @param $table
     * @param null $table_alias
     * @return ActiveSelectBuilder
     */
    public function left_outer_join($table, $table_alias=null) {
        $this->_builder->left_outer_join($table, $table_alias);
        return $this;
    }

    /**
     * Add an RIGHT OUTER JOIN souce to the query
     * @param $table
     * @param null $table_alias
     * @return ActiveSelectBuilder
     */
    public function right_outer_join($table, $table_alias=null) {
        $this->_builder->right_outer_join($table, $table_alias);
        return $this;
    }

    /**
     * Add an FULL OUTER JOIN souce to the query
     * @param $table
     * @param null $table_alias
     * @return ActiveSelectBuilder
     */
    public function full_outer_join($table, $table_alias=null) {
        $this->_builder->full_outer_join($table, $table_alias);
        return $this;
    }
    /**
     * Add an ON clause for a constraint
     * @param $col_left
     * @param $col_right
     * @return ActiveSelectBuilder
     */
    public function on($col_left, $col_right) {
        $this->_builder->on($col_left, "=", $col_right);
        return $this;
    }
    public function on_equal($col_left, $col_right) {
        $this->_builder->on_equal($col_left, $col_right);
        return $this;
    }
    public function on_not_equal($col_left, $col_right) {
        $this->_builder->on_not_equal($col_left, $col_right);
        return $this;
    }
    public function on_like($col_left, $col_right) {
        $this->_builder->on_like($col_left, $col_right);
        return $this;
    }
    public function on_not_like($col_left, $col_right) {
        $this->_builder->on_not_like($col_left, $col_right);
        return $this;
    }
    public function on_gt($col_left, $col_right) {
        $this->_builder->on_gt($col_left, $col_right);
        return $this;
    }
    public function on_lt($col_left, $col_right) {
        $this->_builder->on_lt($col_left, $col_right);
        return $this;
    }
    public function on_gte($col_left, $col_right) {
        $this->_builder->on_gte($col_left, $col_right);
        return $this;
    }

    /**
     * @param $col_left
     * @param $col_right
     * @return ActiveSelectBuilder
     */
    public function on_lte($col_left, $col_right) {
        $this->_builder->on_lte($col_left, $col_right);
        return $this;
    }

    /**
     * Add a WHERE column = value clause to your query. Each time
     * this is called in the chain, an additional WHERE will be
     * added, and these will be ANDed together when the final query
     * is built.
     * @param $column_name
     * @param $value
     * @return ActiveSelectBuilder
     */
    public function where($column_name, $value) {
        $this->_builder->where($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * More explicitly named version of for the where() method.
     * Can be used if preferred.
     * @param $column_name
     * @param $value
     * @return ActiveSelectBuilder
     */
    public function where_equal($column_name, $value) {
        $this->_builder->where_equal($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE column != value clause to your query.
     * @param $column_name
     * @param $value
     * @return ActiveSelectBuilder
     */
    public function where_not_equal($column_name, $value) {
        $this->_builder->where_not_equal($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... LIKE clause to your query.
     * @param $column_name
     * @param $value
     * @return ActiveSelectBuilder
     */
    public function where_like($column_name, $value) {
        $this->_builder->where_like($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add where WHERE ... NOT LIKE clause to your query.
     * @param $column_name
     * @param $value
     * @return ActiveSelectBuilder
     */
    public function where_not_like($column_name, $value) {
        $this->_builder->where_not_like($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... > clause to your query
     * @param $column_name
     * @param $value
     * @return ActiveSelectBuilder
     */
    public function where_gt($column_name, $value) {
        $this->_builder->where_gt($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... < clause to your query
     * @param $column_name
     * @param $value
     * @return ActiveSelectBuilder
     */
    public function where_lt($column_name, $value) {
        $this->_builder->where_lt($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... >= clause to your query
     * @param $column_name
     * @param $value
     * @return ActiveSelectBuilder
     */
    public function where_gte($column_name, $value) {
        $this->_builder->where_gte($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... <= clause to your query
     * @param $column_name
     * @param $value
     * @return ActiveSelectBuilder
     */
    public function where_lte($column_name, $value) {
        $this->_builder->where_lte($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... IN clause to your query
     * @param $column_name
     * @param $values
     * @return ActiveSelectBuilder
     */
    public function where_in($column_name, $values) {
        $this->_builder->where_in($column_name, count($values));
        $this->_values = array_merge($this->_values, $values);
        return $this;
    }

    /**
     * Add a WHERE ... NOT IN clause to your query
     * @param $column_name
     * @param $values_count
     * @return ActiveSelectBuilder
     */
    public function where_not_in($column_name, $values_count) {
        $this->_builder->where_not_in($column_name, count($values_count));
        $this->_values = array_merge($this->_values, $values_count);
        return $this;
    }

    /**
     * Add a WHERE column IS NULL clause to your query
     * @param $column_name
     * @return ActiveSelectBuilder
     */
    public function where_null($column_name) {
        $this->_builder->where_null($column_name);
        return $this;
    }

    /**
     * Add a WHERE column IS NOT NULL clause to your query
     * @param $column_name
     * @return ActiveSelectBuilder
     */
    public function where_not_null($column_name) {
        $this->_builder->where_not_null($column_name);
        return $this;
    }

    /**
     * Add a raw WHERE clause to the query. The clause should
     * contain question mark placeholders, which will be bound
     * to the parameters supplied in the second argument.
     * @param $clause
     * @param $values
     * @return ActiveSelectBuilder
     */
    public function where_raw($clause, $values) {
        $this->_builder->where_raw($clause);
        if(is_array($values) )
            $this->_values = array_merge($this->_values, $values);
        else
            $this->_values[] = $values;
        return $this;
    }

    /**
     * Add a LIMIT to the query
     * @param $limit
     * @return ActiveSelectBuilder
     */
    public function limit($limit) {
        $this->_builder->limit($limit);
        return $this;
    }

    /**
     * Add an OFFSET to the query
     * @param $offset
     * @return ActiveSelectBuilder
     */
    public function offset($offset) {
        $this->_builder->offset($offset);
        return $this;
    }

    /**
     * Add an ORDER BY column DESC clause
     * @param $column_name
     * @return ActiveSelectBuilder
     */
    public function order_by_desc($column_name) {
        $this->_builder->order_by_desc($column_name);
        return $this;
    }

    /**
     * Add an ORDER BY column ASC clause
     * @param $column_name
     * @return ActiveSelectBuilder
     */
    public function order_by_asc($column_name) {
        $this->_builder->order_by_asc($column_name);
        return $this;
    }

    /**
     * @param $column_name
     * @param $dir
     * @return ActiveSelectBuilder
     */
    public function order_by($column_name, $dir) {
        $this->_builder->order_by($column_name, $dir);
        return $this;
    }

    /**
     * Add a column to the list of columns to GROUP BY
     * @param $column_name
     * @return ActiveSelectBuilder
     */
    public function group_by($column_name) {
        $this->_builder->group_by($column_name);
        return $this;
    }

    /**
     * @return string
     */
    public function build(){
        return $this->_builder->build();
    }

    /**
     * @return mixed|string
     */
    public function _print(){
        $raw_query  = $this->build();
        $values     = $this->_values;
        $marks_pos  = array();
        $curr_pos   = 0;
        while( $curr_pos !== false ){
            $curr_pos = strpos($raw_query, "?", $curr_pos+1);
            if( $curr_pos !== false ) $marks_pos[] = $curr_pos;
        }
        $values     = array_reverse( $values );
        $marks_pos  = array_reverse( $marks_pos );

        foreach( $marks_pos as $index=>$mark_pos ){
            $raw_query = substr_replace($raw_query, "'".  addslashes($values[$index])."'", $mark_pos, 1 );
        }

        return $raw_query;
    }

    public function compile(){
        return call_user_func_array($this->cmpl_callback, array($this->build(), $this->_values));
    }

    public function execute(){
        return call_user_func_array($this->exec_callback, array($this->compile(), $this->_values));
    }

}

class ActiveInsertBuilder{
    /**
     *
     * @var InsertBuilder
     */
    protected $_builder;
    protected $_values = array();

    protected $exec_callback;
    protected $cmpl_callback;

    public function __construct( InsertBuilder $builder ){
        $this->_builder = $builder;
    }

    public function setCallback( $cmpl_callback, $exec_callback ){
        $this->cmpl_callback = $cmpl_callback;
        $this->exec_callback = $exec_callback;
    }

    public function fields( $fields, $values=null ){
        $this->_builder->fields($fields);
        if( $values !== null ){
            if(is_array($values) )
                $this->_values = array_merge($this->_values, $values);
            else
                $this->_values[] = $values;
        }
        return $this;
    }

    public function build(){
        return $this->_builder->build();
    }

    public function _print(){
        $raw_query  = $this->build();
        $values     = $this->_values;
        $marks_pos  = array();
        $curr_pos   = 0;
        while( $curr_pos !== false ){
            $curr_pos = strpos($raw_query, "?", $curr_pos+1);
            if( $curr_pos !== false ) $marks_pos[] = $curr_pos;
        }
        $values     = array_reverse( $values );
        $marks_pos  = array_reverse( $marks_pos );

        foreach( $marks_pos as $index=>$mark_pos ){
            $raw_query = substr_replace($raw_query, "'".  addslashes($values[$index])."'", $mark_pos, 1 );
        }

        return $raw_query;
    }

    public function compile(){
        return call_user_func_array($this->cmpl_callback, array($this->build(), $this->_values));
    }

    public function execute(){
        return call_user_func_array($this->exec_callback, array($this->compile(), $this->_values));
    }


}

class ActiveReplaceBuilder extends ActiveInsertBuilder{

    public function __construct( ReplaceBuilder $builder ){
        $this->_builder = $builder;
    }
}

class ActiveUpdateBuilder{
    /**
     *
     * @var UpdateBuilder
     */
    protected $_builder;
    protected $_values = array();

    protected $exec_callback;
    protected $cmpl_callback;

    public function __construct( UpdateBuilder $builder ){
        $this->_builder = $builder;
    }

    public function setCallback( $cmpl_callback, $exec_callback ){
        $this->cmpl_callback = $cmpl_callback;
        $this->exec_callback = $exec_callback;
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
        if( $values !== null ){
            if(is_array($values) )
                $this->_values = array_merge($this->_values, $values);
            else
                $this->_values[] = $values;
        }
        return $this;
    }


    public function fields( $fields, $values ){
        $this->_builder->fields($fields);
        if( $values !== null ){
            if(is_array($values) )
                $this->_values = array_merge($this->_values, $values);
            else
                $this->_values[] = $values;
        }
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
        $this->_values[] = $value;
        return $this;
    }

    /**
     * More explicitly named version of for the where() method.
     * Can be used if preferred.
     */
    public function where_equal($column_name, $value) {
        $this->_builder->where_equal($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE column != value clause to your query.
     */
    public function where_not_equal($column_name, $value) {
        $this->_builder->where_not_equal($column_name);
        $this->_values[] = $value;
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
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... > clause to your query
     */
    public function where_gt($column_name, $value) {
        $this->_builder->where_gt($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... < clause to your query
     */
    public function where_lt($column_name, $value) {
        $this->_builder->where_lt($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... >= clause to your query
     */
    public function where_gte($column_name, $value) {
        $this->_builder->where_gte($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... <= clause to your query
     */
    public function where_lte($column_name, $value) {
        $this->_builder->where_lte($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... IN clause to your query
     */
    public function where_in($column_name, $values) {
        $this->_builder->where_in($column_name, count($values));
        $this->_values = array_merge($this->_values, $values);
        return $this;
    }

    /**
     * Add a WHERE ... NOT IN clause to your query
     */
    public function where_not_in($column_name, $values_count) {
        $this->_builder->where_not_in($column_name, count($values_count));
        $this->_values = array_merge($this->_values, $values_count);
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
        $this->_builder->where_raw($clause);
        if(is_array($values) )
            $this->_values = array_merge($this->_values, $values);
        else
            $this->_values[] = $values;
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
        $raw_query  = $this->build();
        $values     = $this->_values;
        $marks_pos  = array();
        $curr_pos   = 0;
        while( $curr_pos !== false ){
            $curr_pos = strpos($raw_query, "?", $curr_pos+1);
            if( $curr_pos !== false ) $marks_pos[] = $curr_pos;
        }
        $values     = array_reverse( $values );
        $marks_pos  = array_reverse( $marks_pos );

        foreach( $marks_pos as $index=>$mark_pos ){
            $raw_query = substr_replace($raw_query, "'".  addslashes($values[$index])."'", $mark_pos, 1 );
        }

        return $raw_query;
    }

    public function compile(){
        return call_user_func_array($this->cmpl_callback, array($this->build(), $this->_values));
    }

    public function execute(){
        return call_user_func_array($this->exec_callback, array($this->compile(), $this->_values));
    }
}

class ActiveDeleteBuilder{
    /**
     *
     * @var DeleteBuilder
     */
    protected $_builder;
    protected $_values = array();

    protected $exec_callback;
    protected $cmpl_callback;

    public function __construct( DeleteBuilder $builder ){
        $this->_builder = $builder;
    }

    public function setCallback( $cmpl_callback, $exec_callback ){
        $this->cmpl_callback = $cmpl_callback;
        $this->exec_callback = $exec_callback;
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
        if( $values !== null ){
            if(is_array($values) )
                $this->_values = array_merge($this->_values, $values);
            else
                $this->_values[] = $values;
        }
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
        $this->_values[] = $value;
        return $this;
    }

    /**
     * More explicitly named version of for the where() method.
     * Can be used if preferred.
     */
    public function where_equal($column_name, $value) {
        $this->_builder->where_equal($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE column != value clause to your query.
     */
    public function where_not_equal($column_name, $value) {
        $this->_builder->where_not_equal($column_name);
        $this->_values[] = $value;
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
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... > clause to your query
     */
    public function where_gt($column_name, $value) {
        $this->_builder->where_gt($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... < clause to your query
     */
    public function where_lt($column_name, $value) {
        $this->_builder->where_lt($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... >= clause to your query
     */
    public function where_gte($column_name, $value) {
        $this->_builder->where_gte($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... <= clause to your query
     */
    public function where_lte($column_name, $value) {
        $this->_builder->where_lte($column_name);
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Add a WHERE ... IN clause to your query
     */
    public function where_in($column_name, $values) {
        $this->_builder->where_in($column_name, count($values));
        $this->_values = array_merge($this->_values, $values);
        return $this;
    }

    /**
     * Add a WHERE ... NOT IN clause to your query
     */
    public function where_not_in($column_name, $values_count) {
        $this->_builder->where_not_in($column_name, count($values_count));
        $this->_values = array_merge($this->_values, $values_count);
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
        $this->_builder->where_raw($clause);
        if(is_array($values) )
            $this->_values = array_merge($this->_values, $values);
        else
            $this->_values[] = $values;
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
        $raw_query  = $this->build();
        $values     = $this->_values;
        $marks_pos  = array();
        $curr_pos   = 0;
        while( $curr_pos !== false ){
            $curr_pos = strpos($raw_query, "?", $curr_pos+1);
            if( $curr_pos !== false ) $marks_pos[] = $curr_pos;
        }
        $values     = array_reverse( $values );
        $marks_pos  = array_reverse( $marks_pos );

        foreach( $marks_pos as $index=>$mark_pos ){
            $raw_query = substr_replace($raw_query, "'".  addslashes($values[$index])."'", $mark_pos, 1 );
        }

        return $raw_query;
    }
    public function compile(){
        return call_user_func_array($this->cmpl_callback, array($this->build(), $this->_values));
    }

    public function execute(){
        return call_user_func_array($this->exec_callback, array($this->compile(), $this->_values));
    }
}
