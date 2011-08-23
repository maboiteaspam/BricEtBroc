<?php
function assert_true( $description, $result_operation){
    if( $result_operation === true ){
        
    }else{
        $backtrace = debug_backtrace(1);
        $line = $backtrace[0]["line"];
        echo "TEST FAILED on line ".$line." : ".$description.", result was ".  var_export($result_operation,true)."\n";
    }
}
function assert_false( $description, $result_operation){
    if( $result_operation === false ){
        
    }else{
        $backtrace = debug_backtrace(1);
        $line = $backtrace[0]["line"];
        echo "TEST FAILED on line ".$line." : ".$description.", result was ".  var_export($result_operation,true)."\n";
    }
}

function create_file( $content, $ext ){
    static $file_incr = 0;
    $this_dir = dirname(__FILE__)."/";
    $f_name = $this_dir.time()."-".$file_incr.".".$ext;
    file_put_contents($f_name, $content);
    $file_incr++;
    return $f_name;
}
function rrmdir($dir) { 
    if (is_dir($dir)) { 
        $objects = scandir($dir); 
        foreach ($objects as $object) { 
            if ($object != "." && $object != "..") { 
                if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
            } 
        } 
        reset($objects); 
        rmdir($dir); 
    } 
}

use BricEtBroc\Config\Container as Container;
use BricEtBroc\Config\Cache as Cache;
use BricEtBroc\Config\FileLoader as FileLoader;

/*******************************************************************************
 * 
 */
$values = array("test"=>"value");
$Config = new Container( $values );

assert_true("Test read config value", in_array("test", array_keys($Config->getArrayCopy())) );
assert_true("Test read config value", $Config["test"]==="value" );

/*******************************************************************************
 * 
 */
$yaml_content = "
test: Simple Alias Example
brief: >
    If you need to refer to the same item of data twice,
    you can give that item an alias.  The alias is a plain
    string, starting with an ampersand.  The item may then
    be referred to by the alias throughout your document
    by using an asterisk before the name of the alias.
    This is called an anchor.
yaml: |
    - &showell Steve
    - Clark
    - Brian
    - Oren
    - *showell
";
$yaml_file      = create_file( $yaml_content, "yml" );
$loader         = FileLoader::loadFile($yaml_file);
$Config         = $loader->getData();
$Config_files   = $loader->getMergedFiles();
assert_true("Test read config value", in_array("test", array_keys($Config)) );
assert_true("Test read config value", $Config["test"]==="Simple Alias Example" );
assert_true("Test files infos", in_array($yaml_file, $Config_files) );
unlink($yaml_file);

/*******************************************************************************
 * 
 */
$yaml_content2 = "
test: Simple Alias Example2
test2: Simple Alias Example
";
$yaml_file2         = create_file( $yaml_content2, "yml" );
$yaml_content = "
test: Simple Alias Example
extern_file: ::@$yaml_file2
";
$yaml_file      = create_file( $yaml_content, "yml" );
$loader         = FileLoader::loadFile($yaml_file);
$Config         = $loader->getData();
$Config_files   = $loader->getMergedFiles();
assert_true("Test read config value", in_array("test", array_keys($Config)) );
assert_true("Test read config value", in_array("extern_file", array_keys($Config)) );
assert_true("Test read config value", is_array($Config["extern_file"]) );
assert_true("Test read config value", $Config["test"]==="Simple Alias Example" );
assert_true("Test files infos", isset($Config["extern_file"]["test"]) );
assert_true("Test read config value", $Config["extern_file"]["test2"]==="Simple Alias Example" );
assert_true("Test files infos", in_array($yaml_file, $Config_files) );
assert_true("Test files infos", in_array($yaml_file2, $Config_files) );
unlink($yaml_file);
unlink($yaml_file2);

/*******************************************************************************
 * 
 */
$cache_dir = dirname(__FILE__)."/cache";
if(is_dir($cache_dir) === false)
 mkdir( $cache_dir );

$yaml_content2 = "
test: Simple Alias Example2
test2: Simple Alias Example
";
$yaml_file2   = create_file( $yaml_content2, "yml" );
$yaml_content = "
test: Simple Alias Example
extern_file: ::@$yaml_file2
";
$yaml_file      = create_file( $yaml_content, "yml" );

$loader         = FileLoader::loadFile($yaml_file);
$Config         = $loader->getData();
$Config_files   = $loader->getMergedFiles();

assert_true("Test save config", Cache::save($cache_dir, $loader) );
$cached_config = Cache::load($cache_dir, $yaml_file);

assert_true("verify cached_config == config", $Config == $cached_config );
assert_true("Test read config value", is_array($cached_config["extern_file"]) );
assert_true("Test read config value", $cached_config["test"]==="Simple Alias Example" );
assert_true("Test files infos", isset($cached_config["extern_file"]["test"]) );
assert_true("Test read config value", $cached_config["extern_file"]["test2"]==="Simple Alias Example" );
assert_true("Test files infos", in_array($yaml_file, $Config_files) );
assert_true("Test files infos", in_array($yaml_file2, $Config_files) );

assert_true("Test that cache is fresh", Cache::isFresh($cache_dir, $yaml_file));
sleep(1);
clearstatcache();
touch( $yaml_file2 );
assert_false("Test that cache is not fresh", Cache::isFresh($cache_dir, $yaml_file));

unlink($yaml_file);
assert_false("Test that cache is not fresh", Cache::isFresh($cache_dir, $yaml_file));


unlink($yaml_file2);

rrmdir($cache_dir);

/*******************************************************************************
 * 
 */
$cache_dir = dirname(__FILE__)."/cache";
if(is_dir($cache_dir) === false)
 mkdir( $cache_dir );

$yaml_content2 = "
test: Simple Alias Example2
test2: Simple Alias Example
";
$yaml_file2         = create_file( $yaml_content2, "yml" );
$yaml_content = "
test: Simple Alias Example
extern_file: ::@".  basename($yaml_file2)."
";
$yaml_file      = create_file( $yaml_content, "yml" );

$loader         = FileLoader::loadFile($yaml_file);
$Config         = $loader->getData();
$Config_files   = $loader->getMergedFiles();

assert_true("Test save config", Cache::save($cache_dir, $loader) );
$cached_config = Cache::load($cache_dir, $yaml_file);

assert_true("verify cached_config == config", $Config == $cached_config );
assert_true("Test read config value", is_array($cached_config["extern_file"]) );
assert_true("Test read config value", $cached_config["test"]==="Simple Alias Example" );
assert_true("Test files infos", isset($cached_config["extern_file"]["test"]) );
assert_true("Test read config value", $cached_config["extern_file"]["test2"]==="Simple Alias Example" );
assert_true("Test files infos", in_array($yaml_file, $Config_files) );
assert_true("Test files infos", in_array($yaml_file2, $Config_files) );

assert_true("Test that cache is fresh", Cache::isFresh($cache_dir, $yaml_file));
sleep(1);
clearstatcache();
touch( $yaml_file2 );
assert_false("Test that cache is not fresh", Cache::isFresh($cache_dir, $yaml_file));

unlink($yaml_file);
assert_false("Test that cache is not fresh", Cache::isFresh($cache_dir, $yaml_file));


unlink($yaml_file2);

rrmdir($cache_dir);




