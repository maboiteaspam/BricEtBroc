<?php

$db_name        = "active_controller";
$db_user        = "clement";
$db_pwd         = "123456";

$use_cache      = true;
$use_debug      = true;
$frozen         = false;
$cache_path     = __DIR__ . "/entity_cache/";
$entity_path    = __DIR__ . "/entity/";
$print_success  = false;
$print_closure  = false;

include("../loader.php");




echo "Testing SQL buider";
new_line();
new_line();
new_line();

$tests_suite = array();

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->where("name","")->build(); },
    "SELECT `reflectiveentity`.* FROM `reflectiveentity` WHERE `reflectiveentity`.`name` = ?"
);

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->where("ReflectiveEntity.name","")->build(); },
    "SELECT `reflectiveentity`.* FROM `reflectiveentity` WHERE `reflectiveentity`.`name` = ?"
);

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->where("ReflectiveEntity.name","")->_print(); },
    "SELECT `reflectiveentity`.* FROM `reflectiveentity` WHERE `reflectiveentity`.`name` = ''"
);

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->where_in("ReflectiveEntity.name",array("","1","2"))->build(); },
    "SELECT `reflectiveentity`.* FROM `reflectiveentity` WHERE `reflectiveentity`.`name` IN (?, ?, ?)"
);

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->where_in("ReflectiveEntity.name",array("","1","2"))->_print(); },
    "SELECT `reflectiveentity`.* FROM `reflectiveentity` WHERE `reflectiveentity`.`name` IN ('', '1', '2')"
);

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->where_like("ReflectiveEntity.name", "t")->build(); },
    "SELECT `reflectiveentity`.* FROM `reflectiveentity` WHERE `reflectiveentity`.`name` LIKE ?"
);

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->where_like("ReflectiveEntity.name", "t")->_print(); },
    "SELECT `reflectiveentity`.* FROM `reflectiveentity` WHERE `reflectiveentity`.`name` LIKE 't'"
);

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->where_like("ReflectiveEntity.name", "%t")->build(); },
    "SELECT `reflectiveentity`.* FROM `reflectiveentity` WHERE `reflectiveentity`.`name` LIKE ?"
);

$tests_suite[] = array(
    function(){ return ReflectiveEntity::select()->where_like("ReflectiveEntity.name", "%t")->_print(); },
    "SELECT `reflectiveentity`.* FROM `reflectiveentity` WHERE `reflectiveentity`.`name` LIKE '%t'"
);

$tests_suite[] = array(
    function(){ return DumbEntity::select()->inner_join("DumbEntity.dumb_catalog")->where_like("DumbEntity.name", "%t")->build(); },
    "SELECT `dumbentity`.* FROM `dumbentity` INNER JOIN `catalog` ON ( `catalog`.`id` = `dumbentity`.`dumb_catalog_id` ) WHERE `dumbentity`.`name` LIKE ?"
);

$tests_suite[] = array(
    function(){ return DumbEntity::select()->inner_join("DumbEntity.dumb_catalog")->where("Catalog.id", "1")->build(); },
    "SELECT `dumbentity`.* FROM `dumbentity` INNER JOIN `catalog` ON ( `catalog`.`id` = `dumbentity`.`dumb_catalog_id` ) WHERE `catalog`.`id` = ?"
);

$tests_suite[] = array(
    function(){ return DumbEntity::select()->inner_join("DumbEntity.dumb_catalog", "c")->where("Catalog.id", "1")->build(); },
    "SELECT `dumbentity`.* FROM `dumbentity` INNER JOIN `catalog` ON ( `catalog`.`id` = `dumbentity`.`dumb_catalog_id` ) WHERE `catalog`.`id` = ?"
);





include("../ender.php");




