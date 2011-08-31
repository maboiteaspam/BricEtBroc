<?php
namespace BricEtBroc\Form;

use BricEtBroc\Form\InputValueAccessor as InputValueAccessor;

class InputValues{
    public $data_sources;
    const DFT_SRCE = "from_default";
    
    /**
     *
     * @param array $values
     * @param string $data_source_name 
     * @param bool $isTextual 
     */
    public function __construct( ){
        $this->data_sources = array();
    }
    
    /**
     *
     * @param array $values
     * @param string $data_source_name 
     * @param bool $isTextual 
     */
    public function setDataSource( array $values, $data_source_name=InputValues::DFT_SRCE, $isTextual=true ){
        $this->data_sources[$data_source_name] = array(
            "content"=>$values,
            "is_textual"=>$isTextual,
        );
    }
    
    /**
     *
     * @param string $date_source_name
     * @return mixed 
     */
    public function getDataSource( $date_source_name=InputValues::DFT_SRCE ){
        return $this->data_sources[$date_source_name]["content"];
    }
    
    /**
     *
     * @param string $date_source_name
     * @return mixed 
     */
    public function isTextual( $date_source_name=InputValues::DFT_SRCE ){
        return $this->data_sources[$date_source_name]["is_textual"];
    }
    
    /**
     *
     * @param string $target
     * @return InputValueAccessor 
     */
    public function getAccessor( $target ){
        $retour = new InputValueAccessor( $target );
        $retour->setInputValues($this);
        return $retour;
    }
    
    
    public function compact( ){
        $retour = array();
        foreach( $this->data_sources as $source_name => $source_infos ){
            $retour[$source_name] = $source_infos["content"];
        }
        return $retour;
    }
}