<?php
function assert_true( $description, $result_operation){
    if( $result_operation === true ){
        
    }else{
        $backtrace = debug_backtrace(1);
        $line = $backtrace[0]["line"];
        echo "TEST FAILED on line ".$line." : ".$description.", result was ".  var_export($result_operation,true)."\n";
    }
}
function assert_false( $description, $result_operation){
    if( $result_operation === false ){
        
    }else{
        $backtrace = debug_backtrace(1);
        $line = $backtrace[0]["line"];
        echo "TEST FAILED on line ".$line." : ".$description.", result was ".  var_export($result_operation,true)."\n";
    }
}

use BricEtBroc\Form\FormValidator as FormValidator;
use BricEtBroc\Form\InputValueAccessor as InputValueAccessor;
use BricEtBroc\Form\Dependency as Dependency;

$in_values = array("nom"=>"");

/*******************************************************************************
 * 
 */
$options = array('rules' => array(
                    'nom' => array(
                        'required' => true,
                        )
                    )
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setDataSource($in_values);
assert_false("'nom' is a required argument", $validator->validate());

/*******************************************************************************
 * 
 */
$options = array('rules' => array(
                    'nom' => 'required',
                    )
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setDataSource($in_values);
assert_false("'nom' is a required argument, inlined", $validator->validate());

/*******************************************************************************
 * 
 */
$options = array('rules' => array(
                    'nom' => array(
                        'required' => true,
                        'minlength' => 2,
                        )
                    )
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setDataSource($in_values);
assert_false("'nom' is a required argument, with minlength>=2", $validator->validate());


/*******************************************************************************
 * 
 */
$options = array('rules' => array(
                    'nom' => array(
                        'required' => 'text_box:unchecked',
                        )
                    )
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setDataSource($in_values);
assert_true("'nom' is a required argument, if 'text_box' is not checked", $validator->validate());


/*******************************************************************************
 * 
 */
$in_values = array("nom"=>"", "text_box2"=>true);
$options = array('rules' => array(
                    'nom' => array(
                        'required' => 'text_box2:unchecked',
                        )
                    )
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setDataSource($in_values);
assert_false("'nom' is a required argument, if 'text_box' is not checked", $validator->validate());

/*******************************************************************************
 * 
 */
$in_values = array("nom"=>"");
$options = array('rules' => array(
                    'nom' => array(
                        "ajax"=>function(InputValueAccessor $valueAccessor){
                            return $valueAccessor->is_set();
                        }
                    ),
                    )
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setDataSource($in_values);
assert_true("'nom' is a required argument, ajaxified", $validator->validate());

/*******************************************************************************
 * 
 */
$in_values = array("prenom"=>"");
$options = array('rules' => array(
                    'nom' => array(
                        "ajax"=>function(InputValueAccessor $valueAccessor){
                                return $valueAccessor->is_set();
                            }
                        ),
                    )
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setDataSource($in_values);
assert_false("'nom' is a required argument, ajaxified", $validator->validate());

/*******************************************************************************
 * 
 */
$in_values = array("prenom"=>"");
$options = array('rules' => array(
                    'nom' => array(
                        "ajax"=>function(InputValueAccessor $valueAccessor){
                                return $valueAccessor->is_set();
                            }
                        ),
                    ),
                 'messages'=>array(
                     'nom'=>array('ajax'=>'Bad value %value% typed in.')
                 ),
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setDataSource($in_values);
$validator->validate();
assert_true("'nom' is a required argument, ajaxified", $validator->hasErrors());
$messages = $validator->getMessages();
assert_true("'nom' is a required argument, ajaxified", count($messages)===1);
$message = $messages[0];
assert_true("'nom' is a required argument, ajaxified", $message->message==='Bad value %value% typed in.');

/*******************************************************************************
 * 
 */
$in_values = array("prenom"=>"");
$options = array('rules' => array(
                    'nom' => array(
                        "ajax"=>function(InputValueAccessor $valueAccessor){
                                return $valueAccessor->is_set();
                            }
                        ),
                    ),
                 'messages'=>array(
                     'nom'=>'Bad value %value% typed in.'
                 ),
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setDataSource($in_values);
$validator->validate();
assert_true("'nom' is a required argument, ajaxified", $validator->hasErrors());
$messages = $validator->getMessages();
assert_true("'nom' is a required argument, ajaxified", count($messages)===1);
$message = $messages[0];
assert_true("'nom' is a required argument, ajaxified", $message->message==='Bad value %value% typed in.');

/*******************************************************************************
 * 
 */

/**
 * An example of personnal and one time use
 * of dependency
 */
class MyOwnDependencyRuleWithComplexProcess extends Dependency{
    
    /**
     *
     * @return bool 
     */
    public function is_confirmed(){
        return false===true;
    }
    public function __toJavascript(){
$str = <<<'EOD'
function(){return false == true;}
EOD;
        return "'".$this->accessor->data_source_target."'";
    }
}
$in_values = array("prenom"=>"");
$options = array('rules' => array(
                    'nom' => array(
                        "required"=>new MyOwnDependencyRuleWithComplexProcess()
                        ),
                    ),
                 'messages'=>array(
                     'nom'=>'Bad value %value% typed in.'
                 ),
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setDataSource($in_values);
$validator->validate();
assert_true("'nom' is a required argument, complex process rule", $validator->hasErrors());
$messages = $validator->getMessages();
assert_true("'nom' is a required argument, complex process rule", count($messages)===1);
$message = $messages[0];
assert_true("'nom' is a required argument, complex process rule", $message->message==='Bad value %value% typed in.');

/*******************************************************************************
 * 
 */
$in_values = array("prenom"=>"");
$options = array('rules' => array(
                    'nom' => array(
                        "somecallback"=>function(InputValueAccessor $valueAccessor){
                                return $valueAccessor->is_set();
                            }
                        ),
                    ),
                 'messages'=>array(
                     'nom'=>'Bad value %value% typed in.'
                 ),
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setDataSource($in_values);
$validator->validate();
assert_true("'nom' is a required argument, ajaxified", $validator->hasErrors());
$messages = $validator->getMessages();
assert_true("'nom' is a required argument, ajaxified", count($messages)===1);
$message = $messages[0];
assert_true("'nom' is a required argument, ajaxified", $message->message==='Bad value %value% typed in.');




