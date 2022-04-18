<?php
global $wpdb;
define('TERMS_EVENT_TABLE_NAME', $wpdb->prefix . 'event_terms');
define('BOOKING_TERMS_EVENT_TABLE_NAME', $wpdb->prefix . 'event_terms_booking');
define('BOOKING_GROUP_TABLE_NAME', $wpdb->prefix . 'booking_group');
/**
 * Create DB tables
 */
function oja_db_custom_tables()
{
    global $wpdb;
    $oja_actual_version = '1.0.0';
    $installed_ver = get_option("oja_db_version");
    if ($installed_ver == $oja_actual_version) {
        return;
    }
    if (empty($installed_ver)) {
        $event_terms_sql_create = 'CREATE TABLE ' . TERMS_EVENT_TABLE_NAME .
            '(ID BIGINT NOT NULL AUTO_INCREMENT,
            event_id BIGINT NOT NULL,
            term TIMESTAMP NOT NULL,
            language VARCHAR(32) NULL,
            PRIMARY KEY (ID)
            );';

        $event_terms_booking_sql_create = 'CREATE TABLE ' . BOOKING_TERMS_EVENT_TABLE_NAME .
            '(ID BIGINT NOT NULL AUTO_INCREMENT,
            user_email VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            tel VARCHAR(18) NOT NULL,
            school_name_department VARCHAR(255),
            class_department VARCHAR(255), 
            term_id BIGINT NOT NULL,
            status VARCHAR(32) DEFAULT "created",
            code VARCHAR(64) NOT NULL,
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (ID)
            );';

        $booking_group_sql_create = 'CREATE TABLE ' . BOOKING_GROUP_TABLE_NAME .
            '(ID BIGINT NOT NULL AUTO_INCREMENT,
            booking_id BIGINT NOT NULL,
            category VARCHAR(32) NOT NULL,
            count INT NOT NULL,
            PRIMARY KEY (ID)
            );';

        oja_db_request_query($event_terms_sql_create);
        oja_db_request_query($event_terms_booking_sql_create);
        oja_db_request_query($booking_group_sql_create);
    }
    if ($installed_ver = '1.0.1') {
        $booking_table = BOOKING_TERMS_EVENT_TABLE_NAME;

        $query = "ALTER TABLE {$booking_table} 
            ADD COLUMN tel VARCHAR(18) NOT NULL,
            ADD COLUMN school_name_department VARCHAR(255),
            ADD COLUMN class_department VARCHAR(255);";
        
        $wpdb->query($query);
    }
    update_option("oja_db_version", $oja_actual_version);
}


function oja_db_request_query($query)
{
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    return dbDelta($query);
}


function oja_get_booking_statuses()
{
    return array(
        "created",
        "confirmed",
        "accepted",
        "canceled",
    );
}
