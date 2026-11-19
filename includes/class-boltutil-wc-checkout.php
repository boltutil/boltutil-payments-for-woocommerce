<?php
defined( 'ABSPATH' ) || exit;

/** Presentation only. WooCommerce remains responsible for permission checks and payment. */
final class BoltUtil_WC_Checkout {
    private static $authorised_order;

    public static function copy() {
        return array(
            'intro' => boltutil_wc_text( '使用稳定币支付账单', __( 'Stablecoin invoice payments', 'boltutil-payments-for-woocommerce' ) ),
            'title' => boltutil_wc_text( '支付账单', __( 'Pay your invoice', 'boltutil-payments-for-woocommerce' ) ),
            'balance' => boltutil_wc_text( '待付金额', __( 'Amount to pay', 'boltutil-payments-for-woocommerce' ) ),
            'order' => boltutil_wc_text( '订单', __( 'Order', 'boltutil-payments-for-woocommerce' ) ),
            'choose' => boltutil_wc_text( '选择币种和网络', __( 'Choose an asset and network', 'boltutil-payments-for-woocommerce' ) ),
            'continue' => boltutil_wc_text( '前往 BoltUtil 收银台', __( 'Continue to BoltUtil', 'boltutil-payments-for-woocommerce' ) ),
            'back' => boltutil_wc_text( '返回商店', __( 'Back to store', 'boltutil-payments-for-woocommerce' ) ),
        );
    }

    public static function merchant_description( $gateway ) {
        $saved = $gateway->get_option( 'description', '' );
        if ( in_array( $saved, array( '', 'Pay USDT on the network you choose.', '选择网络后，前往 BoltUtil 收银台支付 USDT。', 'Choose a stablecoin and network, then pay at BoltUtil Checkout.', '选择币种和网络后，前往 BoltUtil 收银台付款。' ), true ) ) return '';
        return $gateway->checkout_description();
    }

    public static function amount( $value ) {
        if ( ! is_scalar( $value ) || ! preg_match( '/^([0-9]{1,13})(?:\.([0-9]{1,6}))?$/D', (string) $value, $parts ) ) return '';
        $whole = ltrim( $parts[1], '0' );
        $fraction = str_pad( rtrim( $parts[2] ?? '', '0' ), 2, '0' );
        return ( '' === $whole ? '0' : $whole ) . '.' . $fraction;
    }

    public static function context( $order = null ) {
        if ( null === $order ) $order = self::$authorised_order;
        if ( $order instanceof WC_Order ) {
            return array( 'amount' => self::amount( (string) $order->get_total( 'edit' ) ), 'currency' => $order->get_currency(), 'reference' => (string) $order->get_order_number(), 'invoice' => true );
        }
        $cart = function_exists( 'WC' ) && WC() ? WC()->cart : null;
        return array( 'amount' => $cart ? self::amount( (string) $cart->get_total( 'edit' ) ) : '', 'currency' => get_woocommerce_currency(), 'reference' => '', 'invoice' => false );
    }

    /** Called by WooCommerce only after it has authorised the order-pay context. */
    public static function pay_template( $template, $name, $args, $template_path = '', $default_path = '' ) {
        if ( 'checkout/form-pay.php' === $name ) {
            // Also retain the native invoice amount when another gateway keeps the original template.
            self::$authorised_order = isset( $args['order'] ) && $args['order'] instanceof WC_Order && $args['order']->needs_payment() ? $args['order'] : null;
        }
        if ( 'checkout/form-pay.php' !== $name || empty( $args['order'] ) || ! $args['order'] instanceof WC_Order ||
            ! $args['order']->needs_payment() || 'USD' !== $args['order']->get_currency() ||
            empty( $args['available_gateways'] ) || 1 !== count( $args['available_gateways'] ) ||
            ! isset( $args['available_gateways']['boltutil_usdt'] ) ||
            ! $args['available_gateways']['boltutil_usdt'] instanceof BoltUtil_WC_Gateway ) {
            return $template;
        }
        return __DIR__ . '/templates/form-pay.php';
    }

    public static function render( $gateway, $routes, $context, $footer = '' ) {
        $copy = self::copy();
        include __DIR__ . '/templates/payment-card.php';
    }
}
