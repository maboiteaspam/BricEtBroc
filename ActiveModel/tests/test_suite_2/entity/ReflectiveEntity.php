<?php
/**
 */
class ReflectiveEntity extends ActiveModel{

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
     * @HasOne(target="ReflectiveEntity")
     *
     * @var ReflectiveEntity
     */
    public $parent_reflective;

    /**
     *
     * @HasMany(target="ReflectiveEntity.parent_reflective")
     *
     * @var ReflectiveEntity
     */
    public $children_reflective;
}



