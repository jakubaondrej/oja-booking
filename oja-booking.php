<?php

/**
 * OJA BOOKING
 * 
 * Plugin Name:       OJA Booking
 * Description:       This is my best booking plugin.
 * Version:           0.1
 * Requires at least: 5.3
 * Requires PHP:      7.2
 * Author:            Ondřej Jakuba
 * Author URI:        https://jakubao.eu
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       oja
 * Domain Path:       /languages
 * Tags:              Booking, Events, Reservation, Private party, Multilanguage
 */


register_activation_hook(__FILE__, 'ojabooking_booking_activation');
register_activation_hook(__FILE__, 'ojabooking_db_custom_tables');
function ojabooking_booking_activation()
{
    $new_page_id = ojabooking_create_page_if_not_exists('Terms and Conditions');
    update_option('ojabooking_terms_and_conditions', $new_page_id);

    $new_page_id = ojabooking_create_page_if_not_exists('Booking', '', 'ojabooking-page.php');
    update_option('ojabooking_booking_page', $new_page_id);

    $new_page_id = ojabooking_create_page_if_not_exists('Booking confirmation', '', 'ojabooking_confirmation-page.php');
    update_option('ojabooking_booking_confirmation_page', $new_page_id);
}

function ojabooking_create_page_if_not_exists($new_page_title, $new_page_content = '', $new_page_template = '')
{
    $page_check = get_page_by_title($new_page_title);
    $new_page = array(
        'post_type' => 'page',
        'post_title' => $new_page_title,
        'post_content' => $new_page_content,
        'post_status' => 'publish',
        'post_author' => 1,
    );
    if (!isset($page_check->ID)) {
        $new_page_id = wp_insert_post($new_page);

        if (!empty($new_page_template)) {
            $newmeta = update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
        }
        return $new_page_id;
    }
    return $page_check->ID;
}

require_once plugin_dir_path(__FILE__) . 'templates/Ojabooking_PageTemplater.php';
require_once plugin_dir_path(__FILE__) . 'templates/ojabooking_contact.php';

require_once plugin_dir_path(__FILE__) . 'includes/ojabooking_database.php';
require_once plugin_dir_path(__FILE__) . 'includes/reservations.php';
require_once plugin_dir_path(__FILE__) . 'includes/email.php';

require_once plugin_dir_path(__FILE__) . 'public/icons.php';

require_once plugin_dir_path(__FILE__) . 'includes/posttypes/event.php';
require_once plugin_dir_path(__FILE__) . 'includes/taxonomies/languages.php';
require_once plugin_dir_path(__FILE__) . 'includes/taxonomies/price_categories.php';

require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Ojabooking_Reservation_Type_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Ojabooking_The_Term_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Ojabooking_Date_Span_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Ojabooking_Repeat_Days_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Ojabooking_Repeat_Months_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Ojabooking_Repeat_Times_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Ojabooking_Group_Size_Meta_Box.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/Ojabooking_Price_Categories_Meta_Box.php';


/**********************************************
 *          ADMIN
 **********************************************/
