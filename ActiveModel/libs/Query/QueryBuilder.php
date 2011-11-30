<?php
class QueryBuilder{

    // The name of the table the current ORM instance is associated with
    protected $_identifier_quote_character;

    /**
     * @param $identifier_quote_character
     * @return QueryBuilder
     */
    public function set_identifier_quote_character($identifier_quote_character) {
        $this->_identifier_quote_character = $identifier_quote_character;
        return $this;
    }

    /**
     * @param $from
     * @return SelectBuilder
     */
    public function select($from){
        $retour = new SelectBuilder($from);
        $retour->set_identifier_quote_character($this->_identifier_quote_character);
        return $retour;
    }

    /**
     * @param $into
     * @return InsertBuilder
     */
    public function insert($into){
        $retour = new InsertBuilder($into);
        $retour->set_identifier_quote_character($this->_identifier_quote_character);
        return $retour;
    }

    /**
     * @param $table
     * @return UpdateBuilder
     */
    public function update($table){
        $retour = new UpdateBuilder($table);
        $retour->set_identifier_quote_character($this->_identifier_quote_character);
        return $retour;
    }

    /**
     * @param $into
     * @return ReplaceBuilder
     */
    public function replace($into){
        $retour = new ReplaceBuilder($into);
        $retour->set_identifier_quote_character($this->_identifier_quote_character);
        return $retour;
    }

    /**
     * @param $from
     * @return DeleteBuilder
     */
    public function delete($from){
        $retour = new DeleteBuilder($from);
        $retour->set_identifier_quote_character($this->_identifier_quote_character);
        return $retour;
    }
}

class SelectBuilder{

    public function __construct( $from ){
        $this->_table_name = $from;
    }

    // ----------------------- //
    // --- CLASS CONSTANTS --- //
    // ----------------------- //

    // ------------------------ //
    // --- CLASS PROPERTIES --- //
    // ------------------------ //


    // --------------------------- //
    // --- INSTANCE PROPERTIES --- //
    // --------------------------- //
    // The name of the table the current ORM instance is associated with
    protected $_identifier_quote_character;

    // The name of the table the current ORM instance is associated with
    protected $_table_name;

    // Alias for the table to be used in SELECT queries
    protected $_table_alias = null;

    // Columns to select in the result
    protected $_result_columns = array('*');

    // Are we using the default result column or have these been manually changed?
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

        /**
         * @param $identifier_quote_character
         * @return SelectBuilder
         */
        public function set_identifier_quote_character($identifier_quote_character) {
            $this->_identifier_quote_character = $identifier_quote_character;
            return $this;
        }

        /**
         * Perform a raw query. The query should contain placeholders,
         * in either named or question mark style, and the parameters
         * should be an array of values which will be bound to the
         * placeholders in the query. If this method is called, all
         * other query building methods will be ignored.
         * @param $query
         * @return SelectBuilder
         */
        public function raw_query($query) {
            $this->_is_raw_query = true;
            $this->_raw_query = $query;
            return $this;
        }

        /**
         * Add an alias for the main table to be used in SELECT queries
         * @param $alias
         * @return SelectBuilder
         */
        public function table_alias($alias) {
            $this->_table_alias = $alias;
            return $this;
        }

        /**
         * Internal method to add an unquoted expression to the set
         * of columns returned by the SELECT query. The second optional
         * argument is the alias to return the expression as.
         * @param $expr
         * @param null $alias
         * @return SelectBuilder
         */
        protected function _add_result_column($expr, $alias=null) {
            if (!is_null($alias)) {
                $expr .= " AS " . $this->_quote_identifier($alias);
            }

            if ($this->_using_default_result_columns) {
                $this->_result_columns = array($expr);
                $this->_using_default_result_columns = false;
            } else {
                $this->_result_columns[] = $expr;
            }
            return $this;
        }

        /**
         * Add a column to the list of columns returned by the SELECT
         * query. This defaults to '*'. The second optional argument is
         * the alias to return the column as.
         * @param $column
         * @param null $alias
         * @return SelectBuilder
         */
        public function select($column, $alias=null) {
            $column = $this->_quote_identifier($column);
            return $this->_add_result_column($column, $alias);
        }

        /**
         * Add an unquoted expression to the list of columns returned
         * by the SELECT query. The second optional argument is
         * the alias to return the column as.
         * @param $expr
         * @param null $alias
         * @return SelectBuilder
         */
        public function select_expr($expr, $alias=null) {
            return $this->_add_result_column($expr, $alias);
        }

        /**
         * Add a DISTINCT keyword before the list of columns in the SELECT query
         * @return SelectBuilder
         */
        public function distinct() {
            $this->_distinct = true;
            return $this;
        }

        /**
         * Tell the ORM that you wish to execute a COUNT query.
         * Will return an integer representing the number of
         * rows returned.
         * @return SelectBuilder
         */
        public function count() {
            return $this->select_expr('COUNT(*)', 'count');
        }

