<?php

function assert_true( $description, $result_operation){
    if( $result_operation === true ){
        
    }else{
        $backtrace = debug_backtrace(1);
        $line = $backtrace[0]["line"];
        echo "TEST FAILED on line ".$line." : ".$description.", result was ".  var_export($result_operation,true)."<br/>\n";
    }
}
function assert_false( $description, $result_operation){
    if( $result_operation === false ){
        
    }else{
        $backtrace = debug_backtrace(1);
        $line = $backtrace[0]["line"];
        echo "TEST FAILED on line ".$line." : ".$description.", result was ".  var_export($result_operation,true)."<br/>\n";
    }
}

