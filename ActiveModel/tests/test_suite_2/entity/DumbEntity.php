<?php
/**
 */
class DumbEntity extends ActiveModel{
    
    /**
     * @Column(autoincrement="true",type="int",size="11")
     * @Index(type="pk", name="my_pk")
     *
     * @var int
     */
    public $id;
    
    /**
     * @Column(type="text",size="150")
     * @Index(type="index", name="my_index")
     *
     * @var string
     */
    public $name;
    
    /**
     *
     * @HasOne(target="Catalog")
     * 
     * @var Catalog
     */
    public $dumb_catalog;
}