        /**
         * Internal method to add a JOIN source to the query.
         *
         * The join_operator should be one of INNER, LEFT OUTER, CROSS etc - this
         * will be prepended to JOIN.
         *
         * The table should be the name of the table to join to.
         *
         * The constraint may be either a string or an array with three elements. If it
         * is a string, it will be compiled into the query as-is, with no escaping. The
         * recommended way to supply the constraint is as an array with three elements:
         *
         * first_column, operator, second_column
         *
         * Example: array('user.id', '=', 'profile.user_id')
         *
         * will compile to
         *
         * ON `user`.`id` = `profile`.`user_id`
         *
         * The final (optional) argument specifies an alias for the joined table.
         * @param $join_operator
         * @param $table
         * @param null $table_alias
         * @return SelectBuilder
         */
        protected function _add_join_source($join_operator, $table, $table_alias=null) {

            $join_operator = trim("{$join_operator} JOIN");

            $table = $this->_quote_identifier($table);

            // Add table alias if present
            if (!is_null($table_alias)) {
                $table_alias = $this->_quote_identifier($table_alias);
                $table .= " AS {$table_alias}";
            }

            $this->_join_sources[] = array( "what"=>"{$join_operator} {$table} ",
                                            "on"=>array() );
            return $this;
        }

        /**
         * Add a simple JOIN source to the query
         * @param $table
         * @param null $table_alias
         * @return SelectBuilder
         */
        public function join($table, $table_alias=null) {
            return $this->_add_join_source("INNER", $table, $table_alias);
        }

        /**
         * Add an INNER JOIN souce to the query
         * @param $table
         * @param null $table_alias
         * @return SelectBuilder
         */
        public function inner_join($table, $table_alias=null) {
            return $this->_add_join_source("INNER", $table, $table_alias);
        }

        /**
         * Add a LEFT OUTER JOIN souce to the query
         * @param $table
         * @param null $table_alias
         * @return SelectBuilder
         */
        public function left_outer_join($table, $table_alias=null) {
            return $this->_add_join_source("LEFT OUTER", $table, $table_alias);
        }

        /**
         * Add an RIGHT OUTER JOIN souce to the query
         * @param $table
         * @param null $table_alias
         * @return SelectBuilder
         */
        public function right_outer_join($table, $table_alias=null) {
            return $this->_add_join_source("RIGHT OUTER", $table, $table_alias);
        }

        /**
         * Add an FULL OUTER JOIN souce to the query
         * @param $table
         * @param null $table_alias
         * @return SelectBuilder
         */
        public function full_outer_join($table, $table_alias=null) {
            return $this->_add_join_source("FULL OUTER", $table, $table_alias);
        }

        /**
         * Add an ON clause for a constraint
         * @param $col_left
         * @param $operator
         * @param null $col_right
         * @return SelectBuilder
         * @throws Exception
         */
        public function on($col_left, $operator, $col_right=null) {
            $join_index = count($this->_join_sources)-1;
            if( $join_index < 0 ) throw new Exception ("You cannot do on, you must join before");
            $col_right = $col_right===null?"?":$col_right;
            $col_left = $this->_quote_identifier($col_left);
            $col_right = $this->_quote_identifier($col_right);
            $this->_join_sources[$join_index]["on"][] = "{$col_left} {$operator} {$col_right} AND ";
            return $this;
        }
        public function on_equal($col_left, $col_right=null) {
            return $this->on($col_left, "=", $col_right);
        }
        public function on_not_equal($col_left, $col_right=null) {
            return $this->on($col_left, "!=", $col_right);
        }
        public function on_like($col_left, $col_right=null) {
            return $this->on($col_left, "LIKE", $col_right);
        }
        public function on_not_like($col_left, $col_right=null) {
            return $this->on($col_left, "NOT LIKE", $col_right);
        }
        public function on_gt($col_left, $col_right=null) {
            return $this->on($col_left, ">", $col_right);
        }
        public function on_lt($col_left, $col_right=null) {
            return $this->on($col_left, "<", $col_right);
        }
        public function on_gte($col_left, $col_right=null) {
            return $this->on($col_left, ">=", $col_right);
        }
        public function on_lte($col_left, $col_right=null) {
            return $this->on($col_left, "<=", $col_right);
        }

        /**
         * Internal method to add a WHERE condition to the query
         * @param $fragment
         * @return SelectBuilder
         */
        protected function _add_where($fragment) {
            $this->_where_conditions[] = $fragment;
            return $this;
        }

