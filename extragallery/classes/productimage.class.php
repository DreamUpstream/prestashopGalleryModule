<?php

class ProductImage extends ObjectModel {
    public $id;
    public $product_id;
    public $image;

    public static $definition = [
        'table' => 'extra_gallery',
        'primary' => 'id',
        'multilang' => false,
        'fields' => [
            'product_id' => [
                'type' => self::TYPE_INT,
                'size' => 11,
                'validate' => 'isunsignedInt',
                'required' => true
            ],
            'image' => [
                'type' => self::TYPE_STRING,
                'size' => 255,
                'validate' => 'isCleanHtml',
                'required' => true
            ]    
        ]
    ];
}