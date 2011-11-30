<?php
/**
 * @Table(name="catalog", encoding="utf8_unicode_ci")
 */
class Catalog extends ActiveModel{
    
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
     * @Column(type="text",nullable="true",default_value="null")
     * @Index(type="index", name="my_index", engine="BTREE", size="15", order="ASC")
     *
     * @var string
     */
    public $description;
    
    /**
     * @Column(type="text",nullable="false")
     * @Index(type="unique", name="my_unique_index", size="25")
     *
     * @var string
     */
    public $code;
    
    /**
     *
     * @OwnMany(target="Product.catalog")
     * 
     * @var Products
     */
    public $products;
    
    /**
     *
     * @HasOne(target="DumbEntity")
     * 
     * @var DumbEntity
     */
    public $dumb_entity;
}



