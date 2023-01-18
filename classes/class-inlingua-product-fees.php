<?php
/**
 * WooCommerce Product Fees
 *
 * Add the fees at checkout.
 *
 * @class 	WooCommerce_Product_Fees
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InliguaProductFees {

    /**
     * Constructor for the main product fees class.
     */
    public function __construct() {
        if (is_admin()) {
            // Product & global settings
            require_once 'admin/class-wcpf-admin-product-settings.php';
            require_once 'admin/class-wcpf-admin-global-settings.php';
        }

        // Text domain
        add_action('plugins_loaded', [$this, 'text_domain']);

        // Hook-in for fees to be added
        add_action('woocommerce_cart_calculate_fees', [$this, 'check_product_fees'], 15);
        add_action('woocommerce_cart_calculate_fees', [$this, 'add_tax_mark'], 90);
    }

    /**
     * Load Text Domain
     */
    public function text_domain() {
        load_plugin_textdomain('inlingua-product-fees', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Add tax mark fee.
     *
     * @param object $cart WC Cart object.
     * @return null
     */
    public function check_product_fees($cart) {
        foreach ($cart->cart_contents as $item) {
            $custom_data = $item['custom_data'];
            $multiply_for_quantity = get_post_meta($item['product_id'], 'product-fee-multiplier', true) === 'yes';

            foreach($custom_data as $name => $data) {
                $slug = $data['term']->slug;
                $fee_name = "product-fee-$name-$slug-amount";

                $fee_value = str_replace(wc_get_price_decimal_separator(), '.', get_post_meta($item['product_id'], $fee_name, true)) * 1;

                if ($multiply_for_quantity) {
                    $fee_value = $fee_value * $item['quantity'];
                };

                $cart_fee_name = <<< HTML
                    <div class="cart-fee-name">
                        <span class="cart-fee-name-title">{$item['data']->get_name()}:</span>
                        <span class="cart-fee-name-description">{$data['label']} - {$data['term']->name}</span>
                    </div>
                HTML;

                $cart->add_fee($cart_fee_name, $fee_value, false);
            }
        }
    }

    /**
     * Add tax mark fee.
     *
     * @param object $cart WC Cart object.
     * @return null
     */
    public function add_tax_mark($cart) {
        if (! function_exists('get_field')) return;

        $globals = get_field('marca_da_bollo', 'options');
        $limit = $globals['soglia'];
        $value = $globals['valore'];

        if ($cart->subtotal > $limit) {
            $cart->add_fee(__('Marca da bollo', 'inlingua'), $value, false);
        }
    }

    /**
     * Convert a fee amount from percentage to the actual cost.
     *
     * @param string $fee_amount Fee amount.
     * @param int $item_price Item price.
     * @return int $fee_amount The actual cost of the fee.
     */
    public function make_percentage_adjustments($fee_amount, $item_price) {
        // Replace with a standard decimal separator for calculations.
        $fee_amount = str_replace(wc_get_price_decimal_separator(), '.', $fee_amount);

        if (strpos($fee_amount, '%')) {
            // Convert to decimal, then multiply by the cart item's price.
            $fee_amount = (str_replace( '%', '', $fee_amount) / 100) * $item_price;
        }

        return $fee_amount;
    }

    /**
     * Multiply the fee by the cart item quantity if needed.
     *
     * @param int $amount Fee amount.
     * @param string $multiplier Whether the item should be multiplied by qty or not.
     * @param int $qty Cart item quantity.
     * @return int $amount The actual cost of the fee.
     */
    public function maybe_multiply_by_quantity($amount, $multiplier, $qty) {
        // Multiply the fee by the quantity if needed.
        if ( 'yes' === $multiplier ) {
            $amount = $qty * $amount;
        }

        return $amount;
    }

    /**
     * Get the fee's tax class.
     *
     * @param object $product WC Cart item object.
     * @return string $fee_tax_class Which tax class to use for the fee.
     */
    public function get_fee_tax_class($tax_status, $tax_class) {
        $fee_tax_class = get_option('wcpf_fee_tax_class', '_no_tax');

        if ( ! wc_tax_enabled()) return '_no_tax';

        // Change fee tax settings to the product's tax settings.
        if ('inherit_product_tax' === $fee_tax_class) {
            if ('taxable' === $tax_status) {
                $fee_tax_class = $tax_class;
            } else {
                $fee_tax_class = '_no_tax';
            }
        }

        return $fee_tax_class;
    }
}