        /**
         * Helper method to compile a simple COLUMN SEPARATOR VALUE
         * style WHERE condition into a string and value ready to
         * be passed to the _add_where method. Avoids duplication
         * of the call to _quote_identifier
         * @param $column_name
         * @param $separator
         * @return SelectBuilder
         */
        protected function _add_simple_where($column_name, $separator) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} {$separator} ?");
        }

        /**
         * Return a string containing the given number of question marks,
         * separated by commas. Eg "?, ?, ?"
         * @param $number_of_placeholders
         * @return string
         */
        protected function _create_placeholders($number_of_placeholders) {
            return join(", ", array_fill(0, $number_of_placeholders, "?"));
        }

        /**
         * Add a WHERE column = value clause to your query. Each time
         * this is called in the chain, an additional WHERE will be
         * added, and these will be ANDed together when the final query
         * is built.
         * @param $column_name
         * @return SelectBuilder
         */
        public function where($column_name) {
            return $this->where_equal($column_name);
        }

        /**
         * More explicitly named version of for the where() method.
         * Can be used if preferred.
         * @param $column_name
         * @return SelectBuilder
         */
        public function where_equal($column_name) {
            return $this->_add_simple_where($column_name, '=');
        }

        /**
         * Add a WHERE column != value clause to your query.
         * @param $column_name
         * @return SelectBuilder
         */
        public function where_not_equal($column_name) {
            return $this->_add_simple_where($column_name, '!=');
        }

        /**
         * Add a WHERE ... LIKE clause to your query.
         * @param $column_name
         * @return SelectBuilder
         */
        public function where_like($column_name) {
            return $this->_add_simple_where($column_name, 'LIKE');
        }

        /**
         * Add where WHERE ... NOT LIKE clause to your query.
         * @param $column_name
         * @return SelectBuilder
         */
        public function where_not_like($column_name) {
            return $this->_add_simple_where($column_name, 'NOT LIKE');
        }

        /**
         * Add a WHERE ... > clause to your query
         * @param $column_name
         * @return SelectBuilder
         */
        public function where_gt($column_name) {
            return $this->_add_simple_where($column_name, '>');
        }

        /**
         * Add a WHERE ... < clause to your query
         * @param $column_name
         * @return SelectBuilder
         */
        public function where_lt($column_name) {
            return $this->_add_simple_where($column_name, '<');
        }

        /**
         * Add a WHERE ... >= clause to your query
         * @param $column_name
         * @return SelectBuilder
         */
        public function where_gte($column_name) {
            return $this->_add_simple_where($column_name, '>=');
        }

        /**
         * Add a WHERE ... <= clause to your query
         * @param $column_name
         * @return SelectBuilder
         */
        public function where_lte($column_name) {
            return $this->_add_simple_where($column_name, '<=');
        }

        /**
         * Add a WHERE ... IN clause to your query
         * @param $column_name
         * @param $values_count
         * @return SelectBuilder
         */
        public function where_in($column_name, $values_count) {
            $column_name = $this->_quote_identifier($column_name);
            $placeholders = $this->_create_placeholders( $values_count );
            return $this->_add_where("{$column_name} IN ({$placeholders})");
        }

        /**
         * Add a WHERE ... NOT IN clause to your query
         * @param $column_name
         * @param $values_count
         * @return SelectBuilder
         */
        public function where_not_in($column_name, $values_count) {
            $column_name = $this->_quote_identifier($column_name);
            $placeholders = $this->_create_placeholders($values_count);
            return $this->_add_where("{$column_name} NOT IN ({$placeholders})");
        }

        /**
         * Add a WHERE column IS NULL clause to your query
         * @param $column_name
         * @return SelectBuilder
         */
        public function where_null($column_name) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} IS NULL");
        }

        /**
         * Add a WHERE column IS NOT NULL clause to your query
         * @param $column_name
         * @return SelectBuilder
         */
        public function where_not_null($column_name) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} IS NOT NULL");
        }

        /**
         * Add a raw WHERE clause to the query. The clause should
         * contain question mark placeholders, which will be bound
         * to the parameters supplied in the second argument.
         * @param $clause
         * @return SelectBuilder
         */
        public function where_raw($clause) {
            return $this->_add_where($clause);
        }

        /**
         * Add a LIMIT to the query
         * @param $limit
         * @return SelectBuilder
         */
        public function limit($limit) {
            $this->_limit = $limit;
            return $this;
        }

        /**
         * Add an OFFSET to the query
         * @param $offset
         * @return SelectBuilder
         */
        public function offset($offset) {
            $this->_offset = $offset;
            return $this;
        }

        /**
         * Add an ORDER BY clause to the query
         * @param $column_name
         * @param $ordering
         * @return SelectBuilder
         */
        protected function _add_order_by($column_name, $ordering) {
            $column_name = $this->_quote_identifier($column_name);
            $this->_order_by[] = "{$column_name} {$ordering}";
            return $this;
        }

        /**
         * @param $column_name
         * @param $dir
         * @return SelectBuilder
         */
        public function order_by($column_name, $dir) {
            return $this->_add_order_by($column_name, $dir);
        }

        /**
         * Add an ORDER BY column DESC clause
         * @param $column_name
         * @return SelectBuilder
         */
        public function order_by_desc($column_name) {
            return $this->order_by($column_name, 'DESC');
        }

        /**
         * Add an ORDER BY column ASC clause
         * @param $column_name
         * @return SelectBuilder
         */
        public function order_by_asc($column_name) {
            return $this->order_by($column_name, 'ASC');
        }

        /**
         * Add a column to the list of columns to GROUP BY
         * @param $column_name
         * @return SelectBuilder
         */
        public function group_by($column_name) {
            $column_name = $this->_quote_identifier($column_name);
            $this->_group_by[] = $column_name;
            return $this;
        }

        /**
         * @return string
         */
        public function build(){
            return $this->_build_query();
        }

        /**
         * Build a SELECT statement based on the clauses that have
         * been passed to this instance by chaining method calls.
         * @return string
         */
        protected function _build_query() {
            // If the query is raw, just set the $this->_values to be
            // the raw query parameters and return the raw query
            if ($this->_is_raw_query) {
                return $this->_raw_query;
            }

            // Build and return the full SELECT statement by concatenating
            // the results of calling each separate builder method.
            return $this->_join_if_not_empty(" ", array(
                $this->_build_start(),
                $this->_build_join(),
                $this->_build_where(),
                $this->_build_group_by(),
                $this->_build_order_by(),
                $this->_build_limit(),
                $this->_build_offset(),
            ));
        }

        /**
         * Build the start of the SELECT statement
         * @return string
         */
        protected function _build_start() {
            if( $this->_using_default_result_columns ){
                $t_name = $this->_table_alias===null?$this->_table_name:$this->_table_alias;
                $result_columns = $this->_quote_identifier($t_name).".".$this->_result_columns[0];
            }else{
                $result_columns = join(', ', $this->_result_columns);
            }

            if ($this->_distinct) {
                $result_columns = 'DISTINCT ' . $result_columns;
            }

            $fragment = "SELECT {$result_columns} FROM " . $this->_quote_identifier($this->_table_name);

            if (!is_null($this->_table_alias)) {
                $fragment .= " AS " . $this->_quote_identifier($this->_table_alias);
            }
            return $fragment;
        }

        /**
         * Build the JOIN sources
         * @return string
         */
        protected function _build_join() {
            if (count($this->_join_sources) === 0) {
                return '';
            }
            $retour = " ";
            foreach( $this->_join_sources as $_join_source ){
                $retour .= "".trim($_join_source["what"])."";
                $retour .= " ON ( " . trim( substr(join(" ", $_join_source["on"] ),0,-4) )." ) ";
            }

            return $retour;
        }

        /**
         * Build the WHERE clause(s)
         * @return string
         */
        protected function _build_where() {
            // If there are no WHERE clauses, return empty string
            if (count($this->_where_conditions) === 0) {
                return '';
            }

            $where_conditions = array();
            foreach ($this->_where_conditions as $condition) {
                $where_conditions[] = $condition;
            }

            return "WHERE " . join(" AND ", $where_conditions);
        }

        /**
         * Build GROUP BY
         * @return string
         */
        protected function _build_group_by() {
            if (count($this->_group_by) === 0) {
                return '';
            }
            return "GROUP BY " . join(", ", $this->_group_by);
        }

        /**
         * Build ORDER BY
         * @return string
         */
        protected function _build_order_by() {
            if (count($this->_order_by) === 0) {
                return '';
            }
            return "ORDER BY " . join(", ", $this->_order_by);
        }

        /**
         * Build LIMIT
         * @return string
         */
        protected function _build_limit() {
            if (!is_null($this->_limit)) {
                return "LIMIT " . $this->_limit;
            }
            return '';
        }

        /**
         * Build OFFSET
         * @return string
         */
        protected function _build_offset() {
            if (!is_null($this->_offset)) {
                return "OFFSET " . $this->_offset;
            }
            return '';
        }

        /**
         * Wrapper around PHP's join function which
         * only adds the pieces if they are not empty.
         * @param $glue
         * @param $pieces
         * @return string
         */
        protected function _join_if_not_empty($glue, $pieces) {
            $filtered_pieces = array();
            foreach ($pieces as $piece) {
                if (is_string($piece)) {
                    $piece = trim($piece);
                }
                if (!empty($piece)) {
                    $filtered_pieces[] = $piece;
                }
            }
            return join($glue, $filtered_pieces);
        }

        /**
         * Quote a string that is used as an identifier
         * (table names, column names etc). This method can
         * also deal with dot-separated identifiers eg table.column
         * @param $identifier
         * @return string
         */
        protected function _quote_identifier($identifier) {
            $quote_character = $this->_identifier_quote_character;
            $retour = $identifier;
            if(strpos($identifier,".") === false ){
                if( $identifier !== "*" ){
                    $retour = $quote_character . $identifier . $quote_character;
                }
            }else{
                $parts = explode('.', $identifier);
                $retour = $quote_character . $parts[0] . $quote_character.".";
                if( $parts[1] === "*" )
                    $retour .= $parts[1];
                else
                    $retour .= $quote_character . $parts[1] . $quote_character;
            }
            return $retour;
        }
}