if (!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php");
}
if (current_user_can('edit_published_pages')) {
    require_once plugin_dir_path(__FILE__) . 'includes/options/Ojabooking_Booking_Admin_Page.php';
    require_once plugin_dir_path(__FILE__) . 'includes/options/Ojabooking_Booking_Terms_Admin_Page.php';
}
if (current_user_can('manage_options')) {
    require_once plugin_dir_path(__FILE__) . 'includes/options/Ojabooking_Bank_Holiday_Options_Page.php';
    require_once plugin_dir_path(__FILE__) . 'includes/options/Ojabooking_Currency_Options_Page.php';
    require_once plugin_dir_path(__FILE__) . 'includes/options/Ojabooking_Booking_Language_Options_Page.php';
    require_once plugin_dir_path(__FILE__) . 'includes/options/Ojabooking_Price_Categories_Options_Page.php';
    require_once plugin_dir_path(__FILE__) . 'includes/options/Ojabooking_Terms_Conditions.php';
    require_once plugin_dir_path(__FILE__) . 'includes/options/Ojabooking_Color_Style.php';
}
add_action('wp_loaded', function () {
    if (is_admin()) {
        // we are in admin mode

        global $pagenow;
        $admin_pages = ['index.php', 'edit.php', 'plugins.php'];
        if (in_array($pagenow, $admin_pages)) {

            ojabooking_check_exists_category();

            $terms_page_id = get_option('ojabooking_terms_and_conditions');
            $ojabooking_booking_page = get_option('ojabooking_booking_page');
            $ojabooking_booking_confirmation_page = get_option('ojabooking_booking_confirmation_page');

            if (is_wp_error($terms_page_id) || !isset($terms_page_id) || !$terms_page_id)
                add_action('admin_notices', 'ojabooking_admin_terms_warning');

            if (is_wp_error($ojabooking_booking_page) || !isset($ojabooking_booking_page) || !$ojabooking_booking_page)
                add_action('admin_notices', 'ojabooking_admin_bookings_warning');

            if (is_wp_error($ojabooking_booking_confirmation_page) || !isset($ojabooking_booking_confirmation_page) || !$ojabooking_booking_confirmation_page)
                add_action('admin_notices', 'ojabooking_admin_confirmation_warning');

            global $wpdb;
            $table = ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                ojabooking_admin_DB_warning($table);
            }
            $table = BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                ojabooking_admin_DB_warning($table);
            }
            $table = ojabooking_BOOKING_GROUP_TABLE_NAME;
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                ojabooking_admin_DB_warning($table);
            }
        }
    }
});

