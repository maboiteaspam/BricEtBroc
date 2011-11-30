<?php
class Product extends ActiveModel{
    
    /**
     * @Column(autoincrement="true",type="int",size="11")
     * @Index(type="pk")
     *
     * @var int
     */
    public $id;
    
    /**
     * @Column(type="text",size="150",encoding="utf8_unicode_ci",nullable="true",default_value="null")
     *
     * @var string
     */
    public $name;
    
    /**
     * @Column(type="int",size="11",encoding="utf8_unicode_ci")
     *
     * @var int
     */
    public $position;
    
    /**
     * @HasMany(target="Color.products", with = { 'color_position' } )
     * 
     * @var array
     */
    public $colors;
    
    /**
     * @HasOne(target="Catalog")
     * 
     * @var Catalog
     */
    public $catalog;
    
    /**
     * @HasOne(target="Catalog")
     * 
     * @var Catalog
     */
    public $tomate;
}