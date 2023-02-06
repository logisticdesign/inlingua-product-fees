<?php

function get_product_attributes_not_for_variation($post_id) {
    if ( ! $post_id) return false;

    $product = wc_get_product($post_id);

    if ( ! $product) return false;

    $attributes = $product->get_attributes();

    if (count($attributes)) {
        return array_filter($attributes, function($attribute) {
            $attribute_data = $attribute->get_data();
            return ! $attribute_data['variation'];
        });
    }

    return [];
}
