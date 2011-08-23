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
