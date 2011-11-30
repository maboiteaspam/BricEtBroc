<?php

$print_success = isset($print_success)?$print_success:false;
$print_closure = isset($print_closure)?$print_closure:false;


if( !$print_closure ){
    $test_suite_succeed = $test_suite_succeed_wc;
    $test_suite_failed  = $test_suite_failed_wc;
}



$number_of_builded_request  = 0;
$number_of_failed_request   = 0;
for( $i=0; $i<1;$i++){
    foreach( $tests_suite as $test_index=>$test_case ){

        $test_func          = $test_case[0];
        $test_result        = $test_func();
        $expected_result    = $test_case[1];

        if( $test_suite_tester($test_func, $test_result, $expected_result) ){
            if( $print_success ){
                $test_suite_succeed($test_index, $test_func, $test_result, $expected_result);
                new_line(2);
            }
        }else{
            $test_suite_failed($test_index, $test_func, $test_result, $expected_result);
            $number_of_failed_request++;
            new_line(2);
        }
        $number_of_builded_request++;
    }
}

new_line();
echo "builded tests : ".$number_of_builded_request;
new_line();
echo "failed tests : ".$number_of_failed_request;
new_line();

new_line(1);
echo "Memory usage : ".( convert(memory_get_usage(true)) );
new_line(1);
echo "Peak memory usage : ".( convert(memory_get_peak_usage(true)) );
new_line(1);

new_line(2);
duration_recorder::inst()->end_checkpoint("global");

echo "<table>";
        echo "<tr>";
            echo "<td width='200'>name</td>";
            echo "<td width='200'>start</td>";
            echo "<td width='200'>end</td>";
            echo "<td width='200'>duration</td>";
        echo "</tr>";
    foreach( duration_recorder::inst()->check_points as $name => $start ){
        $end = duration_recorder::inst()->end_check_points[$name];
        $duration = duration_recorder::inst()->duration($name);
        echo "<tr>";
            echo "<td>".$name."</td>";
            echo "<td>".$start."</td>";
            echo "<td>".$end."</td>";
            echo "<td>".$duration."</td>";
        echo "</tr>";
    }
echo "</table>";


echo "</pre>";