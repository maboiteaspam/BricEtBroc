<?php

$vendors_dir = dirname(__FILE__)."/vendors/";

include($vendors_dir."Yaml-1.0.6/lib/sfYaml.php");

include("Config.php");
include("ConfigBridge.php");
include("ConfigCache.php");
include("ConfigLoader.php");
include("Super_Array_walk_recursive.php");

include('test.php');