class InsertBuilder{
    public function __construct( $into ){
        $this->_table_name = $into;
    }

    // ----------------------- //
    // --- CLASS CONSTANTS --- //
    // ----------------------- //

    // ------------------------ //
    // --- CLASS PROPERTIES --- //
    // ------------------------ //


    // --------------------------- //
    // --- INSTANCE PROPERTIES --- //
    // --------------------------- //
    // The name of the table the current ORM instance is associated with
    protected $_identifier_quote_character;

    // The name of the table the current ORM instance is associated with
    protected $_table_name;

    // Array of FIELDS
    protected $_fields = array();

    /**
     */
    public function set_identifier_quote_character($identifier_quote_character) {
        $this->_identifier_quote_character = $identifier_quote_character;
        return $this;
    }

    /**
     * Return a string containing the given number of question marks,
     * separated by commas. Eg "?, ?, ?"
     */
    protected function _create_placeholders($number_of_placeholders) {
        return join(", ", array_fill(0, $number_of_placeholders, "?"));
    }

    protected function _add_field( $field ){
        $field = $this->_quote_identifier($field);
        $this->_fields[] = $field;
    }

    public function fields( $fields ){
        foreach( explode(",",$fields) as $field )
            $this->_add_field (trim($field));
        return $this;
    }

