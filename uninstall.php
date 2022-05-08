<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$ojabooking_db_version = 'ojabooking_db_version';

delete_option($ojabooking_db_version);
// for site options in Multisite
delete_site_option($ojabooking_db_version);

// drop a custom database table
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS " . ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME);
$wpdb->query("DROP TABLE IF EXISTS " . BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME);
$wpdb->query("DROP TABLE IF EXISTS " . ojabooking_BOOKING_GROUP_TABLE_NAME);
