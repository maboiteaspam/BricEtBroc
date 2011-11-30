<?php

$db_name        = "active_controller";
$db_user        = "clement";
$db_pwd         = "123456";

$use_cache      = true;
$use_debug      = true;
$frozen         = false;
$cache_path     = __DIR__ . "/entity_cache/";
$entity_path    = __DIR__ . "/entity/";

$print_success = false;
$print_closure = false;

include("../loader.php");




echo "Testing SQL buider";
new_line();
new_line();
new_line();

$tests_suite = array();

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->build(); },
    "SELECT `reflectiveentity`.* FROM `reflectiveentity`"
);
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
    function(){ return Catalog::select()->inner_join("Product")->on("Product.tomate","Catalog.products")->build(); },
    "SELECT `catalog`.* FROM `catalog` INNER JOIN `product` ON ( `catalog`.`id` = `product`.`tomate_id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->inner_join("Product")->on("Product.catalog","Catalog.products")->build(); },
    "SELECT `catalog`.* FROM `catalog` INNER JOIN `product` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);

$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Catalog")->on("Product.catalog", "Catalog.products")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Catalog")->on("Catalog.products", "Product.catalog")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Catalog")->on("Product.tomate", "Catalog.products")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`tomate_id` )"
);

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->as_("children")->inner_join("ReflectiveEntity", "parent")->on("children.parent_reflective", "parent.children_reflective")->build(); },
    "SELECT `children`.* FROM `reflectiveentity` AS `children` INNER JOIN `reflectiveentity` AS `parent` ON ( `parent`.`id` = `children`.`parent_reflective_id` )"
);

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->as_("parent")->inner_join("ReflectiveEntity", "children")->on("parent.children_reflective", "children.parent_reflective")->build(); },
    "SELECT `parent`.* FROM `reflectiveentity` AS `parent` INNER JOIN `reflectiveentity` AS `children` ON ( `parent`.`id` = `children`.`parent_reflective_id` )"
);

$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Catalog")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);

$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Catalog")->on("Product.tomate")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`tomate_id` )"
);

$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Catalog")->on("Product")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);

$tests_suite[] = array(
    function(){ return Product::select()->inner_join("catalog")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("tomate")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`tomate_id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("tomate")->on("Catalog.products")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`tomate_id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("tomate")->on("Catalog.products", "Product.tomate")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`tomate_id` )"
);

