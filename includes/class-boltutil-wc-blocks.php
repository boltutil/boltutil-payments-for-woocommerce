<?php
defined( 'ABSPATH' ) || exit;

final class BoltUtil_WC_Blocks extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
    protected $name = 'boltutil_usdt';
    private $gateway;

    public function initialize() {
        $this->gateway = new BoltUtil_WC_Gateway();
    }

    public function is_active() {
        return $this->gateway && $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {
        // Blocks can also be embedded on a page other than the configured checkout.
        wp_enqueue_style( 'boltutil-wc-checkout-base', plugins_url( 'assets/checkout-base.css', BOLTUTIL_WC_FILE ), array(), BoltUtil_WC_Gateway::asset_version( 'assets/checkout-base.css' ) );
        wp_enqueue_style( 'boltutil-wc-checkout', plugins_url( 'assets/checkout.css', BOLTUTIL_WC_FILE ), array( 'boltutil-wc-checkout-base' ), BoltUtil_WC_Gateway::asset_version( 'assets/checkout.css' ) );
        wp_register_script( 'boltutil-wc-routes', plugins_url( 'assets/routes.js', BOLTUTIL_WC_FILE ), array(), BoltUtil_WC_Gateway::asset_version( 'assets/routes.js' ), true );
        wp_register_script(
            'boltutil-wc-blocks',
            plugins_url( 'assets/blocks.js', BOLTUTIL_WC_FILE ),
            array( 'wp-element', 'wp-html-entities', 'wc-blocks-registry', 'wc-settings', 'boltutil-wc-routes' ),
            BoltUtil_WC_Gateway::asset_version( 'assets/blocks.js' ),
            true
        );
        return array( 'boltutil-wc-blocks' );
    }

    public function get_payment_method_data() {
        $routes = $this->gateway->available_routes();
        $networks = array();
        foreach ( $routes as $route_id => $route ) {
            $code = $route['network'];
            $labels = BoltUtil_WC_Gateway::network_labels( $code );
            $networks[ $route_id ] = array(
                'label' => $labels[0],
                'short' => BoltUtil_WC_Gateway::token_label( $route['token'], $code ) . ' · ' . $labels[1],
                'token' => $route['token'],
                'network' => $code,
                'networkId' => $route['networkId'],
                'tokenIcon' => BoltUtil_WC_Gateway::token_icon_url( $route['token'] ),
                'icon' => BoltUtil_WC_Gateway::network_icon_url( $code ),
            );
        }
        return array(
            'copy' => BoltUtil_WC_Checkout::copy(),
            'title'       => $this->gateway->checkout_title(),
            'description' => BoltUtil_WC_Checkout::merchant_description( $this->gateway ),
            'networks'    => $networks,
            'icon'        => plugins_url( 'assets/boltutil-official-mark.svg', BOLTUTIL_WC_FILE ),
            'tokenIcon'   => BoltUtil_WC_Gateway::token_icon_url(),
            'tokenLabel' => boltutil_wc_text( '选择支付币种', __( 'Choose a stablecoin', 'boltutil-payments-for-woocommerce' ) ),
            'tokenIcons' => array( 'USDT' => BoltUtil_WC_Gateway::token_icon_url( 'USDT' ), 'USDC' => BoltUtil_WC_Gateway::token_icon_url( 'USDC' ) ),
            'networkLabel' => boltutil_wc_text( '选择支付网络', __( 'Choose a payment network', 'boltutil-payments-for-woocommerce' ) ),
            'selectedLabel' => boltutil_wc_text( '已选择', __( 'Selected', 'boltutil-payments-for-woocommerce' ) ),
            'redirectNotice' => boltutil_wc_text( '请在收银台使用指定网络，支付页面显示的准确金额。付款核验通过后自动入账；打开或返回本页不会将订单标为已付。', __( 'Pay the exact amount on the hosted checkout, using its selected network. Confirmation is automatic after verification. Opening or returning to this page does not mark an order paid.', 'boltutil-payments-for-woocommerce' ) ),
            'networkError' => boltutil_wc_text( '请选择可用的币种和网络。', __( 'Choose an available stablecoin and network.', 'boltutil-payments-for-woocommerce' ) ),
            'supports'    => array( 'products' ),
        );
    }
}
