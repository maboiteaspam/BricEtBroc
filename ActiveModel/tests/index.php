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

<?php foreach( $dirs as $dir ){ ?>
    <a href="<?php echo $dir; ?>/"><?php echo ucfirst(str_replace("_", " ", $dir)); ?></a>
<?php } ?>