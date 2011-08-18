<?php


include("../libs/__include_files.php");

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
        <script type="text/javascript" src="../vendors/jquery-validation/lib/jquery-1.6.1.js"></script>
        <script type="text/javascript" src="../vendors/jquery-validation/lib/jquery.form.js"></script>
        <script type="text/javascript" src="../vendors/jquery-validation/jquery.validate.js"></script>
    </head>
    <body>
        <form name="f_testform" method="POST" action="">
            <label for="testfield">Input field</label>
            <input type="text" name="testfield" id="testfield" value="" />
            <br/>
            <input type="submit" value="Submit !" />
        </form>
        <script type="text/javascript">
            $("form[name=f_testform]").find("input[name=testfield]").keypress(
                function(event){
                    if( trim( doGetCaretPosition(this), String.fromCharCode(event.keyCode)) == false ){
                        event.stopPropagation();
                        event.preventDefault();
                        return false;
                    }
                    return true;
                }
            );
            function doGetCaretPosition (ctrl) {
                var CaretPos = 0;	// IE Support
                if (document.selection){
                    ctrl.focus ();
                    var Sel = document.selection.createRange ();
                    Sel.moveStart ('character', -ctrl.value.length);
                    CaretPos = Sel.text.length;
                }
                // Firefox support
                else if (ctrl.selectionStart || ctrl.selectionStart == '0')
                CaretPos = ctrl.selectionStart;
                return (CaretPos);
            }
            function trim(caretIndex, newChar ){
                if( caretIndex === 0 && newChar === " " ){
                    return false;
                }
                return true;
            }
        </script>
        <?php echo $validator->__toHTML(); ?>
    </body>
</html>