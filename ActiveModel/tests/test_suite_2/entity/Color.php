<?php
class Color extends ActiveModel{
    
    /**
     * 
     * @Column(autoincrement="true",type="int",size="11")
     * @Index(type="pk")
     *
     * @var int
     */
    public $id;
    
    /**
     * 
     * @Column(type="text",size="150",encoding="utf8_unicode_ci")
     *
     * @var string
     */
    public $name;
    
    /**
     * @HasMany(target="Product.colors")
     * 
     * @var string
     */
    public $products;
    
    /**
     * @Column(type="int",size="11",encoding="utf8_unicode_ci",default_value="0", shared_with='Product')
     * 
     * @var int
     */
    public $color_position;
}