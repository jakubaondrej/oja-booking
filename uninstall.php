<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$oja_db_version = 'oja_db_version';

delete_option($oja_db_version);
// for site options in Multisite
delete_site_option($oja_db_version);

// drop a custom database table
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS " . TERMS_EVENT_TABLE_NAME);
$wpdb->query("DROP TABLE IF EXISTS " . BOOKING_TERMS_EVENT_TABLE_NAME);
$wpdb->query("DROP TABLE IF EXISTS " . BOOKING_GROUP_TABLE_NAME);
