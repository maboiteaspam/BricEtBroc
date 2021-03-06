<?php


include("../../libs/__include_files.php");

use BricEtBroc\Form\FormValidator as FormValidator;
use BricEtBroc\Form\InputValues as InputValues;

$options = array('rules' => array(
                    'testfield' => 'required',
                    )
                );
$validator = new FormValidator("f_testform", $options);

if( $_SERVER['REQUEST_METHOD'] === "POST" ){
    $validator->setInputValues(new InputValues($_POST) );
    $validator->validate();
    if( $validator->hasErrors() === false ){
        echo "ok";
    }
}

?>
<html>
    <head>
        <script type="text/javascript" src="../../vendors/jquery-validation-1.9.0pre/lib/jquery-1.6.1.js"></script>
        <script type="text/javascript" src="../../vendors/jquery-validation-1.9.0pre/lib/jquery.form.js"></script>
        <script type="text/javascript" src="../../vendors/jquery-validation-1.9.0pre/jquery.validate.js"></script>
    </head>
    <body>
        <form name="f_testform" method="POST" action="">
            <label for="testfield">Input field</label>
            <input type="text" name="testfield" id="testfield" value="" />
            <br/>
            <input type="submit" value="Submit !" />
        </form>
        <?php echo $validator->__toHTML(); ?>
    </body>
</html>