    public function build(){
        return $this->_build_query();
    }

    /**
     * Build a SELECT statement based on the clauses that have
     * been passed to this instance by chaining method calls.
     */
    protected function _build_query() {

        // Build and return the full SELECT statement by concatenating
        // the results of calling each separate builder method.
        return $this->_join_if_not_empty(" ", array(
            $this->_build_start(),
            $this->_build_columns(),
            $this->_build_values(),
        ));
    }

    /**
     * Build the start of the SELECT statement
     */
    protected function _build_start() {
        $fragment = "INSERT INTO " . $this->_quote_identifier($this->_table_name) ."";
        return $fragment;
    }

    /**
     * Build the WHERE clause(s)
     */
    protected function _build_columns() {
        return " (" . join(" , ", $this->_fields).") ";
    }

    /**
     * Build the WHERE clause(s)
     */
    protected function _build_values() {
        return " VALUES (" . substr(str_repeat(" ? , ", count($this->_fields)),0,-2).") ";
    }

    /**
     * Quote a string that is used as an identifier
     * (table names, column names etc). This method can
     * also deal with dot-separated identifiers eg table.column
     */
    protected function _quote_identifier($identifier) {
        $parts = explode('.', $identifier);
        $parts = array_map(array($this, '_quote_identifier_part'), $parts);
        return join('.', $parts);
    }

    /**
     * This method performs the actual quoting of a single
     * part of an identifier, using the identifier quote
     * character specified in the config (or autodetected).
     */
    protected function _quote_identifier_part($part) {
        if ($part === '*') {
            return $part;
        }
        $quote_character = $this->_identifier_quote_character;
        return $quote_character . $part . $quote_character;
    }

    /**
     * Wrapper around PHP's join function which
     * only adds the pieces if they are not empty.
     */
    protected function _join_if_not_empty($glue, $pieces) {
        $filtered_pieces = array();
        foreach ($pieces as $piece) {
            if (is_string($piece)) {
                $piece = trim($piece);
            }
            if (!empty($piece)) {
                $filtered_pieces[] = $piece;
            }
        }
        return join($glue, $filtered_pieces);
    }
}

class ReplaceBuilder extends InsertBuilder{

    /**
     * Build the start of the SELECT statement
     */
    protected function _build_start() {
        $fragment = "REPLACE INTO " . $this->_quote_identifier($this->_table_name) ."";
        return $fragment;
    }
}

class UpdateBuilder{
    public function __construct( $table ){
        $this->_table_name = $table;
    }

        // ----------------------- //
        // --- CLASS CONSTANTS --- //
        // ----------------------- //

        // ------------------------ //
        // --- CLASS PROPERTIES --- //
        // ------------------------ //


        // --------------------------- //
        // --- INSTANCE PROPERTIES --- //
        // --------------------------- //
        // The name of the table the current ORM instance is associated with
        protected $_identifier_quote_character;

        // Array of FIELDS
        protected $_fields = array();

        // The name of the table the current ORM instance is associated with
        protected $_table_name;

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


        /**
         */
        public function set_identifier_quote_character($identifier_quote_character) {
            $this->_identifier_quote_character = $identifier_quote_character;
            return $this;
        }

        /**
         * Perform a raw query. The query should contain placeholders,
         * in either named or question mark style, and the parameters
         * should be an array of values which will be bound to the
         * placeholders in the query. If this method is called, all
         * other query building methods will be ignored.
         */
        public function raw_query($query) {
            $this->_is_raw_query = true;
            $this->_raw_query = $query;
            return $this;
        }

