<?php
/** BoltUtil's single-gateway order-pay view. Native WooCommerce authorisation runs before this template. */
defined( 'ABSPATH' ) || exit;
$boltutil_wc_gateway = $available_gateways['boltutil_usdt'];
$boltutil_wc_routes = $boltutil_wc_gateway->available_routes();
?>
<div class="boltutil-invoice-page">
    <form id="order_review" method="post">
        <input type="hidden" name="payment_method" value="boltutil_usdt" />
        <?php do_action( 'woocommerce_pay_order_before_payment' ); ?>
        <?php
        ob_start();
        echo '<div class="boltutil-native-submit">';
        echo '<input type="hidden" name="woocommerce_pay" value="1" />';
        wc_get_template( 'checkout/terms.php' );
        do_action( 'woocommerce_pay_order_before_submit' );
        $boltutil_wc_button = BoltUtil_WC_Checkout::copy()['continue'];
        echo apply_filters( 'woocommerce_pay_order_button_html', '<button type="submit" class="button alt primary" id="place_order" value="' . esc_attr( $boltutil_wc_button ) . '" data-value="' . esc_attr( $boltutil_wc_button ) . '">' . esc_html( $boltutil_wc_button ) . ' <span aria-hidden="true">→</span></button>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        do_action( 'woocommerce_pay_order_after_submit' );
        wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' );
        echo '</div>';
        $boltutil_wc_footer = ob_get_clean();
        BoltUtil_WC_Checkout::render( $boltutil_wc_gateway, $boltutil_wc_routes, BoltUtil_WC_Checkout::context( $order ), $boltutil_wc_footer );
        ?>
    </form>
    <details class="boltutil-order-details"><summary><?php echo esc_html( boltutil_wc_text( '订单详情', __( 'Order details', 'boltutil-payments-for-woocommerce' ) ) ); ?></summary>
        <table class="shop_table"><tbody><?php foreach ( $order->get_items() as $boltutil_wc_item_id => $boltutil_wc_item ) : ?>
            <?php if ( ! apply_filters( 'woocommerce_order_item_visible', true, $boltutil_wc_item ) ) continue; ?>
            <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $boltutil_wc_item, $order ) ); ?>"><th><?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $boltutil_wc_item->get_name(), $boltutil_wc_item, false ) ); ?> <?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_quantity_html', '× ' . esc_html( $boltutil_wc_item->get_quantity() ), $boltutil_wc_item ) ); ?><?php do_action( 'woocommerce_order_item_meta_start', $boltutil_wc_item_id, $boltutil_wc_item, $order, false ); wc_display_item_meta( $boltutil_wc_item ); do_action( 'woocommerce_order_item_meta_end', $boltutil_wc_item_id, $boltutil_wc_item, $order, false ); ?></th><td><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $boltutil_wc_item ) ); ?></td></tr>
        <?php endforeach; ?><?php foreach ( (array) $order->get_order_item_totals() as $boltutil_wc_total ) : ?><tr><th><?php echo wp_kses_post( $boltutil_wc_total['label'] ); ?></th><td><?php echo wp_kses_post( $boltutil_wc_total['value'] ); ?></td></tr><?php endforeach; ?></tbody></table>
    </details>
</div>
