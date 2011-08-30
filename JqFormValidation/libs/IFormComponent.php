<?php
namespace BricEtBroc\Form;
use BricEtBroc\Form\Form as Form;

/**
 *
 * @author clement
 */
interface IFormComponent {
    public function attachTo( Form $Form );
    public function render( $has_validated, \DOMDocument $response_content );
}

?>
