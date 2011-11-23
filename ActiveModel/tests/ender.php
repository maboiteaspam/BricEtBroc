<?php

new_line(1);
echo( convert(memory_get_usage(true)) );
new_line(1);
echo( convert(memory_get_peak_usage(true)) );
new_line(1);
echo "</pre>";


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