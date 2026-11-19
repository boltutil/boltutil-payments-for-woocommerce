<?php
defined( 'ABSPATH' ) || exit;
// $gateway, $routes, $context, $copy and optional trusted native form footer are supplied by the renderer.
?>
<div class="boltutil-payment-panel boltutil-classic-panel boltutil-checkout-standard">
    <section class="checkout-card">
        <header class="checkout-header">
            <div class="brand" aria-label="BoltUtil"><span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 10V3L4 14h7v7l9-11h-7z" /></svg></span><span>BoltUtil</span></div>
            <p class="checkout-promo"><?php echo esc_html( $copy['intro'] ); ?></p>
            <h2 class="checkout-title"><?php echo esc_html( ! empty( $context['invoice'] ) ? $copy['title'] : $gateway->checkout_title() ); ?></h2>
        </header>
        <div class="checkout-body">
            <?php if ( '' !== $context['amount'] ) : ?>
                <div class="checkout-amount"><p><?php echo esc_html( $copy['balance'] ); ?></p><div class="checkout-amount-value" dir="ltr"><strong><?php echo esc_html( $context['amount'] ); ?></strong><span><?php echo esc_html( $context['currency'] ); ?></span></div><?php if ( '' !== $context['reference'] ) : ?><p class="invoice-reference"><?php echo esc_html( $copy['order'] ); ?> #<?php echo esc_html( $context['reference'] ); ?></p><?php endif; ?></div>
            <?php endif; ?>
            <?php if ( BoltUtil_WC_Checkout::merchant_description( $gateway ) ) : ?><p class="boltutil-merchant-description"><?php echo esc_html( BoltUtil_WC_Checkout::merchant_description( $gateway ) ); ?></p><?php endif; ?>
            <fieldset class="boltutil-token-fieldset" hidden>
                <legend><?php echo esc_html( $copy['choose'] ); ?></legend>
                <div class="asset-switch boltutil-token-list">
                    <?php foreach ( array( 'USDT', 'USDC' ) as $boltutil_wc_token ) : ?>
                        <?php if ( ! in_array( $boltutil_wc_token, array_column( $routes, 'token' ), true ) ) continue; ?>
                        <label class="boltutil-token-option"><input type="radio" name="boltutil_token" value="<?php echo esc_attr( $boltutil_wc_token ); ?>" /><img src="<?php echo esc_url( BoltUtil_WC_Gateway::token_icon_url( $boltutil_wc_token ) ); ?>" alt="" width="20" height="20" aria-hidden="true" /><span><?php echo esc_html( $boltutil_wc_token ); ?></span></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <fieldset class="boltutil-network-fieldset">
                <legend class="boltutil-network-heading"><?php echo esc_html( $copy['choose'] ); ?></legend>
                <div class="routes boltutil-network-list">
                    <?php foreach ( $routes as $boltutil_wc_route_id => $boltutil_wc_route ) : ?>
                        <?php $boltutil_wc_code = $boltutil_wc_route['network']; $boltutil_wc_token = $boltutil_wc_route['token']; $boltutil_wc_labels = BoltUtil_WC_Gateway::network_labels( $boltutil_wc_code ); ?>
                        <label class="route boltutil-network-option"><input required type="radio" name="boltutil_route" data-token="<?php echo esc_attr( $boltutil_wc_token ); ?>" data-network="<?php echo esc_attr( $boltutil_wc_code ); ?>" value="<?php echo esc_attr( $boltutil_wc_route_id ); ?>" />
                            <span class="route-icon" aria-hidden="true"><span class="route-chain route-chain--<?php echo esc_attr( $boltutil_wc_code ); ?>"><img src="<?php echo esc_url( BoltUtil_WC_Gateway::network_icon_url( $boltutil_wc_code ) ); ?>" alt="" width="36" height="36" /></span><span class="route-token"><img src="<?php echo esc_url( BoltUtil_WC_Gateway::token_icon_url( $boltutil_wc_token ) ); ?>" alt="" width="24" height="24" /></span></span>
                            <span class="route-label"><strong><?php echo esc_html( BoltUtil_WC_Gateway::token_label( $boltutil_wc_token, $boltutil_wc_code ) ); ?></strong><small><?php echo esc_html( $boltutil_wc_labels[0] ); ?></small></span><span class="route-check" aria-hidden="true"></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <?php // This HTML comes only from the native order-pay template below (hooks, terms, nonce and button). ?>
            <?php if ( $footer ) echo $footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <p class="help boltutil-payment-note"><?php echo esc_html( boltutil_wc_text( '请在收银台使用指定网络，支付页面显示的准确金额。付款核验通过后自动入账；打开或返回本页不会将订单标为已付。', __( 'Pay the exact amount on the hosted checkout, using its selected network. Confirmation is automatic after verification. Opening or returning to this page does not mark an order paid.', 'boltutil-payments-for-woocommerce' ) ) ); ?></p>
            <?php if ( ! empty( $context['invoice'] ) ) : ?><div class="checkout-return"><a class="back" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">← <?php echo esc_html( $copy['back'] ); ?></a></div><?php endif; ?>
        </div>
    </section>
</div>
