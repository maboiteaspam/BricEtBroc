<?php


include("../../libs/__include_files.php");

use BricEtBroc\Form\FormValidator as FormValidator;
use BricEtBroc\Form\InputValues as InputValues;
use BricEtBroc\Form\InputValueAccessor as InputValueAccessor;

$options = array('rules' => array(
                    'testfield' => 
                    array('remote'=>function(InputValueAccessor $valueAccessor){
                                    return $valueAccessor->read() !== "test" ;
                                }
                            ),
                    ),
                );
$validator = new FormValidator("f_testform", $options);

if( $_SERVER['REQUEST_METHOD'] === "POST" ){
    
    $validator->setInputValues(new InputValues($_POST) );
    
    $is_remote = false;
    if( isset($_SERVER['HTTP_X_REQUESTED_WITH']) ){
        $is_remote = $_SERVER['HTTP_X_REQUESTED_WITH']==="XMLHttpRequest";
    }
    
    if( $is_remote ){
        $remote_validator_id = isset($_GET["vid"])?$_GET["vid"]:"";
        $validator->validate($remote_validator_id);
        if( $validator->hasErrors() === false ){
            echo "true";
        }else{
            echo "false";
        }
        die();
    }else{
        $validator->validate();
        if( $validator->hasErrors() === false ){
            echo "ok";
        }
    }
    
}

?>
<html>
    <head>
        <script type="text/javascript" src="../../vendors/jquery-validation/lib/jquery-1.6.1.js"></script>
        <script type="text/javascript" src="../../vendors/jquery-validation/lib/jquery.form.js"></script>
        <script type="text/javascript" src="../../vendors/jquery-validation/jquery.validate.js"></script>
    </head>
    <body>
        <form name="f_testform" method="POST" action="">
            <label for="testfield">Input field</label>
            <input type="text" name="testfield" id="testfield" value="" />
            <br/>
            <label for="testfield">Input field 2</label>
            <input type="text" name="testfield2" id="testfield" value="" />
            <br/>
            <input type="submit" value="Submit !" />
        </form>
        <?php echo $validator->__toHTML(); ?>
    </body>
</html>