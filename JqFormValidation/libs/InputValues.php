<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\InputValueAccessor as InputValueAccessor;

class InputValues{
    public $data_sources;
    const DFT_SRCE = "from_default";
    
    public function __construct( array $values, $data_source_name=InputValues::DFT_SRCE ){
        $this->data_sources = array();
        $this->setDataSource( $values, $data_source_name );
    }
    
    public function setDataSource( array $values, $data_source_name=InputValues::DFT_SRCE ){
        $this->data_sources[$data_source_name] = $values;
    }
    
    public function getDataSource( $date_source_name=InputValues::DFT_SRCE ){
        return $this->data_sources[$date_source_name];
    }
    
    public function getAccessor( $target ){
        $retour = new InputValueAccessor( $target );
        $retour->setDataSource($this);
        return $retour;
    }
}