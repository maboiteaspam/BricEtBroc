<?php

$db_name        = "active_controller";
$db_user        = "clement";
$db_pwd         = "123456";

$use_cache      = true;
$use_debug      = true;
$frozen         = true;
$cache_path     = __DIR__ . "/entity_cache/";
$entity_path    = __DIR__ . "/entity/";

include("../loader.php");




echo "Testing SQL buider";
new_line();

$tests_suite = array();
$tests_suite[] = array(
    function(){ return Catalog::select()->build(); },
    "SELECT `catalog`.* FROM `catalog`"
);
$tests_suite[] = array(
    function(){ return Product::select()->build(); },
    "SELECT `product`.* FROM `product`"
);
$tests_suite[] = array(
    function(){ return Color::select()->build(); },
    "SELECT `color`.* FROM `color`"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->inner_join("Product.tomate")->on("Catalog")->build(); },
    "SELECT `catalog`.* FROM `catalog` INNER JOIN `product` ON ( `product`.`tomate_id` = `catalog`.`id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->inner_join("Product.catalog")->on("Catalog")->build(); },
    "SELECT `catalog`.* FROM `catalog` INNER JOIN `product` ON ( `product`.`catalog_id` = `catalog`.`id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->inner_join("Catalog.products")->on("Catalog")->build(); },
    "SELECT `catalog`.* FROM `catalog` INNER JOIN `product` ON ( `product`.`catalog_id` = `catalog`.`id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Catalog.products")->on("Product")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `product`.`catalog_id` = `catalog`.`id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Color.products")->on("Product")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `colors_products` ON ( `product`.`id` = `colors_products`.`product_id` ) INNER JOIN `color` ON ( `color`.`id` = `colors_products`.`color_id` )"
);
$tests_suite[] = array(
    function(){ return Color::select()->inner_join("Product.colors")->on("Color")->build(); },
    "SELECT `color`.*, `colors_products`.`color_position` FROM `color` INNER JOIN `colors_products` ON ( `color`.`id` = `colors_products`.`color_id` ) INNER JOIN `product` ON ( `product`.`id` = `colors_products`.`product_id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("catalog")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `product`.`catalog_id` = `catalog`.`id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("tomate")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `product`.`tomate_id` = `catalog`.`id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Catalog")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `product`.`catalog_id` = `catalog`.`id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Product.catalog")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `product`.`catalog_id` = `catalog`.`id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Product.tomate")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `product`.`tomate_id` = `catalog`.`id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->inner_join("DumbEntity.dumb_catalog")->on("Catalog")->build(); },
    "SELECT `catalog`.* FROM `catalog` INNER JOIN `dumbentity` ON ( `dumbentity`.`dumb_catalog_id` = `catalog`.`id` )"
);
$tests_suite[] = array(
    function(){ return DumbEntity::select()->inner_join("Catalog.dumb_entity")->on("DumbEntity")->build(); },
    "SELECT `dumbentity`.* FROM `dumbentity` INNER JOIN `catalog` ON ( `catalog`.`dumb_entity_id` = `dumbentity`.`id` )"
);
$tests_suite[] = array(
    function(){ return DumbEntity::select()->inner_join("Catalog.dumb_entity")->on("DumbEntity")->inner_join("Catalog.products")->build(); },
    "SELECT `dumbentity`.* FROM `dumbentity` INNER JOIN `catalog` ON ( `catalog`.`dumb_entity_id` = `dumbentity`.`id` ) INNER JOIN `product` ON ( `product`.`catalog_id` = `catalog`.`id` )"
);
$tests_suite[] = array(
    function(){ return Color::select("id")->select("name")->build(); },
    "SELECT `id`, `name` FROM `color`"
);
$tests_suite[] = array(
    function(){ return Color::select("color.id")->select("color.name")->build(); },
    "SELECT `color`.`id`, `color`.`name` FROM `color`"
);
$tests_suite[] = array(
    function(){ return Color::select()->table_alias("test")->build(); },
    "SELECT `test`.* FROM `color` AS `test`"
);
$tests_suite[] = array(
    function(){ return Color::select()->count()->build(); },
    "SELECT COUNT(*) AS `count` FROM `color`"
);
$tests_suite[] = array(
    function(){ return Color::select()->table_alias("test")->count()->build(); },
    "SELECT COUNT(*) AS `count` FROM `color` AS `test`"
);
$tests_suite[] = array(
    function(){ return Color::select("id")->select("name")->limit(0)->offset(15)->build(); },
    "SELECT `id`, `name` FROM `color` LIMIT 0 OFFSET 15"
);
$tests_suite[] = array(
    function(){ return Product::select()->as_("p")->inner_join("p.tomate", "c")->build(); },
    "SELECT `p`.* FROM `product` AS `p` INNER JOIN `catalog` AS `c` ON ( `p`.`tomate_id` = `c`.`id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->as_("c")->inner_join("Product.catalog", "p")->on("c")->build(); },
    "SELECT `c`.* FROM `catalog` AS `c` INNER JOIN `product` AS `p` ON ( `p`.`catalog_id` = `c`.`id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->as_("p")->inner_join("sometable")->on("p.id","sometable.id_product")->on("p.id","sometable.id_product2")->build(); },
    "SELECT `p`.* FROM `product` AS `p` INNER JOIN `sometable` ON ( `p`.`id` = `sometable`.`id_product` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->as_("p")->inner_join("sometable")->on_not_equal("p.id","sometable.id_product")->build(); },
    "SELECT `p`.* FROM `product` AS `p` INNER JOIN `sometable` ON ( `p`.`id` = `sometable`.`id_product` )"
);





$number_of_builded_request  = 0;
$with_printed_closure       = !!true;
for( $i=0; $i<1;$i++){
    foreach( $tests_suite as $index=>$test_case ){
        $func               = $test_case[0];
        $exec_result        = $func();
        $expected_result    = $test_case[1];
        if( $with_printed_closure ) $closure = new SuperClosure($func);
        if(test_sql_build($exec_result, $test_case[1]) === false ){
            echo "<b>Test has failed at index $index</b>";
            new_line();
            if( $with_printed_closure ) echo("Closure is        ".substr($closure->getCode(),19,-2));
            if( $with_printed_closure ) new_line();
            echo("Result is         ".$exec_result);
            new_line();
            echo("Result should be  ".$expected_result);
            new_line();
        }else{
            echo "Test has succeed at index $index";
            new_line();
            if( $with_printed_closure ) echo("Closure is        ".substr($closure->getCode(),19,-2));
            if( $with_printed_closure ) new_line();
            echo("Result is         ".$exec_result);
            $number_of_builded_request++;
        }
        new_line(2);
    }
}

new_line();
echo "builded request : ".$number_of_builded_request;
new_line();
include("../ender.php");




