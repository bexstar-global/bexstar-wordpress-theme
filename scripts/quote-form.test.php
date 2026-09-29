<?php
/** Standalone server validation tests. Transport is mocked: no real messages sent. */
define( 'ABSPATH', __DIR__ );
function add_action( ...$args ) {}
function add_shortcode( ...$args ) {}
function sanitize_text_field( $s ) { return trim( preg_replace( '/[\r\n\t]+/', ' ', strip_tags( $s ) ) ); }
function sanitize_textarea_field( $s ) { return trim( strip_tags( $s ) ); }
function sanitize_email( $s ) { return filter_var( $s, FILTER_SANITIZE_EMAIL ); }
function is_email( $s ) { return filter_var( $s, FILTER_VALIDATE_EMAIL ); }
function wp_verify_nonce( $n, $action ) { return 'valid' === $n && 'bexstar_quote' === $action; }
function wp_mail( $to, $subject, $message, $headers ) { $GLOBALS['mail_calls'][] = compact( 'to', 'subject', 'message', 'headers' ); return $GLOBALS['mail_ok']; }
function esc_attr( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function esc_html( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function esc_textarea( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function esc_url( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function home_url( $s ) { return 'https://example.test' . $s; }
function wp_nonce_field( ...$args ) { echo '<input type="hidden" name="bex_quote_nonce" value="valid">'; }
function checked( $a, $b ) { if ( $a === $b ) { echo 'checked="checked"'; } }
function selected( $a, $b ) { if ( $a === $b ) { echo 'selected="selected"'; } }
require __DIR__ . '/../inc/quote-form.php';
$GLOBALS['mail_ok'] = true; $GLOBALS['mail_calls'] = array();
$base = array( 'country' => 'Poland', 'postal' => '97-220', 'whatsapp' => '+48 555 123 456', 'email' => '', 'method' => 'package', 'package_weight' => '27.3', 'length' => '47', 'width' => '31', 'height' => '43', 'pieces' => '4', 'website' => '', 'bex_quote_nonce' => 'valid' );
$count = 0;
function check( $yes, $message ) { global $count; if ( ! $yes ) { throw new Exception( $message ); } $count++; }
$r = bexstar_quote_process( $base ); check( $r['sent'], 'WhatsApp only, package method' );
check( 'quotes@bexgl.com' === $GLOBALS['mail_calls'][0]['to'], 'Recipient' );
check( false !== strpos( $GLOBALS['mail_calls'][0]['message'], '47 x 31 x 43' ), 'Package dimensions in notification' );
$r = bexstar_quote_process( array_merge( $base, array( 'whatsapp' => '', 'email' => 'client@example.com' ) ) ); check( $r['sent'], 'Email only' );
$r = bexstar_quote_process( array_merge( $base, array( 'method' => 'total', 'total_weight' => '109.2', 'volume' => '.25' ) ) ); check( $r['sent'], 'Total method, inactive package ignored' );
$message = end( $GLOBALS['mail_calls'] )['message']; check( false !== strpos( $message, 'Total volume (CBM): .25' ) && false === strpos( $message, 'Weight per package' ), 'Only selected cargo method is sent' );
$failures = array(
    array( 'whatsapp' => '', 'email' => '' ), array( 'email' => 'invalid' ), array( 'email' => "a@example.com\r\nBcc: attacker@example.com" ),
    array( 'country' => '' ), array( 'postal' => '' ), array( 'method' => 'invalid' ), array( 'pieces' => '1.5' ),
    array( 'package_weight' => '-1' ), array( 'package_weight' => '1e9' ), array( 'package_weight' => 'INF' ),
    array( 'whatsapp' => '123' ), array( 'bex_quote_nonce' => 'invalid' ), array( 'website' => 'spam' ), array( 'delivery' => 'unsupported' ),
    array( 'email' => array( 'bad' ), 'whatsapp' => '' ), array( 'additional' => str_repeat( 'x', 3001 ) ),
);
foreach ( array( 'package_weight', 'length', 'width', 'height', 'pieces' ) as $field ) { $failures[] = array( $field => '' ); }
foreach ( array( 'total_weight', 'volume' ) as $field ) { $failures[] = array( 'method' => 'total', 'total_weight' => '100', 'volume' => '1', $field => '' ); }
foreach ( $failures as $input ) { $calls = count( $GLOBALS['mail_calls'] ); $r = bexstar_quote_process( array_merge( $base, $input ) ); check( ! $r['sent'] && $r['errors'] && count( $GLOBALS['mail_calls'] ) === $calls, 'Invalid request must not send: ' . json_encode( $input ) ); }
$GLOBALS['mail_ok'] = false; $r = bexstar_quote_process( $base ); check( ! $r['sent'] && isset( $r['errors']['send'] ), 'Mail failure' );
$GLOBALS['bexstar_quote_result'] = $r; check( false === strpos( bexstar_render_quote_form(), 'Thank you. We received' ), 'No false success' );
check( false !== strpos( bexstar_render_quote_form(), 'value="97-220"' ), 'Failed submission retains fields' );
$GLOBALS['bexstar_quote_result'] = array( 'data' => array(), 'errors' => array(), 'sent' => true ); check( false !== strpos( bexstar_render_quote_form(), 'Thank you. We received your shipment details.' ), 'Success message' );
list( $data ) = bexstar_quote_validate( array_merge( $base, array( 'name' => '<script>alert(1)</script>' ) ) ); check( false === strpos( $data['name'], '<script>' ), 'Sanitization' );
unset( $GLOBALS['bexstar_quote_result'] );
if ( in_array( '--render', $argv, true ) ) { echo bexstar_render_quote_form(); } else { echo "PASS: $count server-side checks (mocked WordPress mail transport).\n"; }
