<?php
echo "<pre>";
/**
 * some checks.
 */
$vars = array(
    "db_name",
    "db_user",
    "db_pwd",
    "use_cache",
    "frozen",
    "cache_path",
    "entity_path",
);
foreach( $vars as $var ){
    if( isset($$var) === false ){
        die("missing init var ".$var);
    }
}


/**
 * Some functions to do the test
 *
 */
include("../../vendors/super_closure/SerializableClosure.php");
include("../../vendors/super_closure/SuperClosure.class.php");
/**
 * @param $size
 * @return string
 */
function convert($size){
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}
/**
 * @param int $multiplier
 */
function new_line($multiplier=1){
    echo str_repeat("\n", $multiplier);
}
/**
 * @param $builded_result
 * @param $expected_result
 * @return bool
 */
function test_sql_build( $builded_result, $expected_result ){
    return trim($builded_result) === trim($expected_result);
}



$test_suite_tester = function($test_func, $test_result, $expected_result){
    return test_sql_build($test_result, $expected_result);
};


$test_suite_failed = function($test_index, $test_func, $test_result, $expected_result){
    echo "<b>Test has failed at index $test_index</b>";
    new_line();
    echo("Result is         ".$test_result);
    new_line();
    echo("Result should be  ".$expected_result);
    new_line();
};
$test_suite_failed_wc = function($test_index, $test_func, $test_result, $expected_result){
    $closure    = new SuperClosure($test_func);
    $line       = $closure->startLine();
    echo "<b>Test has failed at index $test_index, line $line</b>";
    new_line();
    echo("Closure is        ".substr($closure->getCode(),19,-2));
    new_line();
    echo("Result is         ".$test_result);
    new_line();
    echo("Result should be  ".$expected_result);
    new_line();
};

$test_suite_succeed = function($test_index, $test_func, $test_result, $expected_result){
    echo "Test has succeed at index $test_index";
    new_line();
    echo("Result is         ".$expected_result);
};
$test_suite_succeed_wc = function($test_index, $test_func, $test_result, $expected_result){
    $closure    = new SuperClosure($test_func);
    $line       = $closure->startLine();
    echo "Test has succeed at index $test_index, line $line";
    new_line();
    echo("Closure is        ".substr($closure->getCode(),19,-2));
    new_line();
    echo("Result is         ".$expected_result);
};




class duration_recorder{
    public $time_start;
    public $check_points;
    public $end_check_points;
    public $duplicate_check_points;

    public function __construct() {
        $this->time_start               = microtime(true);
        $this->check_points             = array();
        $this->duplicate_check_points   = array();
    }

    public function add_checkpoint($name){
        if( isset($this->duplicate_check_points[$name]) ){
            $this->duplicate_check_points[$name]++;
            $name = $name."_".$this->duplicate_check_points[$name];
        }else{
            $this->duplicate_check_points[$name] = 0;
        }
        $this->check_points[$name] = microtime(true);
        return $name;
    }

    public function end_checkpoint($name){
        $this->end_check_points[$name] = microtime(true);
    }

    public function duration($name){
        return $this->end_check_points[$name] - $this->check_points[$name];
    }

    protected static $instance;
    /**
     *
     * @return duration_recorder
     */
    public static function inst(){
        if( self::$instance === null )
            self::$instance = new duration_recorder();
        return self::$instance;
    }
}
duration_recorder::inst()->add_checkpoint("global");

/**
 * Init some values
 */
new_line(1);
echo "Memory usage : ".( convert(memory_get_usage(true)) );
new_line(1);
echo "Peak memory usage : ".( convert(memory_get_peak_usage(true)) );
new_line(1);

/**
 * Include the required files
 */
include("../../vendors/Addedum-0.4.1/annotations.php");
include("../../libs/__include_files.php");

/**
 * display some infos about memory
 */
new_line(1);
echo "Memory usage : ".( convert(memory_get_usage(true)) );
new_line(1);
echo "Peak memory usage : ".( convert(memory_get_peak_usage(true)) );
new_line(1);



/**
 * Here we define the entity folder
 *  This folder must contain all your entities
 */
ActiveModelController::autoload( $entity_path );


/**
 * Factory / get active controller
 */
$ActiveModelController = ActiveModelController::get_instance();
// define it as active one if needed
// ActiveModelController::set_active_instance( $ActiveModelController );

/**
 * Set the cache path
 */
$ActiveModelController->setCachePath( $cache_path );

/**
 * Use cache, or not
 */
$ActiveModelController->useCache($use_cache);

/**
 * Frozen or not
 */
$ActiveModelController->setFrozen($frozen);

/**
 * Define a modeler to work with
 */
$Modeler    = new MySQLModeler();

/**
 * this is the db layer, i used to work with pdo
 *  maybe you do prefer mysql !
 */
$pdo        = new PDO('mysql:dbname='.$db_name.';host=127.0.0.1', $db_user, $db_pwd);

/**
 * This are callback to prepare
 * and execute queries
 * You may use different db layer like mysql etc
 */
$Modeler->setCallback(  function($sql) use($pdo){return $pdo->exec($sql);},
                        function($sql) use($pdo){return $pdo->query($sql);});
/**
 * This is the db name we are
 * working on
 */
$Modeler->setContainerName( $db_name );
$ActiveModelController->setModeler($Modeler);

/**
 * Define a query builder to work with
 *
 * The QueryBuilder is responsible to produce only SQL
 *
 * The ActiveQueryBuilder is responsible to take input
 *  values and bind them to SQL query, hardly depends of your db layer (pdo / mysql etc)
 *
 * The ActiveModelQueryBuilder is responsible to help you
 *  to work with objects relationships, it does implements helper to
 *  simplify your day to day work against your model
 *
 */
$query_builder = new QueryBuilder();
$query_builder->set_identifier_quote_character("`");

$active_query_builder = new ActiveQueryBuilder($query_builder);
/**
 * This are callback to prepare
 * and execute queries
 * You may use different db layer like mysql etc
 */
$active_query_builder->setCallback( function($sql, $values) use($pdo)
                                        {return $pdo->prepare($sql);},
                                    function($stmt, $values) use($pdo)
                                        {$stmt->execute($values); return $stmt->fetchAll(PDO::FETCH_ASSOC);});

$active_model_query_builder = new ActiveModelQueryBuilder( $active_query_builder );

/**
 * We have to bind this builder to our controller
 */
$ActiveModelController->setModelQueryBuilder($active_model_query_builder);



new_line(1);
echo "Memory usage : ".( convert(memory_get_usage(true)) );
new_line(1);
echo "Peak memory usage : ".( convert(memory_get_peak_usage(true)) );
new_line(1);
new_line(2);

