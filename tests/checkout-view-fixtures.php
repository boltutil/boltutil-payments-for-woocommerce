<?php
/** Render the shipped checkout and test native order-pay delegation without WordPress/network. */
define( 'ABSPATH', __DIR__ );
define( 'BOLTUTIL_WC_VERSION', 'fixture' );
define( 'BOLTUTIL_WC_FILE', dirname( __DIR__ ) . '/boltutil-woocommerce.php' );
class WC_Payment_Gateway {
    public $settings = array(), $title = 'Pay with BoltUtil', $description = '';
    public function get_option( $key, $default ) { return $this->settings[ $key ] ?? $default; }
}
class WC_Order {
    public $currency = 'USD', $payable = true;
    public function needs_payment() { return $this->payable; }
    public function get_currency() { return $this->currency; }
    public function get_total( $context ) { return '12.50'; }
    public function get_order_number() { return '100<script>'; }
    public function get_items() { return array(); }
    public function get_order_item_totals() { return array(); }
}
function __( $text, $domain ) { return $text; }
function boltutil_wc_text( $zh, $en ) { return $en; }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $value ) { return esc_html( $value ); }
function esc_url( $value ) { return esc_html( $value ); }
function esc_html_e( $text, $domain ) { echo esc_html( $text ); }
function plugins_url( $path, $file ) { return '/plugin/' . $path; }
function add_query_arg( $key, $value, $url ) { return $url . '?' . $key . '=' . $value; }
function get_woocommerce_currency() { return 'USD'; }
function wc_get_page_permalink( $page ) { return '/shop/'; }
function wc_get_template( $name ) { echo '<p class="native-terms">Native terms</p>'; }
function wp_nonce_field( $action, $name ) { echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="fixture-nonce" />'; }
function do_action( $name, ...$args ) { $GLOBALS['hooks'][] = $name; }
function apply_filters( $name, $value, ...$args ) { return $value; }
function wp_kses_post( $html ) { return $html; }
require dirname( __DIR__ ) . '/includes/class-boltutil-wc-gateway.php';
$checks = 0;
function check_view( $ok, $name ) { global $checks; if ( ! $ok ) throw new RuntimeException( $name ); $checks++; }
$gateway = ( new ReflectionClass( 'BoltUtil_WC_Gateway' ) )->newInstanceWithoutConstructor();
$order = new WC_Order();
$args = array( 'order' => $order, 'available_gateways' => array( 'boltutil_usdt' => $gateway ) );
$path = BoltUtil_WC_Checkout::pay_template( '/native/form-pay.php', 'checkout/form-pay.php', $args );
check_view( false !== strpos( $path, '/includes/templates/form-pay.php' ), 'Native authorised single-gateway invoice gets shared view' );
$multiple = $args; $multiple['available_gateways']['other'] = new stdClass();
check_view( '/native/form-pay.php' === BoltUtil_WC_Checkout::pay_template( '/native/form-pay.php', 'checkout/form-pay.php', $multiple ), 'Other payment choices preserve native template' );
check_view( '12.50' === BoltUtil_WC_Checkout::context()['amount'] && '100<script>' === BoltUtil_WC_Checkout::context()['reference'], 'Multi-gateway invoice uses the authorised order, not the cart balance' );
check_view( '/native/form-pay.php' === BoltUtil_WC_Checkout::pay_template( '/native/form-pay.php', 'other.php', $args ), 'Unrelated templates are untouched' );
$order->currency = 'EUR'; check_view( '/native/form-pay.php' === BoltUtil_WC_Checkout::pay_template( '/native/form-pay.php', 'checkout/form-pay.php', $args ), 'Non-USD invoice is not overridden' );
$order->currency = 'USD'; $order->payable = false; check_view( '/native/form-pay.php' === BoltUtil_WC_Checkout::pay_template( '/native/form-pay.php', 'checkout/form-pay.php', $args ), 'Paid invoice is not overridden' ); $order->payable = true;
$routes = array(); foreach ( array( 'USDT', 'USDC' ) as $token ) foreach ( array_keys( BoltUtil_WC_Gateway::NETWORKS ) as $network ) if ( ! ( 'USDC' === $token && 'TRC20' === $network ) ) $routes[ $token . ':' . $network ] = array( 'token' => $token, 'network' => $network );
ob_start(); BoltUtil_WC_Checkout::render( $gateway, $routes, BoltUtil_WC_Checkout::context( $order ), '<button class="primary" type="submit">Pay</button>' ); $html = ob_get_clean();
check_view( 11 === substr_count( $html, 'name="boltutil_route"' ), 'All eleven routes are present for progressive enhancement' );
check_view( false === strpos( $html, 'checked=' ), 'No network is preselected' );
check_view( false === strpos( $html, '<script>' ) && false !== strpos( $html, '100&lt;script&gt;' ), 'Order reference is escaped' );
check_view( false !== strpos( $html, '>12.50</strong>' ) && false !== strpos( $html, 'class="checkout-header"' ), 'Shared header and exact amount are rendered' );
check_view( false !== strpos( $html, 'chains/tron.png' ) && false !== strpos( $html, 'chains/solana.png' ), 'Original site asset filenames are used' );
check_view( false !== strpos( $html, 'Binance-Peg USDC' ) && false !== strpos( $html, 'Bridged USDT' ), 'Asset variants remain explicit' );
check_view( false === strpos( $html, 'name="amount"' ), 'Displayed balance is never a client-controlled payment amount' );
check_view( '9999999999999.12' === BoltUtil_WC_Checkout::amount( '9999999999999.120000' ), 'Large display amounts retain decimal precision' );
check_view( '' === BoltUtil_WC_Checkout::amount( '-1' ) && '' === BoltUtil_WC_Checkout::amount( '1e2' ), 'Malformed display amounts fail closed' );
check_view( '' === BoltUtil_WC_Checkout::merchant_description( $gateway ), 'Default merchant description does not duplicate standard copy' );
class Fixture_Gateway extends BoltUtil_WC_Gateway { public function available_routes() { return $GLOBALS['routes']; } }
$GLOBALS['routes'] = $routes;
$gateway = ( new ReflectionClass( 'Fixture_Gateway' ) )->newInstanceWithoutConstructor();
$available_gateways = array( 'boltutil_usdt' => $gateway ); $order_button_text = 'Native pay';
ob_start(); include $path; $form = ob_get_clean();
check_view( false !== strpos( $form, 'id="order_review" method="post"' ), 'Order-pay keeps native POST form' );
check_view( false !== strpos( $form, 'name="payment_method" value="boltutil_usdt"' ) && false !== strpos( $form, 'name="woocommerce_pay" value="1"' ), 'Native gateway and pay markers are retained' );
check_view( false !== strpos( $form, 'name="woocommerce-pay-nonce"' ) && false !== strpos( $form, 'Native terms' ), 'Native nonce and terms are retained' );
check_view( in_array( 'woocommerce_pay_order_before_submit', $GLOBALS['hooks'], true ) && in_array( 'woocommerce_pay_order_after_submit', $GLOBALS['hooks'], true ), 'Native submit hooks are retained' );
check_view( false !== strpos( $form, 'class="boltutil-order-details"' ), 'Original order details stay available' );
echo 'Checkout view fixtures passed: ' . $checks . " checks.\n";