if (is_admin()) {
    function ojabooking_admin_pagination($num_of_pages = '',  $page = '')
    {
        if (empty($page)) {
            $page = 1;
        }
        if ($num_of_pages == '') {
            $num_of_pages = 1;
        }
        $page_links = paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;', 'text-domain'),
            'next_text' => __('&raquo;', 'text-domain'),
            'total' => $num_of_pages,
            'current' => $page
        ));

        if ($page_links) {
            echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
        }
    }

    function ojabooking_admin_categories_warning()
    {
        $class = 'notice notice-warning is-dismissible';
        $url = admin_url('edit-tags.php?taxonomy=ojabooking_price_categories'); //&post_type=ojabooking_event
        $link = sprintf(wp_kses(__('There are no categories. <a href="%s">Please create one</a>.', 'ojabooking'), array('a' => array('href' => array()))), esc_url($url));

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $link);
    }
    
    function ojabooking_admin_default_categories_warning()
    {
        $class = 'notice notice-warning is-dismissible';
        $url = admin_url('options-general.php?page=ojabooking_price_categories'); //&post_type=ojabooking_event
        $link = sprintf(wp_kses(__('Default category is not set. <a href="%s">Please set it</a>.', 'ojabooking'), array('a' => array('href' => array()))), esc_url($url));

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $link);
    }

    function ojabooking_admin_terms_warning()
    {
        $class = 'notice notice-warning is-dismissible';
        $url = admin_url('options-general.php?page=ojabooking_terms_conditions'); //&post_type=ojabooking_event
        $link = sprintf(wp_kses(__('It looks like there is no "Terms and conditions" page. <a href="%s">Please create one</a>.', 'ojabooking'), array('a' => array('href' => array()))), esc_url($url));

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $link);
    }

    function ojabooking_admin_bookings_warning()
    {
        $class = 'notice notice-warning is-dismissible';
        $url = admin_url('edit.php?post_type=page'); //&post_type=ojabooking_event
        $link = sprintf(wp_kses(__('It looks like there is no "Booking" page. <a href="%s">Please create one</a>.', 'ojabooking'), array('a' => array('href' => array()))), esc_url($url));

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $link);
    }

    function ojabooking_admin_confirmation_warning()
    {
        $class = 'notice notice-warning is-dismissible';
        $url = admin_url('edit.php?post_type=page'); //&post_type=ojabooking_event
        $link = sprintf(wp_kses(__('It looks like there is no "Booking Confirmation" page. <a href="%s">Please create one</a>.', 'ojabooking'), array('a' => array('href' => array()))), esc_url($url));

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $link);
    }

    function ojabooking_admin_DB_warning($db_name)
    {
        $class = 'notice notice-warning is-dismissible';
        $text = sprintf(esc_html__('Database "%s" does not exists.', 'ojabooking'), $db_name);
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $text);
    }

    function ojabooking_check_exists_category()
    {
        $tax = get_taxonomy('ojabooking_price_categories');
        $categories = get_terms(array(
            'taxonomy' => 'ojabooking_price_categories',
            'hide_empty' => false
        ));
        if (is_wp_error($categories) || !isset($categories)) {
            add_action('admin_notices', 'ojabooking_admin_categories_warning');
            return;
        }
        $default_category = get_option('ojabooking_default_price_category', '');
        if (is_wp_error($default_category) || !isset($default_category)) {
            add_action('admin_notices', 'ojabooking_admin_default_categories_warning');
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


function ojabooking_get_currency($num)
{
    $current_currency = get_option('ojabooking_current_currency');
     $local_wp = get_locale();
    $local_lang = $local_wp . ".utf8";
    setlocale(LC_ALL, $local_lang);
    $locale_info = localeconv();
    $cur_val = number_format_i18n($num, $locale_info['mon_decimal_point']);
    return $cur_val . " " . ojabooking_get_currency_symbol($current_currency);
}

function ojabooking_booking_enqueue()
{
    wp_enqueue_script(
        'oja-currency-js',
        plugins_url('public/js/ojabooking_currency.js',  __FILE__),
        array('jquery')
    );
    wp_localize_script(
        'oja-currency-js',
        'Ojabooking_Currency',
        array(
            'current_currency'  => get_option('ojabooking_current_currency', 'USD')
        )
    );

    wp_enqueue_script(
        'oja-booking-js',
        plugins_url('public/js/ojabooking_booking.js',  __FILE__),
        array('jquery', 'oja-currency-js')
    );
    wp_localize_script(
        'oja-booking-js',
        'Ojabooking_Ajax',
        array(
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nextNonce' => wp_create_nonce('oja-events-next-nonce'),
            'bookingNonce' => wp_create_nonce('oja-create-booking-nonce'),
            'current_page' => 0,
            'posts_per_page' => 6,
            'private_party' => ojabooking_get_private_party_price_categories(),
            'select_group_text' => __('Select group','ojabooking')
        )
    );
    wp_enqueue_style(
        'oja-booking-style',
        plugins_url('public/css/style.css',  __FILE__),
        false,
        '1.4.0'
    );
}

function ojabooking_enqueue_scripts()
{
    
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
        'Ojabooking_Properties',
        array(
            'icons' => get_template_directory_uri() . '/assets/icons/',
        )
    );
}

function ojabooking_show_404()
{
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}

function ojabooking_get_alert_placeholder()
{
?>
    <div class="visually-hidden">
        <?php
        ojabooking_get_warning_icon();
        ojabooking_get_success_icon();
        ?>
    </div>
    <div id="liveAlertPlaceholder"></div>
<?php
}


add_action('save_post', 'ojabooking_update_page_id', 10, 3);

function ojabooking_update_page_id($post_id, $post, $update)
{
    if (esc_attr($_REQUEST['page_template']) == 'ojabooking-page.php') {
        update_option('ojabooking_booking_page', $post_id);
    }

    if (esc_attr($_REQUEST['page_template']) == 'ojabooking_confirmation-page.php') {
        update_option('ojabooking_booking_confirmation_page', $post_id);
    }
}


function ojabooking_get_local_date_time($date_time)
{
    $date_format = get_option('date_format');
    $time_format = get_option('time_format');
    $datetime = new DateTime($date_time);
    // The date in the local timezone.
    return $datetime->format("$date_format $time_format");
}


function ojabooking_get_object_by_property_value(array $objects, $property, $value)
{
    foreach ($objects as $object) {
        if (property_exists($object, $property) && $object->{$property} === $value) {
            return $object;
        }
    }
    return new stdClass();
}

function ojabooking_is_phone_correct($number){
    return preg_match('/^\+?([0-9]{3})?\)?[-. ]?([0-9]{3})[-. ]?([0-9]{3})[-. ]?([0-9]{3})$/i', $number);
}