        /**
         * Internal method to add a WHERE condition to the query
         */
        protected function _add_where($fragment) {
            $this->_where_conditions[] = $fragment;
            return $this;
        }

        /**
         * Helper method to compile a simple COLUMN SEPARATOR VALUE
         * style WHERE condition into a string and value ready to
         * be passed to the _add_where method. Avoids duplication
         * of the call to _quote_identifier
         */
        protected function _add_simple_where($column_name, $separator) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} {$separator} ?");
        }

        /**
         * Return a string containing the given number of question marks,
         * separated by commas. Eg "?, ?, ?"
         */
        protected function _create_placeholders($number_of_placeholders) {
            return join(", ", array_fill(0, $number_of_placeholders, "?"));
        }

        protected function _add_field( $field ){
            $field = $this->_quote_identifier($field);
            $this->_fields[] = $field;
        }

        public function fields( $fields ){
            foreach( explode(",",$fields) as $field )
                $this->_add_field (trim($field));
            return $this;
        }

        /**
         * Add a WHERE column = value clause to your query. Each time
         * this is called in the chain, an additional WHERE will be
         * added, and these will be ANDed together when the final query
         * is built.
         */
        public function where($column_name) {
            return $this->where_equal($column_name);
        }

        /**
         * More explicitly named version of for the where() method.
         * Can be used if preferred.
         */
        public function where_equal($column_name) {
            return $this->_add_simple_where($column_name, '=');
        }

        /**
         * Add a WHERE column != value clause to your query.
         */
        public function where_not_equal($column_name) {
            return $this->_add_simple_where($column_name, '!=');
        }

        /**
         * Add a WHERE ... LIKE clause to your query.
         */
        public function where_like($column_name) {
            return $this->_add_simple_where($column_name, 'LIKE');
        }

        /**
         * Add where WHERE ... NOT LIKE clause to your query.
         */
        public function where_not_like($column_name) {
            return $this->_add_simple_where($column_name, 'NOT LIKE');
        }

        /**
         * Add a WHERE ... > clause to your query
         */
        public function where_gt($column_name) {
            return $this->_add_simple_where($column_name, '>');
        }

        /**
         * Add a WHERE ... < clause to your query
         */
        public function where_lt($column_name) {
            return $this->_add_simple_where($column_name, '<');
        }

        /**
         * Add a WHERE ... >= clause to your query
         */
        public function where_gte($column_name) {
            return $this->_add_simple_where($column_name, '>=');
        }

        /**
         * Add a WHERE ... <= clause to your query
         */
        public function where_lte($column_name) {
            return $this->_add_simple_where($column_name, '<=');
        }

        /**
         * Add a WHERE ... IN clause to your query
         */
        public function where_in($column_name, $values_count) {
            $column_name = $this->_quote_identifier($column_name);
            $placeholders = $this->_create_placeholders( $values_count );
            return $this->_add_where("{$column_name} IN ({$placeholders})");
        }

        /**
         * Add a WHERE ... NOT IN clause to your query
         */
        public function where_not_in($column_name, $values_count) {
            $column_name = $this->_quote_identifier($column_name);
            $placeholders = $this->_create_placeholders($values_count);
            return $this->_add_where("{$column_name} NOT IN ({$placeholders})");
        }

        /**
         * Add a WHERE column IS NULL clause to your query
         */
        public function where_null($column_name) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} IS NULL");
        }

        /**
         * Add a WHERE column IS NOT NULL clause to your query
         */
        public function where_not_null($column_name) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} IS NOT NULL");
        }

        /**
         * Add a raw WHERE clause to the query. The clause should
         * contain question mark placeholders, which will be bound
         * to the parameters supplied in the second argument.
         */
        public function where_raw($clause) {
            return $this->_add_where($clause);
        }

        /**
         * Add a LIMIT to the query
         */
        public function limit($limit) {
            $this->_limit = $limit;
            return $this;
        }

        /**
         * Add an OFFSET to the query
         */
        public function offset($offset) {
            $this->_offset = $offset;
            return $this;
        }

        public function build(){
            return $this->_build_query();
        }

        /**
         * Build a SELECT statement based on the clauses that have
         * been passed to this instance by chaining method calls.
         */
        protected function _build_query() {
            // If the query is raw, just set the $this->_values to be
            // the raw query parameters and return the raw query
            if ($this->_is_raw_query) {
                return $this->_raw_query;
            }

            // Build and return the full SELECT statement by concatenating
            // the results of calling each separate builder method.
            return $this->_join_if_not_empty(" ", array(
                $this->_build_start(),
                $this->_build_set(),
                $this->_build_where(),
                $this->_build_limit(),
                $this->_build_offset(),
            ));
        }

        /**
         * Build the start of the SELECT statement
         */
        protected function _build_start() {
            $fragment = "UPDATE " . $this->_quote_identifier($this->_table_name);
            return $fragment;
        }

        /**
         * Build the start of the SELECT statement
         */
        protected function _build_set() {
            $fragment = "SET " . join(" = ? , ", $this->_fields)." = ?";
            return $fragment;
        }

        /**
         * Build the WHERE clause(s)
         */
        protected function _build_where() {
            // If there are no WHERE clauses, return empty string
            if (count($this->_where_conditions) === 0) {
                return '';
            }

            $where_conditions = array();
            foreach ($this->_where_conditions as $condition) {
                $where_conditions[] = $condition;
            }

            return "WHERE " . join(" AND ", $where_conditions);
        }

        /**
         * Build LIMIT
         */
        protected function _build_limit() {
            if (!is_null($this->_limit)) {
                return "LIMIT " . $this->_limit;
            }
            return '';
        }

        /**
         * Build OFFSET
         */
        protected function _build_offset() {
            if (!is_null($this->_offset)) {
                return "OFFSET " . $this->_offset;
            }
            return '';
        }

        /**
         * Quote a string that is used as an identifier
         * (table names, column names etc). This method can
         * also deal with dot-separated identifiers eg table.column
         */
        protected function _quote_identifier($identifier) {
            $parts = explode('.', $identifier);
            $parts = array_map(array($this, '_quote_identifier_part'), $parts);
            return join('.', $parts);
        }

        /**
         * This method performs the actual quoting of a single
         * part of an identifier, using the identifier quote
         * character specified in the config (or autodetected).
         */
        protected function _quote_identifier_part($part) {
            if ($part === '*') {
                return $part;
            }
            $quote_character = $this->_identifier_quote_character;
            return $quote_character . $part . $quote_character;
        }

        /**
         * Wrapper around PHP's join function which
         * only adds the pieces if they are not empty.
         */
        protected function _join_if_not_empty($glue, $pieces) {
            $filtered_pieces = array();
            foreach ($pieces as $piece) {
                if (is_string($piece)) {
                    $piece = trim($piece);
                }
                if (!empty($piece)) {
                    $filtered_pieces[] = $piece;
                }
            }
            return join($glue, $filtered_pieces);
        }

}

