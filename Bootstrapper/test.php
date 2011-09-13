<?php

include("lib/__include_files.php");

$Bt = new BricEtBroc\BootStrapper\BootStrapper();

$Bt->set("bootstrapp_files", array());
$Bt->add_event_listener("configure_bootstrap", function( $Bt ){
    $files = $Bt->get("bootstrapp_files");
    foreach ($files as $file) {
        include($file);
    }
});
$Bt->trigger_event("configure_bootstrap");
$Bt->trigger_event("start_app");
