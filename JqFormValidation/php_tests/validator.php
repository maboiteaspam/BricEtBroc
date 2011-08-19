<?php

use BricEtBroc\Form\FormValidator as FormValidator;
use BricEtBroc\Form\InputValues as InputValues;
use BricEtBroc\Form\InputValueAccessor as InputValueAccessor;
use BricEtBroc\Form\Dependency as Dependency;

$input_values = new InputValues( 
        array("nom"=>"") 
        );
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
$validator->setInputValues($input_values);
assert_false("'nom' is a required argument", $validator->validate());

/*******************************************************************************
 * 
 */
$options = array('rules' => array(
                    'nom' => 'required',
                    )
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setInputValues($input_values);
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
$validator->setInputValues($input_values);
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
$validator->setInputValues($input_values);
assert_true("'nom' is a required argument, if 'text_box' is not checked", $validator->validate());


/*******************************************************************************
 * 
 */
$input_values = new InputValues( 
        array("nom"=>"", "text_box2"=>true)
        );
$options = array('rules' => array(
                    'nom' => array(
                        'required' => 'text_box2:unchecked',
                        )
                    )
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setInputValues($input_values);
assert_false("'nom' is a required argument, if 'text_box' is not checked", $validator->validate());

/*******************************************************************************
 * 
 */
$input_values = new InputValues( 
        array("nom"=>"")
        );
$options = array('rules' => array(
                    'nom' => array(
                        "ajax"=>function(InputValueAccessor $valueAccessor){
                            return $valueAccessor->is_set();
                        }
                    ),
                    )
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setInputValues($input_values);
assert_true("'nom' is a required argument, ajaxified", $validator->validate());

/*******************************************************************************
 * 
 */
$input_values = new InputValues( 
        array("prenom"=>"")
        );
$options = array('rules' => array(
                    'nom' => array(
                        "ajax"=>function(InputValueAccessor $valueAccessor){
                                return $valueAccessor->is_set();
                            }
                        ),
                    )
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setInputValues($input_values);
assert_false("'nom' is a required argument, ajaxified", $validator->validate());

/*******************************************************************************
 * 
 */
$input_values = new InputValues( 
        array("prenom"=>"")
        );
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
$validator->setInputValues($input_values);
$validator->validate();
assert_true("'nom' is a required argument, ajaxified", $validator->hasErrors());
$messages = $validator->getErrors();
assert_true("'nom' is a required argument, ajaxified", count($messages)===1);
$message = $messages[0];
assert_true("'nom' is a required argument, ajaxified", $message->message==='Bad value %value% typed in.');

/*******************************************************************************
 * 
 */
$input_values = new InputValues( 
        array("prenom"=>"")
        );
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
$validator->setInputValues($input_values);
$validator->validate();
assert_true("'nom' is a required argument, ajaxified", $validator->hasErrors());
$messages = $validator->getErrors();
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
    public function is_confirmed( InputValueAccessor $accessor ){
        return false===true;
    }
    public function __toJavascript(){
$str = <<<'EOD'
function(){return false == true;}
EOD;
        return "'".$this->accessor->data_source_target."'";
    }
}
$input_values = new InputValues( 
        array("prenom"=>"")
        );
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
$validator->setInputValues($input_values);
$validator->validate();
assert_true("'nom' is a required argument, complex process rule", $validator->hasErrors());
$messages = $validator->getErrors();
assert_true("'nom' is a required argument, complex process rule", count($messages)===1);
$message = $messages[0];
assert_true("'nom' is a required argument, complex process rule", $message->message==='Bad value %value% typed in.');

/*******************************************************************************
 * 
 */
$input_values = new InputValues( 
        array("prenom"=>"")
        );
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
$validator->setInputValues($input_values);
$validator->validate();
assert_true("'nom' is a required argument, ajaxified", $validator->hasErrors());
$messages = $validator->getErrors();
assert_true("'nom' is a required argument, ajaxified", count($messages)===1);
$message = $messages[0];
assert_true("'nom' is a required argument, ajaxified", $message->message==='Bad value %value% typed in.');

/*******************************************************************************
 * @todo finir ce test
 */
$input_values = new InputValues( 
        array("nom"=>array("","--"))
        );
$options = array('rules' => array(
                    'nom[]' => array(
                        "required"=>true
                        ),
                    ),
                 'messages'=>array(
                     'nom'=>'Bad value %value% typed in.'
                 ),
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setInputValues($input_values);
$validator->validate();
assert_true("'nom' is a required argument, ajaxified", $validator->hasErrors());
$messages = $validator->getErrors();
assert_true("'nom' is a required argument, ajaxified", count($messages)===1);
if( count($messages) > 0 ){
    $message = $messages[0];
    assert_true("'nom' is a required argument, ajaxified", $message->message==='Bad value %value% typed in.');
}
/*******************************************************************************
 * @todo finir ce test
 */
$input_values = new InputValues( 
        array("nom"=>array("","--"))
        );
$options = array('rules' => array(
                    'nom\[\]' => array(
                        "required"=>true
                        ),
                    ),
                 'messages'=>array(
                     'nom'=>'Bad value %value% typed in.'
                 ),
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setInputValues($input_values);
$validator->validate();
assert_true("'nom' is a required argument, ajaxified", $validator->hasErrors());
$messages = $validator->getErrors();
assert_true("'nom' is a required argument, ajaxified", count($messages)===1);
if( count($messages) > 0 ){
    $message = $messages[0];
    assert_true("'nom' is a required argument, ajaxified", $message->message==='Bad value %value% typed in.');
}
/*******************************************************************************
 * @todo finir ce test
 */
$input_values = new InputValues( 
        array("nom"=>array("test"=>"--"))
        );
$options = array('rules' => array(
                    'nom\[test\]' => array(
                        "required"=>true
                        ),
                    ),
                 'messages'=>array(
                     'nom'=>'Bad value %value% typed in.'
                 ),
                );
$validator = new FormValidator("f_test_form", $options);
$validator->setInputValues($input_values);
$validator->validate();
assert_true("'nom' is a required argument, ajaxified", $validator->hasErrors());
$messages = $validator->getErrors();
assert_true("'nom' is a required argument, ajaxified", count($messages)===1);
if( count($messages) > 0 ){
    $message = $messages[0];
    assert_true("'nom' is a required argument, ajaxified", $message->message==='Bad value %value% typed in.');
}




