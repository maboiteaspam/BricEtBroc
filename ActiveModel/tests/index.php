<?php

$current_dir    = __DIR__;
$dirs           = array();
foreach( scandir($current_dir) as $f ){
    if( in_array($f, array(".","..")) == false
         && is_dir($current_dir."/".$f) ){
        $dirs[] = $f;
    }
}
?>

<html>
<head></head>
<body style="margin: 0;">
    <div style="float: left; width:100px; padding: 5px;">
        <?php foreach( $dirs as $dir ){ ?>
            <a href="<?php echo $dir; ?>/" target="results"><?php echo ucfirst(str_replace("_", " ", $dir)); ?></a><br/>
        <?php } ?>
    </div>
    <iframe name="results" style="float: left; width: 1050px; height: 100%; border-width: 0px; border-style: solid; border-left-width: 1px;"></iframe>
</body>
</html>