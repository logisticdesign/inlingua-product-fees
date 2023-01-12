<?php
/**
 * WooCommerce Product Fees
 *
 * Creates global product settings, coupon options, and adds csv import support.
 *
 * @class 	WCPF_Admin_Global_Settings
 * @author 	Caleb Burks
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCPF_Admin_Global_Settings {

    public function __construct() {
        add_action('woocommerce_get_sections_general', [$this, 'add_general_section'], 10);
        add_action('woocommerce_get_settings_general', [$this, 'general_settings_output'], 10, 2);

        add_action('woocommerce_get_sections_products', [$this, 'add_product_section'], 10);
        add_action('woocommerce_get_settings_products', [$this, 'product_settings_output'], 10, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Fees Settings
    |--------------------------------------------------------------------------
    */

    public function add_general_section($sections) {
        $sections['fees'] = __('General Fees', 'woocommerce-general-fees');
        return $sections;
    }

    public function general_settings_output($settings, $current_section) {
        if ('fees' == $current_section) {
            $settings = $this->settings_fields();
        }
        return $settings;
    }

    public function add_product_section( $sections ) {
        $sections['fees'] = __('Product Fees', 'woocommerce-product-fees');
        return $sections;
    }

    public function product_settings_output( $settings, $current_section ) {
        if ( 'fees' == $current_section ) {
            $settings = $this->settings_fields();
        }
        return $settings;
    }

    public function settings_fields() {
        $settings = apply_filters('wcpf_global_product_settings', [
            [
                'title' => __( 'Product Fees', 'woocommerce-product-fees'),
                'type' 	=> 'title',
                'desc' 	=> '',
                'id' 	=> 'product_fees_options',
            ],
            [
                'title'    => __('Fee Tax Class:', 'woocommerce-product-fees'),
                'desc'     => __('Optionally control which tax class gets applied to fees, or leave it so no taxes are applied.', 'woocommerce-product-fees'),
                'id'       => 'wcpf_fee_tax_class',
                'css'      => 'min-width:150px;',
                'default'  => 'title',
                'type'     => 'select',
                'class'    => 'wc-enhanced-select',
                'options'  => $this->tax_classes(),
                'desc_tip' =>  true,
            ],
            [
                'title'   => __('Fee Name Conflicts', 'woocommerce-product-fees'),
                'desc'    => __('If option #2 is chosen, whichever product comes first in the cart will take precedence. ', 'woocommerce-product-fees'),
                'id'      => 'wcpf_name_conflicts',
                'default' => 'combine',
                'type'    => 'radio',
                'options' => [
                    'combine'      => __( '1) Combine fees with the same name. (recommended)', 'woocommerce-product-fees' ),
                    'dont_combine' => __( '2) Only add one fee if the names are conflicting.', 'woocommerce-product-fees' ),
                ],
                'desc_tip'        =>  true,
            ],
            [
                'type' => 'sectionend', 'id' => 'product_fees_options'
            ],
        ]);

        return $settings;
    }

    public function tax_classes() {
        $tax_classes     = WC_Tax::get_tax_classes();
        $classes_options = array();

        $classes_options['_no_tax'] = __('No taxes for fees', 'woocommerce-product-fees');

        // Add support for product-level tax settings.
        $classes_options['inherit_product_tax'] = __('Fee tax class based on the fee\'s product', 'woocommerce-product-fees');

        // Manually add the standard tax as it's not returned by WC_Tax::get_tax_classes().
        // Thanks @daigo75
        $classes_options[''] = __('Standard', 'woocommerce');

        if ( ! empty($tax_classes)) {
            foreach ($tax_classes as $class) {
                $classes_options[sanitize_title($class)] = esc_html($class);
            }
        }

        return $classes_options;
    }
}

return new WCPF_Admin_Global_Settings();
