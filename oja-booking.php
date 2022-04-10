<?php

/**
 * Plugin Name:       OJA Booking
 * Plugin URI:        
 * Description:       This is my best booking plugin.
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Ondřej Jakuba
 * Author URI:        https://ojakuba.eu
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        
 * Text Domain:       oja
 * Domain Path:       /languages
 */

require_once plugin_dir_path(__FILE__) . 'templates/PageTemplater.php';
require_once plugin_dir_path(__FILE__) . 'templates/contact.php';

require_once plugin_dir_path(__FILE__) . 'includes/oja_database.php';
require_once plugin_dir_path(__FILE__) . 'includes/reservations.php';
require_once plugin_dir_path(__FILE__) . 'includes/email.php';

require_once plugin_dir_path(__FILE__) . 'public/icons.php';

require_once plugin_dir_path(__FILE__) . 'includes/posttypes/event.php';
require_once plugin_dir_path(__FILE__) . 'includes/taxonomies/languages.php';
require_once plugin_dir_path(__FILE__) . 'includes/taxonomies/price_categories.php';

require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Oja_Reservation_Type_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Oja_The_Term_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Oja_Date_Span_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Oja_Repeat_Days_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Oja_Repeat_Months_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Oja_Repeat_Times_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Oja_Group_Size_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Oja_Price_Categories_Meta_Box.php';


/**********************************************
 *          ADMIN
 **********************************************/

if (current_user_can( 'edit_posts' )) {
    // we are in admin mode
    //require_once __DIR__ . '/admin/plugin-name-admin.php';
    $categories = get_terms(array(
        'taxonomy' => 'oja_price_categories',
        'hide_empty' => false,
        'meta_key' => 'private_party',
        'meta_compare' => 'NOT EXISTS'
    ));
    if (is_wp_error($categories) || !isset($categories)) {
        add_action('admin_notices', 'oja_admin_categories_warning');
    }

    function oja_admin_categories_warning()
    {
        global $pagenow;
        $admin_pages = ['index.php', 'edit.php', 'plugins.php'];
        if (in_array($pagenow, $admin_pages)) {
            $class = 'notice notice-warning is-dismissible';
            $url = admin_url('edit-tags.php?taxonomy=oja_price_categories'); //&post_type=oja_event
            $link = sprintf(wp_kses(__('There are no categories. <a href="%s">Please create one</a>.', 'oja'), array('a' => array('href' => array()))), esc_url($url));
            
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $link);
        }
    }
    
}


/**********************************************
 *          OTHERS
 **********************************************/
if (!function_exists('IsNullOrEmptyString')) {
    function IsNullOrEmptyString($str)
    {
        return ($str === null || trim($str) === '' || !isset($str));
    }
}


function oja_get_currency($num)
{
    $local_wp = get_locale();
    $local_lang = $local_wp . ".utf8";
    setlocale(LC_ALL, $local_lang);
    $locale_info = localeconv();
    $cur_val = number_format_i18n($num, $locale_info['mon_decimal_point']);
    return $cur_val . " " . $locale_info['currency_symbol'];
}

/**
 * Return unicode char by its code
 *
 * @param int $u
 * @return char
 */
function unichr($u)
{
    return mb_convert_encoding('&#' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
}


function oja_booking_enqueue()
{
    wp_enqueue_script(
        'oja-currency-js',
        plugins_url('public/js/oja_currency.js',  __FILE__),
        array('jquery')
    );
    wp_localize_script(
        'oja-currency-js',
        'Oja_Currency',
        array(
            'current_currency'  => get_option('oja_current_currency', 'USD')

        )
    );

    wp_enqueue_script(
        'oja-booking-js',
        plugins_url('public/js/oja_booking.js',  __FILE__),
        array('jquery', 'oja-currency-js')
    );
    wp_localize_script(
        'oja-booking-js',
        'Oja_Ajax',
        array(
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nextNonce' => wp_create_nonce('oja-events-next-nonce'),
            'bookingNonce' => wp_create_nonce('oja-create-booking-nonce'),
            'current_page' => 0,
            'posts_per_page' => 6,
            'private_party' => oja_get_private_party_price_categories()
        )
    );
}

function oja_enqueue_scripts()
{
    wp_enqueue_style(
        'oja-booking-style',
        plugins_url('public/css/style.css',  __FILE__),
        false,
        '1.3.0'
    );
    wp_enqueue_script(
        'oja-Bootstrap-js-async',
        plugins_url('public/js/bootstrap.min.js',  __FILE__),
        array('jquery')
    );
    wp_enqueue_script(
        'oja-Bootstrap-bundle-async',
        plugins_url('public/js/bootstrap.bundle.min.js',  __FILE__),
        array('jquery')
    );
    wp_enqueue_script(
        'oja-main-js-async',
        plugins_url('public/js/main.js',  __FILE__),
        array('jquery')
    );
    wp_localize_script(
        'oja-main-js-async',
        'Oja_Properties',
        array(
            'icons' => get_template_directory_uri() . '/assets/icons/',
        )
    );
}

function oja_show_404()
{
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}

function oja_get_alert_placeholder()
{
?>
    <div class="visually-hidden">
        <?php
        oja_get_warning_icon();
        oja_get_success_icon();
        ?>
    </div>
    <div id="liveAlertPlaceholder"></div>
<?php
}
