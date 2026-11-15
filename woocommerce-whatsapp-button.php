
<?php
/**
 * Plugin Name: WooCommerce WhatsApp Button
 * Description: Adds a WhatsApp contact button to WooCommerce product pages.
 * Version: 1.0.0
 * Author: Cesar.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
add_action(
    'woocommerce_after_add_to_cart_form',
    'wawb_add_whatsapp_button'
);

add_action( 'admin_menu', 'wawb_add_settings_page' );

function wawb_add_settings_page() {

    add_options_page(
        'WhatsApp Button Settings',
        'WhatsApp Button',
        'manage_options',
        'wawb-settings',
        'wawb_render_settings_page'
    );
}
function wawb_render_settings_page() {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>WhatsApp Button Settings</h1>

        <form method="post" action="options.php">

            <?php
            settings_fields( 'wawb_settings_group' );

            do_settings_sections( 'wawb-settings' );

            submit_button();
            ?>

        </form>
    </div>
    <?php
}

add_action( 'admin_init', 'wawb_register_settings' );

function wawb_register_settings() {

    register_setting(
        'wawb_settings_group',
        'wawb_phone_number',
        array(
            'sanitize_callback' => 'wawb_sanitize_phone_number',
        )
    );

    add_settings_section(
        'wawb_main_section',
        'WhatsApp Configuration',
        '__return_false',
        'wawb-settings'
    );

    add_settings_field(
        'wawb_phone_field',
        'WhatsApp Phone Number',
        'wawb_render_phone_field',
        'wawb-settings',
        'wawb_main_section'
    );
}

function wawb_render_phone_field() {

    $phone = get_option( 'wawb_phone_number', '' );

    echo '<input type="text" name="wawb_phone_number" value="' . esc_attr( $phone ) . '" class="regular-text">';

    echo '<p class="description">Enter your WhatsApp number with country code, without + or spaces.</p>';
}

function wawb_add_whatsapp_button() {

    if ( ! is_product() ) {
        return;
    }

    global $product;

    if ( ! $product ) {
        return;
    }

    $phone = get_option( 'wawb_phone_number', '' );

if ( empty( $phone ) ) {
    return;
};

    $product_name = $product->get_name();
    $product_url  = get_permalink( $product->get_id() );

    $message = 'Hello, I am interested in: '
        . $product_name . ' '
        . $product_url;

    $whatsapp_url = 'https://wa.me/' . $phone
        . '?text=' . rawurlencode( $message );

    echo '<a class="wawb-button" href="' . esc_url( $whatsapp_url ) . '" target="_blank" rel="noopener noreferrer">';
    echo esc_html__( 'Contact us on WhatsApp', 'wawb' );
    echo '</a>';
}


function wawb_sanitize_phone_number( $phone ) {

    if ( ! is_string( $phone ) ) {
        return get_option( 'wawb_phone_number', '' );
    }

    // Allow only digits, spaces, +, hyphens and parentheses.
    if ( ! preg_match( '/^[0-9+\s()\-]+$/', $phone ) ) {

        add_settings_error(
            'wawb_phone_number',
            'wawb_invalid_phone',
            'Please enter a valid phone number.',
            'error'
        );

        return get_option( 'wawb_phone_number', '' );
    }

    // Remove formatting characters.
    $phone = preg_replace( '/\D/', '', $phone );

    if ( strlen( $phone ) < 8 || strlen( $phone ) > 15 ) {

        add_settings_error(
            'wawb_phone_number',
            'wawb_invalid_phone_length',
            'Phone number must contain 8 to 15 digits.',
            'error'
        );

        return get_option( 'wawb_phone_number', '' );
    }

    return $phone;
}


