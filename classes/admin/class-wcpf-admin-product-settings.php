<?php
/**
 * WooCommerce Product Fees
 *
 * Create the product and variation settings.
 *
 * @class 	WCPF_Admin_Product_Settings
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCPF_Admin_Product_Settings
{
    protected $attributesNotVariation;

    public function __construct() {
        // Add and save product settings.
        add_action('woocommerce_product_write_panel_tabs', [$this, 'create_product_panel_tab']);
        add_action('woocommerce_product_data_panels', [$this, 'product_settings_fields']);
        add_action('woocommerce_process_product_meta', [$this, 'save_product_settings_fields']);

        // Add and save variation settings.
        add_action( 'woocommerce_product_after_variable_attributes', [$this, 'variation_settings_fields'], 10, 3);
        add_action( 'woocommerce_save_product_variation', [$this, 'save_variation_settings_fields'], 10, 2);

        // CSS
        add_action( 'admin_head', [$this, 'admin_css']);
    }

    public function create_product_panel_tab() {
        echo '<li class="fees_product_tab product_fee_options"><a href="#fees_product_data"><span>' . __('Product Fees', 'woocommerce-product-fees').'</span></a></li>';
    }

    public function product_settings_fields() {
        echo '<div id="fees_product_data" class="fee_panel panel woocommerce_options_panel wc-metaboxes-wrapper">';

        $this->attributesNotVariation = get_product_attributes_not_for_variation($GLOBALS['post_id']);

        foreach ($this->attributesNotVariation as $attribute) {
            $attribute_data = $attribute->get_data();

            echo '
                <div>
                <div class="toolbar toolbar-variations-defaults"><h4>' . wc_attribute_label($attribute_data['name']) . '</h4></div>
            ';

            foreach ($attribute_data['options'] as $option) {
                echo '<div class="options_group">';

                $term = get_term($option, $attribute_data['name']);

                // Text Field - Fee Amount
                woocommerce_wp_text_input([
                    'id' => "product-fee-{$attribute_data['name']}-{$term->slug}-amount",
                    'label' => sprintf(__("{$term->name} Amount (%s)", 'woocommerce-product-fees'), get_woocommerce_currency_symbol()),
                    'data_type' => 'price',
                    'placeholder' => __('Monetary Decimal or Percentage', 'woocommerce-product-fees'),
                    'desc_tip' => 'true',
                    'description' => __( 'Enter a monetary decimal without any currency symbols or thousand seperators. This field also accepts percentages.', 'woocommerce-product-fees')
                ]);

                echo '</div>';
            }

            echo '</div>';
        }

        echo '<div class="options_group">';

        do_action('wcpf_add_product_settings_group_one');

        echo '
            </div>
            <div class="options_group">
        ';

        // Check Box - Fee Multiply Option
        woocommerce_wp_checkbox(['id'=> 'product-fee-multiplier', 'label' => __('Multiply Fee by Quantity', 'woocommerce-product-fees'), 'desc_tip' => 'true', 'description' => __('Multiply the fee by the quanitity of this product that is added to the cart.', 'woocommerce-product-fees')]);

        do_action('wcpf_add_products_settings_group_two');

        echo '
            </div>
            </div>
        ';
    }

    public function save_product_settings_fields($post_id) {
        $this->attributesNotVariation = get_product_attributes_not_for_variation($post_id);

        foreach ($this->attributesNotVariation as $attribute) {
            $attribute_data = $attribute->get_data();

            foreach ($attribute_data['options'] as $option) {
                $term = get_term($option, $attribute_data['name']);

                foreach ([
                    "product-fee-{$attribute_data['name']}-{$term->slug}-amount"
                ] as $field) {
                    $field_value = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : ''; // phpcs:ignore CSRF

                    if ($field_value !== get_post_meta($post_id, $field, true)) {
                        if ('' === $field_value) {
                            delete_post_meta($post_id, $field);
                        } else {
                            update_post_meta($post_id, $field, $field_value);
                        }
                    }
                }
            }
        }

        $product_fee_multiplier_checkbox = isset($_POST['product-fee-multiplier']) ? 'yes' : ''; // phpcs:ignore CSRF

        if ($product_fee_multiplier_checkbox !== get_post_meta($post_id, 'product-fee-multiplier', true)) {
            if ( '' === $product_fee_multiplier_checkbox ) {
                delete_post_meta($post_id, 'product-fee-multiplier');
            } else {
                update_post_meta($post_id, 'product-fee-multiplier', $product_fee_multiplier_checkbox);
            }
        }
    }

    public function admin_css() {
        echo "
            <style type='text/css'>
                #woocommerce-product-data ul.product_data_tabs li.product_fee_options a:before {
                    content: '\\e01e';
                    font-family: 'WooCommerce';
                }
                .product-fee-multiplier .checkbox {
                    margin: 3px 6px 0 0 !important;
                }
            </style>
        ";
    }

}

return new WCPF_Admin_Product_Settings();
