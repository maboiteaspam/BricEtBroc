<?php


include("../../libs/__include_files.php");

use BricEtBroc\Form\FormFilter as FormFilter;
use BricEtBroc\Form\InputValues as InputValues;

$options = array('testfield' => 'trim');
$filter = new FormFilter("f_testform", array("filter"=>$options));

if( $_SERVER['REQUEST_METHOD'] === "POST" ){
    $in_values = new InputValues($_POST);
    $filter->setInputValues( $in_values );
    $filter->filter();
    $post_data = $in_values->getDataSource();
}

?>
<html>
    <head>
        <script type="text/javascript" src="../../vendors/jquery-validation/lib/jquery-1.6.1.js"></script>
        <script type="text/javascript" src="../../libs/Filter/filter.js"></script>
    </head>
    <body>
        <form name="f_testform" method="POST" action="">
            <label for="testfield">Input field</label>
            <input type="text" name="testfield" id="testfield" value="" />
            <br/>
            <input type="submit" value="Submit !" />
        </form>
        <?php echo $filter->__toHTML(); ?>
    </body>
</html>