class DeleteBuilder{

    public function __construct( $from ){
        $this->_table_name = $from;
    }

        // ----------------------- //
        // --- CLASS CONSTANTS --- //
        // ----------------------- //

        // ------------------------ //
        // --- CLASS PROPERTIES --- //
        // ------------------------ //


        // --------------------------- //
        // --- INSTANCE PROPERTIES --- //
        // --------------------------- //
        // The name of the table the current ORM instance is associated with
        protected $_identifier_quote_character;

        // The name of the table the current ORM instance is associated with
        protected $_table_name;


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


        /**
         */
        public function set_identifier_quote_character($identifier_quote_character) {
            $this->_identifier_quote_character = $identifier_quote_character;
            return $this;
        }

        /**
         * Perform a raw query. The query should contain placeholders,
         * in either named or question mark style, and the parameters
         * should be an array of values which will be bound to the
         * placeholders in the query. If this method is called, all
         * other query building methods will be ignored.
         */
        public function raw_query($query) {
            $this->_is_raw_query = true;
            $this->_raw_query = $query;
            return $this;
        }

        /**
         * Internal method to add a WHERE condition to the query
         */
        protected function _add_where($fragment) {
            $this->_where_conditions[] = $fragment;
            return $this;
        }

        /**
         * Helper method to compile a simple COLUMN SEPARATOR VALUE
         * style WHERE condition into a string and value ready to
         * be passed to the _add_where method. Avoids duplication
         * of the call to _quote_identifier
         */
        protected function _add_simple_where($column_name, $separator) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} {$separator} ?");
        }

        /**
         * Return a string containing the given number of question marks,
         * separated by commas. Eg "?, ?, ?"
         */
        protected function _create_placeholders($number_of_placeholders) {
            return join(", ", array_fill(0, $number_of_placeholders, "?"));
        }

        /**
         * Add a WHERE column = value clause to your query. Each time
         * this is called in the chain, an additional WHERE will be
         * added, and these will be ANDed together when the final query
         * is built.
         */
        public function where($column_name) {
            return $this->where_equal($column_name);
        }

        /**
         * More explicitly named version of for the where() method.
         * Can be used if preferred.
         */
        public function where_equal($column_name) {
            return $this->_add_simple_where($column_name, '=');
        }

        /**
         * Add a WHERE column != value clause to your query.
         */
        public function where_not_equal($column_name) {
            return $this->_add_simple_where($column_name, '!=');
        }

        /**
         * Add a WHERE ... LIKE clause to your query.
         */
        public function where_like($column_name) {
            return $this->_add_simple_where($column_name, 'LIKE');
        }

        /**
         * Add where WHERE ... NOT LIKE clause to your query.
         */
        public function where_not_like($column_name) {
            return $this->_add_simple_where($column_name, 'NOT LIKE');
        }

        /**
         * Add a WHERE ... > clause to your query
         */
        public function where_gt($column_name) {
            return $this->_add_simple_where($column_name, '>');
        }

        /**
         * Add a WHERE ... < clause to your query
         */
        public function where_lt($column_name) {
            return $this->_add_simple_where($column_name, '<');
        }

        /**
         * Add a WHERE ... >= clause to your query
         */
        public function where_gte($column_name) {
            return $this->_add_simple_where($column_name, '>=');
        }

        /**
         * Add a WHERE ... <= clause to your query
         */
        public function where_lte($column_name) {
            return $this->_add_simple_where($column_name, '<=');
        }

        /**
         * Add a WHERE ... IN clause to your query
         */
        public function where_in($column_name, $values_count) {
            $column_name = $this->_quote_identifier($column_name);
            $placeholders = $this->_create_placeholders( $values_count );
            return $this->_add_where("{$column_name} IN ({$placeholders})");
        }

        /**
         * Add a WHERE ... NOT IN clause to your query
         */
        public function where_not_in($column_name, $values_count) {
            $column_name = $this->_quote_identifier($column_name);
            $placeholders = $this->_create_placeholders($values_count);
            return $this->_add_where("{$column_name} NOT IN ({$placeholders})");
        }

        /**
         * Add a WHERE column IS NULL clause to your query
         */
        public function where_null($column_name) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} IS NULL");
        }

        /**
         * Add a WHERE column IS NOT NULL clause to your query
         */
        public function where_not_null($column_name) {
            $column_name = $this->_quote_identifier($column_name);
            return $this->_add_where("{$column_name} IS NOT NULL");
        }

        /**
         * Add a raw WHERE clause to the query. The clause should
         * contain question mark placeholders, which will be bound
         * to the parameters supplied in the second argument.
         */
        public function where_raw($clause) {
            return $this->_add_where($clause);
        }

        /**
         * Add a LIMIT to the query
         */
        public function limit($limit) {
            $this->_limit = $limit;
            return $this;
        }

        /**
         * Add an OFFSET to the query
         */
        public function offset($offset) {
            $this->_offset = $offset;
            return $this;
        }

        public function build(){
            return $this->_build_query();
        }

        /**
         * Build a SELECT statement based on the clauses that have
         * been passed to this instance by chaining method calls.
         */
        protected function _build_query() {
            // If the query is raw, just set the $this->_values to be
            // the raw query parameters and return the raw query
            if ($this->_is_raw_query) {
                return $this->_raw_query;
            }

            // Build and return the full SELECT statement by concatenating
            // the results of calling each separate builder method.
            return $this->_join_if_not_empty(" ", array(
                $this->_build_start(),
                $this->_build_where(),
                $this->_build_limit(),
                $this->_build_offset(),
            ));
        }

        /**
         * Build the start of the SELECT statement
         */
        protected function _build_start() {
            $fragment = "DELETE FROM  " . $this->_quote_identifier($this->_table_name);
            return $fragment;
        }

        /**
         * Build the WHERE clause(s)
         */
        protected function _build_where() {
            // If there are no WHERE clauses, return empty string
            if (count($this->_where_conditions) === 0) {
                return '';
            }

            $where_conditions = array();
            foreach ($this->_where_conditions as $condition) {
                $where_conditions[] = $condition;
            }

            return "WHERE " . join(" AND ", $where_conditions);
        }

        /**
         * Build LIMIT
         */
        protected function _build_limit() {
            if (!is_null($this->_limit)) {
                return "LIMIT " . $this->_limit;
            }
            return '';
        }

        /**
         * Build OFFSET
         */
        protected function _build_offset() {
            if (!is_null($this->_offset)) {
                return "OFFSET " . $this->_offset;
            }
            return '';
        }

        /**
         * Quote a string that is used as an identifier
         * (table names, column names etc). This method can
         * also deal with dot-separated identifiers eg table.column
         */
        protected function _quote_identifier($identifier) {
            $parts = explode('.', $identifier);
            $parts = array_map(array($this, '_quote_identifier_part'), $parts);
            return join('.', $parts);
        }

        /**
         * This method performs the actual quoting of a single
         * part of an identifier, using the identifier quote
         * character specified in the config (or autodetected).
         */
        protected function _quote_identifier_part($part) {
            if ($part === '*') {
                return $part;
            }
            $quote_character = $this->_identifier_quote_character;
            return $quote_character . $part . $quote_character;
        }

        /**
         * Wrapper around PHP's join function which
         * only adds the pieces if they are not empty.
         */
        protected function _join_if_not_empty($glue, $pieces) {
            $filtered_pieces = array();
            foreach ($pieces as $piece) {
                if (is_string($piece)) {
                    $piece = trim($piece);
                }
                if (!empty($piece)) {
                    $filtered_pieces[] = $piece;
                }
            }
            return join($glue, $filtered_pieces);
        }

}