$tests_suite[] = array(
    function(){ return Catalog::select()->inner_join("products")->build(); },
    "SELECT `catalog`.* FROM `catalog` INNER JOIN `product` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->inner_join("Catalog.products")->build(); },
    "SELECT `catalog`.* FROM `catalog` INNER JOIN `product` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->inner_join("Catalog.products")->on("Product.tomate")->build(); },
    "SELECT `catalog`.* FROM `catalog` INNER JOIN `product` ON ( `catalog`.`id` = `product`.`tomate_id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->inner_join("Catalog.products")->on("Catalog")->build(); },
    "SELECT `catalog`.* FROM `catalog` INNER JOIN `product` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);

$tests_suite[] = array(
    function(){ return Catalog::select()->as_("c")->inner_join("products")->build(); },
    "SELECT `c`.* FROM `catalog` AS `c` INNER JOIN `product` ON ( `c`.`id` = `product`.`catalog_id` )"
);

$tests_suite[] = array(
    function(){ return Catalog::select()->as_("c")->inner_join("products", "p")->build(); },
    "SELECT `c`.* FROM `catalog` AS `c` INNER JOIN `product` AS `p` ON ( `c`.`id` = `p`.`catalog_id` )"
);

$tests_suite[] = array(
    function(){ return Catalog::select()->as_("c")->inner_join("c.products")->build(); },
    "SELECT `c`.* FROM `catalog` AS `c` INNER JOIN `product` ON ( `c`.`id` = `product`.`catalog_id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->as_("c")->inner_join("c.products", "p")->on("p.tomate")->build(); },
    "SELECT `c`.* FROM `catalog` AS `c` INNER JOIN `product` AS `p` ON ( `c`.`id` = `p`.`tomate_id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->as_("c")->inner_join("Product")->on("Product.tomate","c.products")->build(); },
    "SELECT `c`.* FROM `catalog` AS `c` INNER JOIN `product` ON ( `c`.`id` = `product`.`tomate_id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->inner_join("Product", "p")->on("p.tomate","Catalog.products")->build(); },
    "SELECT `catalog`.* FROM `catalog` INNER JOIN `product` AS `p` ON ( `catalog`.`id` = `p`.`tomate_id` )"
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
    function(){ return Product::select()->inner_join("Color")->on("Color.products","Product.colors")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `colors_products` ON ( `colors_products`.`product_id` = `product`.`id` ) INNER JOIN `color` ON ( `colors_products`.`color_id` = `color`.`id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Color")->on("Product.colors","Color.products")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `colors_products` ON ( `colors_products`.`product_id` = `product`.`id` ) INNER JOIN `color` ON ( `colors_products`.`color_id` = `color`.`id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Color")->on("Product.colors")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `colors_products` ON ( `colors_products`.`product_id` = `product`.`id` ) INNER JOIN `color` ON ( `colors_products`.`color_id` = `color`.`id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Product.colors")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `colors_products` ON ( `colors_products`.`product_id` = `product`.`id` ) INNER JOIN `color` ON ( `colors_products`.`color_id` = `color`.`id` )"
);
$tests_suite[] = array(
    function(){ return Color::select()->inner_join("Product")->on("Color.products","Product.colors")->build(); },
    "SELECT `color`.*, `colors_products`.`color_position` FROM `color` INNER JOIN `colors_products` ON ( `colors_products`.`color_id` = `color`.`id` ) INNER JOIN `product` ON ( `colors_products`.`product_id` = `product`.`id` )"
);
$tests_suite[] = array(
    function(){ return Color::select()->inner_join("Color.products")->build(); },
    "SELECT `color`.*, `colors_products`.`color_position` FROM `color` INNER JOIN `colors_products` ON ( `colors_products`.`color_id` = `color`.`id` ) INNER JOIN `product` ON ( `colors_products`.`product_id` = `product`.`id` )"
);



$tests_suite[] = array(
    function(){ return DumbEntity::select()->inner_join("Catalog")->on("DumbEntity.dumb_catalog", "Catalog")->build(); },
    "SELECT `dumbentity`.* FROM `dumbentity` INNER JOIN `catalog` ON ( `catalog`.`id` = `dumbentity`.`dumb_catalog_id` )"
);
$tests_suite[] = array(
    function(){ return DumbEntity::select()->inner_join("DumbEntity.dumb_catalog")->build(); },
    "SELECT `dumbentity`.* FROM `dumbentity` INNER JOIN `catalog` ON ( `catalog`.`id` = `dumbentity`.`dumb_catalog_id` )"
);
$tests_suite[] = array(
    function(){ return DumbEntity::select()->inner_join("Catalog")->on("DumbEntity.dumb_catalog")->inner_join("Catalog.products")->build(); },
    "SELECT `dumbentity`.* FROM `dumbentity` INNER JOIN `catalog` ON ( `catalog`.`id` = `dumbentity`.`dumb_catalog_id` ) INNER JOIN `product` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);
$tests_suite[] = array(
    function(){ return DumbEntity::select()->inner_join("DumbEntity.dumb_catalog")->inner_join("Catalog.products")->build(); },
    "SELECT `dumbentity`.* FROM `dumbentity` INNER JOIN `catalog` ON ( `catalog`.`id` = `dumbentity`.`dumb_catalog_id` ) INNER JOIN `product` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);


$tests_suite[] = array(
    function(){ return Product::select()->as_("p")->inner_join("p.tomate", "c")->build(); },
    "SELECT `p`.* FROM `product` AS `p` INNER JOIN `catalog` AS `c` ON ( `c`.`id` = `p`.`tomate_id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->as_("c")->inner_join("Product.catalog", "p")->on("c")->build(); },
    "SELECT `c`.* FROM `catalog` AS `c` INNER JOIN `product` AS `p` ON ( `c`.`id` = `p`.`catalog_id` )"
);
$tests_suite[] = array(
    function(){ return Catalog::select()->as_("c")->inner_join("c.products", "p")->on("c")->build(); },
    "SELECT `c`.* FROM `catalog` AS `c` INNER JOIN `product` AS `p` ON ( `c`.`id` = `p`.`catalog_id` )"
);


$tests_suite[] = array(
    function(){ return Product::select()->as_("p")->inner_join("sometable")->on("p.id","sometable.id_product")->on("p.id","sometable.id_product2")->build(); },
    "SELECT `p`.* FROM `product` AS `p` INNER JOIN `sometable` ON ( `p`.`id` = `sometable`.`id_product` AND  `p`.`id` = `sometable`.`id_product2` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->as_("p")->inner_join("sometable")->on_not_equal("p.id","sometable.id_product")->build(); },
    "SELECT `p`.* FROM `product` AS `p` INNER JOIN `sometable` ON ( `p`.`id` != `sometable`.`id_product` )"
);


$tests_suite[] = array(
    function(){ return Product::select()->as_("p")->inner_join("sometable")->on("sometable.id_product","p.id")->on("sometable.id_product2","p.id")->build(); },
    "SELECT `p`.* FROM `product` AS `p` INNER JOIN `sometable` ON ( `sometable`.`id_product` = `p`.`id` AND  `sometable`.`id_product2` = `p`.`id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->as_("p")->inner_join("sometable")->on_not_equal("sometable.id_product", "p.id")->build(); },
    "SELECT `p`.* FROM `product` AS `p` INNER JOIN `sometable` ON ( `sometable`.`id_product` != `p`.`id` )"
);

$tests_suite[] = array(
    function(){ return Product::select()->as_("p")->inner_join("sometable", "s")->on_not_equal("s.id_product", "p.id")->build(); },
    "SELECT `p`.* FROM `product` AS `p` INNER JOIN `sometable` AS `s` ON ( `s`.`id_product` != `p`.`id` )"
);

$tests_suite[] = array(
    function(){ return Product::select()->as_("p")->inner_join("sometable", "s")->on_not_equal("sometable.id_product", "p.id")->build(); },
    "SELECT `p`.* FROM `product` AS `p` INNER JOIN `sometable` AS `s` ON ( `s`.`id_product` != `p`.`id` )"
);

/**
 *
 * Those two tests can not work because the inner join
 *  - is using a property
 *  - the property is related to an unknown model in the query
 *
 * So, in a join, if a property is given, it must belong to a known model
 *
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Catalog.products")->on("Product.catalog")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);
$tests_suite[] = array(
    function(){ return Product::select()->inner_join("Catalog.products")->on("Product.catalog", "Catalog.products")->build(); },
    "SELECT `product`.* FROM `product` INNER JOIN `catalog` ON ( `catalog`.`id` = `product`.`catalog_id` )"
);
 */



include("../ender.php");




