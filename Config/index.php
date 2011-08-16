<?php

$vendors_dir = dirname(__FILE__)."/vendors/";

include($vendors_dir."Yaml-1.0.6/lib/sfYaml.php");

include("Container.php");
include("Bridge.php");
include("Cache.php");
include("FileLoader.php");
include("Super_Array_walk_recursive.php");

include('test.php');