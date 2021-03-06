<?php
function get_created_booking_time_filter($table = "")
{
    if (!empty($table)) {
        $table = $table . ".";
    }
    return ' AND (NOW() - INTERVAL 1 HOUR) <= ' . $table . 'created ';
}

function ojabooking_create_event_term(int $event_id, string $term, string $language = "")
{

    global $wpdb;
    if (!ojabooking_can_be_create_event_term($event_id, $term)) {
        return 0;
    }

    $data = array(
        'event_id' => $event_id,
        'term' => $term,
        'language' => $language,
    );

    $format = array('%d', '%s', '%s');
    $wpdb->insert(ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME, $data, $format);
    $my_id = $wpdb->insert_id;
    return $my_id;
}

function ojabooking_can_be_shown_event($event_id, $date)
{
    if (ojabooking_is_event_periodical($event_id)) {
        $d = ojabooking_event_can_be_this_day($event_id, $date);
        $m = ojabooking_event_can_be_this_month($event_id, $date);
        $h = ojabooking_is_term_bank_holiday($date) ? ojabooking_event_can_be_on_bank_holiday($event_id) : true;
        $term_id = ojabooking_get_event_term_by_datetime($event_id, $date);

        return $d && $m && $h && is_null($term_id);
    }
    return ojabooking_is_one_time_event_the_day($event_id, $date);
}

function ojabooking_is_event_periodical($event_id)
{
    $ojabooking_reservation_type = get_post_meta($event_id, 'ojabooking_reservation_type', true);
    return (isset($ojabooking_reservation_type) && $ojabooking_reservation_type == 'periodical_event');
}

function ojabooking_is_one_time_event_the_day($event_id, $date)
{
    $event_term = get_post_meta($event_id, 'ojabooking_the_term', true);
    if (!isset($event_term)) return false;
    $event_time = strtotime($event_term);
    return date("Y-m-d", $event_time) == $date;
}

function ojabooking_get_event_time_terms($event_id)
{
    if (ojabooking_is_event_periodical($event_id))
        return get_post_meta($event_id, 'ojabooking_repeat_times', true);
    $event_term = get_post_meta($event_id, 'ojabooking_the_term', true);
    if (!isset($event_term))
        return null;
    $event_time = strtotime($event_term);
    return array(date("H:i", $event_time));
}

function ojabooking_is_periodical_event_actual(int $event_id, string $term)
{
    $term_time = strtotime($term);
    $ojabooking_start_term = get_post_meta($event_id, 'ojabooking_start_term', true);
    $ojabooking_end_term = get_post_meta($event_id, 'ojabooking_end_term', true);
    if (isset($ojabooking_start_term) && $ojabooking_start_term != '') {
        $ojabooking_start_term_time = strtotime($ojabooking_start_term);
        if ($term_time < $ojabooking_start_term_time) return false;
    }
    if (isset($ojabooking_end_term) && $ojabooking_end_term != '') {
        $ojabooking_end_term_time = strtotime($ojabooking_end_term);
        if ($term_time > $ojabooking_end_term_time) return false;
    }
    return true;
}

function ojabooking_get_periodical_event_next_term(int $event_id)
{
    $time = time();
    $time2 = $time;
    for ($i = 0; $i < 5; $i++) {
        $time = ojabooking_get_next_month_event_term($event_id, $time);
        $time = ojabooking_get_next_week_day_event_term($event_id, $time);
        $ojabooking_repeat_days = get_post_meta($event_id, 'ojabooking_repeat_days', true);
        $holiday = in_array(8, $ojabooking_repeat_days);
        $holidays = get_option('ojabooking_bank_holidays');
        $date = date("m-d", $time);
        if (!$holiday && in_array($date, $holidays)) {
            $time = strtotime("+1 days", $time);
        }
        $ojabooking_end_term = get_post_meta($event_id, 'ojabooking_end_term', true);
        if (!IsNullOrEmptyString($ojabooking_end_term)) {
            $ojabooking_end_term_time = strtotime($ojabooking_end_term);
            if ($time > $ojabooking_end_term_time) return false;
        }
        $ojabooking_start_term = get_post_meta($event_id, 'ojabooking_start_term', true);
        if (!IsNullOrEmptyString($ojabooking_start_term)) {
            $ojabooking_start_term_time = strtotime($ojabooking_start_term);
            if ($time < $ojabooking_start_term_time) return date("Y-m-d", $ojabooking_start_term_time);
        }
        if ($time2 == $time)
            return date("Y-m-d", $time);
        $time2 = $time;
    }
    return false;
}

function ojabooking_get_next_month_event_term($event_id, $time)
{
    $actual_month = intval(date('m', $time));
    $ojabooking_repeat_months = get_post_meta($event_id, 'ojabooking_repeat_months', true);
    if (!in_array($actual_month, $ojabooking_repeat_months)) {
        $filtered_values = array_filter(
            $ojabooking_repeat_months,
            function ($value) use ($actual_month) {
                return ($value >= $actual_month);
            }
        );
        $next_moth = empty($filtered_values) ? reset($ojabooking_repeat_months) : reset($filtered_values);
        $date = date("Y-" . $next_moth . "-1", $time);
        $time = strtotime($date);
        if ($next_moth < $actual_month)
            $time = strtotime("+1 year", $time);
    }
    return $time;
}

function ojabooking_get_next_week_day_event_term($event_id, $time)
{
    $week_day = intval(date('w', $time));
    $ojabooking_repeat_days = get_post_meta($event_id, 'ojabooking_repeat_days', true);
    $holiday = in_array(8, $ojabooking_repeat_days);
    if ($holiday) unset($ojabooking_repeat_days[array_search(8, $ojabooking_repeat_days)]);
    if (!in_array($week_day, $ojabooking_repeat_days)) {
        $filtered_values = array_filter(
            $ojabooking_repeat_days,
            function ($value) use ($week_day) {
                return ($value >= $week_day);
            }
        );
        $next_day = empty($filtered_values) ? reset($ojabooking_repeat_days) : reset($filtered_values);
        $add_days = $next_day - $week_day; //3-0
        $add_days = $add_days < 0 ? $add_days + 7 : $add_days;
        $time = strtotime("+" . $add_days . " days", $time);
    }
    return $time;
}
function ojabooking_can_be_create_event_term($event_id, $term)
{
    if (ojabooking_is_event_periodical($event_id)) {
        $is_actual = ojabooking_is_periodical_event_actual($event_id, $term);
        $d = ojabooking_event_can_be_this_day($event_id, $term);
        $m = ojabooking_event_can_be_this_month($event_id, $term);
        $t = ojabooking_event_can_be_this_time($event_id, $term);
        $h = ojabooking_is_term_bank_holiday($term) ? ojabooking_event_can_be_on_bank_holiday($event_id) : true;
        $term_id = ojabooking_get_event_term_by_datetime($event_id, $term);

        return $is_actual && $d && $m && $t && $h && is_null($term_id);
    }
    $event_term = get_post_meta($event_id, 'ojabooking_the_term', true);
    if (!isset($event_term)) return false;
    $event_time = strtotime($event_term);
    return date("Y-m-d H:i:00", $event_time) == $term;
}

function ojabooking_is_term_bank_holiday($term)
{
    $the_day = date('m-d', strtotime($term));
    $bank_holidays = get_option('ojabooking_bank_holidays');
    in_array($the_day, $bank_holidays);
}

function ojabooking_event_can_be_on_bank_holiday($event_id)
{
    $ojabooking_repeat_days = get_post_meta($event_id, 'ojabooking_repeat_days');
    return in_array(8, $ojabooking_repeat_days);
}

function ojabooking_event_can_be_this_day($event_id, $term)
{
    $dayofweek = date('w', strtotime($term));
    $ojabooking_repeat_days = get_post_meta($event_id, 'ojabooking_repeat_days', true);
    if (!is_array($ojabooking_repeat_days)) {
        return false;
    }
    return in_array($dayofweek, $ojabooking_repeat_days);
}

function ojabooking_event_can_be_this_time($event_id, $term)
{
    $time = date('H:i', strtotime($term));
    $ojabooking_repeat_times = get_post_meta($event_id, 'ojabooking_repeat_times', true);
    if (!is_array($ojabooking_repeat_times)) {
        return false;
    }
    return in_array($time, $ojabooking_repeat_times);
}

function ojabooking_event_can_be_this_month($event_id, $term)
{
    $month = date('m', strtotime($term));
    $ojabooking_repeat_months = get_post_meta($event_id, 'ojabooking_repeat_months', true);
    if (!is_array($ojabooking_repeat_months)) {
        return false;
    }
    return in_array($month, $ojabooking_repeat_months);
}

function ojabooking_get_event_term_by_id($term_id)
{
    global $wpdb;
    $term_table = ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $booking_group_table = ojabooking_BOOKING_GROUP_TABLE_NAME;
    $booking_time_filter = get_created_booking_time_filter("b1");
    $booking_table = BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;

    $query = "SELECT * FROM {$term_table} term
        LEFT JOIN (SELECT b1.term_id, COUNT(DISTINCT(b1.ID)) as booking_count, SUM(g1.count) group_size, 
            SUM(IF(g1.category IN (SELECT term_id FROM {$wpdb->prefix}termmeta m WHERE m.meta_key = 'private_party'),1,0)) > 0 AS private_party 
            FROM {$booking_table} b1
            LEFT JOIN {$booking_group_table} g1 ON g1.booking_id = b1.ID
            WHERE  (b1.status IN ('confirmed', 'accepted') OR (b1.status='created' {$booking_time_filter}) )
            GROUP BY b1.term_id) 
        AS b ON term.id = b.term_id
        WHERE ID=%d";

    $sql_query = $wpdb->prepare($query, $term_id);
    $term = $wpdb->get_row($sql_query);
    return $term;
}

//return null if term does not exists
function ojabooking_get_event_term_by_datetime($event_id, $term)
{
    global $wpdb;
    $booking_time_filter = get_created_booking_time_filter("b1");
    $term_table = ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $booking_table = BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $booking_group_table = ojabooking_BOOKING_GROUP_TABLE_NAME;
    $query = "SELECT term.*, b.booking_count AS booking_count, b.group_size AS group_size, b.private_party AS private_party  
        FROM {$term_table} term
        LEFT JOIN (SELECT b1.term_id, COUNT(DISTINCT(b1.ID)) as booking_count, SUM(g1.count) group_size, 
        SUM(IF(g1.category IN (SELECT term_id FROM {$wpdb->prefix}termmeta m WHERE m.meta_key = 'private_party'),1,0)) > 0 AS private_party 
        FROM {$booking_table} b1
            LEFT JOIN {$booking_group_table} g1 ON g1.booking_id = b1.ID
            WHERE  (b1.status IN ('confirmed', 'accepted') OR (b1.status='created' {$booking_time_filter}) )
            GROUP BY b1.term_id) 
        AS b ON term.id = b.term_id
        WHERE event_id=%d AND term=%s";
    $sql_query = $wpdb->prepare($query, $event_id, $term);
    $term_id = $wpdb->get_row($sql_query);
    return $term_id;
}

/**
 * Get occupancy for event by term
 * @param string $term booking term in 'YYYY-mm-dd HH:mm:ss' format (e.g. 2000-01-01 12:00:00)
 * @param int $event_id  ID of Event should be created booking for.
 * @return int count of people
 */
function ojabooking_get_event_occupancy_by_term($event_id, $term)
{
    global $wpdb;
    $sql_query = $wpdb->prepare('SELECT SUM(g.count) FROM ' . ojabooking_BOOKING_GROUP_TABLE_NAME . ' g ' .
        'LEFT JOIN ' . BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME . ' b ON b.id = g.booking_id ' .
        'LEFT JOIN ' . ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME . ' t ON t.id = b.term_id ' .
        'WHERE t.event_id=%d AND t.term=%s AND ((b.status="created" ' .
        get_created_booking_time_filter("b") . ') OR b.status IN ("confirmed","accepted"))', $event_id, $term);
    $term_id = $wpdb->get_var($sql_query);

    return $term_id;
}

/**
 * Get occupancy for term by ID
 * @param int $term_id 
 * @return int count of people
 */
function ojabooking_get_occupancy_by_term_id($term_id)
{
    global $wpdb;
    $booking_group_table = ojabooking_BOOKING_GROUP_TABLE_NAME;
    $booking_table = BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $booking_time_filter = get_created_booking_time_filter("b");
    $query = "SELECT SUM(g.count) FROM {$booking_group_table} g
        LEFT JOIN {$booking_table} b ON b.id = g.booking_id 
        WHERE b.term_id=%d AND ((b.statusIN ('confirmed', 'accepted') OR (b.status='created' {$booking_time_filter}) )
    ";

    $sql_query = $wpdb->prepare($query, $term_id);
    $term_id = $wpdb->get_var($sql_query);
    return $term_id;
}

/**
 * Get term event for booking by ID
 * @param int $booking_id 
 */
function ojabooking_get_booking_term_event($booking_id)
{
    global $wpdb;
    $sql_query = $wpdb->prepare('SELECT t.* FROM ' . ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME .
        ' t LEFT JOIN '  . BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME . ' b ON b.term_id = t.ID WHERE b.ID =%d', $booking_id);
    $event_id = $wpdb->get_row($sql_query);
    return $event_id;
}

/**
 * Get group size for booking by ID
 * @param int $booking_id 
 */
function ojabooking_get_booking_group_size($booking_id)
{
    global $wpdb;
    $sql_query = $wpdb->prepare('SELECT SUM(count) FROM ' . ojabooking_BOOKING_GROUP_TABLE_NAME .
        ' WHERE booking_id=%d', $booking_id);
    $event_id = $wpdb->get_var($sql_query);
    return $event_id;
}

/**
 * Get group size for booking by ID
 * @param int $booking_id 
 * @return array Group array(category=>count).
 */
function ojabooking_get_booking_group($booking_id)
{
    global $wpdb;
    $group_table = ojabooking_BOOKING_GROUP_TABLE_NAME;
    $sql_query = $wpdb->prepare("SELECT category, count FROM {$group_table} WHERE booking_id=%d", $booking_id);
    $wpdb_results = $wpdb->get_results($sql_query);
    $group = array();
    foreach ($wpdb_results as $x) {
        $group[$x->category] = $x->count;
    }
    return $group;
}

/**
 * Get booking code by ID
 * @param int $booking_id 
 * @return string booking code
 */
function ojabooking_get_booking_code($booking_id)
{
    global $wpdb;
    $sql_query = $wpdb->prepare('SELECT code FROM ' . BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME .
        ' WHERE ID=%d', $booking_id);
    $term_id = $wpdb->get_var($sql_query);
    return $term_id;
}

/**
 * Get booking by email and term
 * @param string $email
 * @param int $term_id
 * @return object booking or null if does not exist
 */
function ojabooking_get_booking($email, $term_id)
{
    global $wpdb;
    $group_table = ojabooking_BOOKING_GROUP_TABLE_NAME;
    $booking_table = BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $query = "SELECT b.*, g.detail FROM {$booking_table} b
    LEFT JOIN (SELECT booking_id, GROUP_CONCAT(count,'x ', category) AS detail
    FROM {$group_table}
    GROUP BY booking_id) AS g ON g.booking_id = b.ID
    WHERE user_email=%s AND term_id = %d";
    $sql_query = $wpdb->prepare($query, $email, $term_id);
    $booking = $wpdb->get_row($sql_query);
    return $booking;
}

/**
 * Get booking by ID
 * @param int $booking_id 
 * @return object booking or null if does not exist
 */
function ojabooking_get_booking_by_id($booking_id)
{
    global $wpdb;
    $group_table = ojabooking_BOOKING_GROUP_TABLE_NAME;
    $booking_table = BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $query = "SELECT b.*, g.detail FROM {$booking_table} b
    LEFT JOIN (SELECT booking_id, GROUP_CONCAT(count,'x ', category) AS detail
    FROM {$group_table}
    GROUP BY booking_id) AS g ON g.booking_id = b.ID
    WHERE b.ID=%d";
    $sql_query = $wpdb->prepare($query, $booking_id);
    $booking = $wpdb->get_row($sql_query);
    return $booking;
}
/**
 * Assert if created booking is still active
 * @param int $booking_id 
 * @return (int|false) The ID of booking if is active, otherwise false.
 */
function ojabooking_is_created_booking_active($booking_id)
{
    global $wpdb;
    $sql_query = $wpdb->prepare('SELECT ID FROM ' . BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME .
        ' WHERE ID=%d AND status=%s ' . get_created_booking_time_filter(), $booking_id, 'created');
    $term_id = $wpdb->get_var($sql_query);
    return $term_id;
}

/**
 * Assert if the term contains the price category
 * @param int $term_id 
 * @param int $price_category 
 * @return bool true if the category is contained.
 */
function ojabooking_contains_term_price_category($term_id, $price_category)
{
    global $wpdb;
    $booking_table = BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $group_table = ojabooking_BOOKING_GROUP_TABLE_NAME;
    $query = "SELECT EXISTS (SELECT * FROM {$group_table} g
    LEFT JOIN {$booking_table} b ON b.ID=g.booking_id
    Where b.term_id=%d AND g.category = %d )";

    $sql_query = $wpdb->prepare($query, $term_id, $price_category);

    $term_id = $wpdb->get_var($sql_query);
    return $term_id;
}
/**
 * Update booking status by ID
 * @param int $booking_id 
 * @param string $status 
 * @return (int|false) The number of rows updated, or false on error.
 */
function ojabooking_update_booking_status($booking_id, $status)
{
    global $wpdb;
    if (!in_array($status, ojabooking_get_booking_statuses())) {
        return false;
    }

    $update_data = array('status' => $status);
    $format_data = array('%s');
    if ($status == 'canceled') {
        $update_data['user_email'] = $_SERVER['REMOTE_ADDR'];
        $update_data['code'] = '';
        $update_data['tel'] = '';
        $update_data['name'] = '';
        $format_data[] = '%s';
        $format_data[] = '%s';
        $format_data[] = '%s';
    }
    $result = $wpdb->update(BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME, $update_data, array('ID' => $booking_id), $format_data, '%d');
    return $result;
}

/**
 * Update event term language by ID
 * @param int $term_id 
 * @param string $language 
 * @return (int|false) The number of rows updated, or false on error.
 */
function ojabooking_update_term_language($term_id, $language)
{
    global $wpdb;

    $update_data = array('language' => $language);
    $format_data = array('%s');
    $result = $wpdb->update(ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME, $update_data, array('ID' => $term_id), $format_data, '%d');
    return $result;
}

/**
 * Create booking
 * @param string $user_email User email used as contact email for booking
 * @param array $group Array of types and count of people array('adult'=>2) 
 * @param int $event_id  ID of Event should be created booking for.
 * @param string $term Booking term in 'YYYY-mm-dd HH:mm:ss' format (e.g. 2000-01-01 12:00:00)
 * @param string $language 
 * @return true if booking was created successfully, else return false
 */
function ojabooking_create_booking($user_email, $name, $group, $event_id, $term, $language, $tel, $school_name_department, $class_department)
{
    global $wpdb;

    $event_term = ojabooking_get_event_term_by_datetime($event_id, $term);

    if (is_null($event_term)) {
        $term_id = ojabooking_create_event_term($event_id, $term, $language);
    } else {
        $term_id = $event_term->ID;
        if ($event_term->group_size == 0) {
            $result = ojabooking_update_term_language($term_id, $language);
        }
    }

    if ($term_id == 0) {
        return false;
    }
    if (!ojabooking_booking_can_be_created($event_id, $term_id, $group)) {
        return false;
    }

    $booking = ojabooking_get_booking($user_email, $term_id);
    if (!is_null($booking)) {
        ojabooking_send_booking_already_exists_email($booking, $term, $event_id);
        return false;
    }

    $confirmation_code = ojabooking_create_confirmation_code($user_email);

    $data = array(
        'term_id' => $term_id,
        'user_email' => $user_email,
        'code'      => $confirmation_code,
        'name'      => $name,
        'tel'       => $tel,
        'school_name_department' => $school_name_department,
        'class_department'      => $class_department
    );

    $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s');
    $wpdb->insert(BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME, $data, $format);
    $booking_id = $wpdb->insert_id;

    if (is_null($booking_id)) {
        return false;
    }

    foreach (array_keys($group, 0, true) as $key) {
        unset($group[$key]);
    }

    foreach ($group as $category => $count) {
        if ($count < 1) {
            unset($group[$category]);
            continue;
        }
        $data = array(
            'booking_id' => $booking_id,
            'category' => $category,
            'count' => $count,
        );

        $format = array('%d', '%s', '%d');
        $wpdb->insert(ojabooking_BOOKING_GROUP_TABLE_NAME, $data, $format);
        $my_id = $wpdb->insert_id;
    }

    //send email
    ojabooking_send_booking_confirmation_email($user_email, $booking_id, $event_id, $confirmation_code, $term, $group, $language);
    return true;
}

function ojabooking_booking_can_be_created(int $event_id, int $term_id, array $group)
{
    $is_periodical = ojabooking_is_event_periodical($event_id);
    if (!$is_periodical && ojabooking_is_group_private_party($group)) {
        return false;
    }
    $group_size = array_sum($group);
    $event_term = ojabooking_get_event_term_by_id($term_id);
    if ((ojabooking_is_group_private_party($group) && $event_term->group_size > 0) || $event_term->private_party > 0) return false;

    $event_group_size = get_post_meta($event_id, 'ojabooking_group_size', true);
    return $event_group_size >= ($event_term->group_size + $group_size);
}

function ojabooking_booking_can_be_created_by_term(int $event_id, string $term, int $group_size)
{
    $event_group_size = get_post_meta($event_id, 'ojabooking_group_size', true);
    $term_id = ojabooking_get_event_term_by_datetime($event_id, $term);
    if (is_null($term_id)) {
        return true;
    }
    $occupancy = ojabooking_get_event_occupancy_by_term($event_id, $term);
    return $event_group_size >= ($occupancy + $group_size);
}

function ojabooking_send_booking_confirmation_email($user_email, $booking_id, $event_id, $code, $term, $group, $language)
{
    $terms_and_conditions = get_option('ojabooking_terms_and_conditions');
    $total_price = ojabooking_get_total_price($event_id, $group);
    $ojabooking_price_category = get_post_meta($event_id, 'ojabooking_price_category', true);

    $date_format = get_option('date_format');
    $time_format = get_option('time_format');
    $term = date($date_format . " "  . $time_format, strtotime($term));
    $use_languages = get_option('ojabooking_use_booking_languages', 0);
    $language_term = get_term($language, 'ojabooking_languages');

    $confirm_link = ojabooking_create_booking_action_link($code, $booking_id, $language);
    $cancel_link = ojabooking_create_booking_action_link($code, $booking_id, $language, 'canceled');
    $booking = ojabooking_get_booking_by_id($booking_id);
    $body    = sprintf('<h1>%s</h1>', __('Booking', 'ojabooking'));

    $body    .= sprintf('<p>%s</p>', __('Please, check and confirm your booking. If you will not confirm your booking you will lose claim on your booking. ', 'ojabooking'));
    $body    .= sprintf('<h3>%s:</h3>', __('Summary', 'ojabooking'));
    $body    .= sprintf('<div><strong>%s:</strong> %s</div>', __('Booking for', 'ojabooking'), $booking->name);
    $body    .= sprintf('<div><strong>%s:</strong> %s</div>', __('Term', 'ojabooking'), $term);

    if ($use_languages) {
        $body    .= sprintf('<div><strong>%s:</strong> %s</div>', __('Language', 'ojabooking'), $language_term->name);
    }

    $body    .= sprintf('<div><strong>%s:</strong> %s</div>', __('Total price', 'ojabooking'), ojabooking_get_currency($total_price));
    $body    .= sprintf('<div><strong>%s:</strong>', __('Group', 'ojabooking'));

    $body    .= sprintf('<table border="1"><tr><th>%s</th><th>%s</th><th>%s</th></tr>', __('Participant', 'ojabooking'), __('Count', 'ojabooking'), __('Price per one', 'ojabooking'));

    foreach ($group as $participant_id => $participant_count) {
        $participant = get_term($participant_id, 'ojabooking_price_categories');

        $participants_price = $ojabooking_price_category[$participant_id];
        $body   .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>', $participant->name, $participant_count, ojabooking_get_currency($participants_price));
    }
    $body    .= sprintf('</table></div>');

    $body    .= sprintf(wp_kses(
        __('<p>Everything looks OK? Please, <a href="%1$s">Confirm your reservation</a> otherwise you can <a href="%2$s">Cancel your reservation</a> </p>', 'ojabooking'),
        array(
            'a' => array(
                'href' => array(),
                'title' => array()
            ),
            'p' => array()
        )
    ), $confirm_link, $cancel_link);
    $body    .= sprintf(wp_kses(
        __('<p>More info you can find on <a href="%1$s">%2$s</a>.</p>', 'ojabooking'),
        array(
            'a' => array(
                'href' => array(),
                'title' => array()
            ),
            'p' => array()
        )
    ), get_permalink($terms_and_conditions), get_the_title($terms_and_conditions));

    $email_sent = ojabooking_send_auto_html_email(__('booking', 'ojabooking'), $user_email, $body);
    if (!$email_sent) {
        echo 'ERROR while sending activation email';
        exit;
    }
}

function ojabooking_send_booking_already_exists_email($booking, $term, $event_id)
{
    $terms_and_conditions = get_option('ojabooking_terms_and_conditions');

    $date_format = get_option('date_format');
    $time_format = get_option('time_format');
    $term = date($date_format . " "  . $time_format, strtotime($term));
    $cancel_link = ojabooking_create_booking_action_link($booking->code, $booking->ID, 'canceled');

    $body    = sprintf('<h1>%s</h1>', __('Booking', 'ojabooking'));

    $body    .= sprintf('<p>%s</p>', __('Your booking for this term already exists. It is not possible to create 2 or more booking for single email. No changes has been done.', 'ojabooking'));
    $body    .= sprintf('<strong>%s: %s</strong>', __('Booking for', 'ojabooking'), $booking->detail);

    $body    .= sprintf(wp_kses(
        __('<p>You can <a href="%1$s">cancel your reservation</a> and after that you can create another new one.</p>', 'ojabooking'),
        array(
            'a' => array(
                'href' => array(),
                'title' => array()
            ),
            'p' => array()
        )
    ), $cancel_link);
    $body    .= sprintf(wp_kses(
        __('<p>More info you can find on <a href="%1$s">%2$s</a>.</p>', 'ojabooking'),
        array(
            'a' => array(
                'href' => array(),
                'title' => array()
            ),
            'p' => array()
        )
    ), get_permalink($terms_and_conditions), get_the_title($terms_and_conditions));

    if (!ojabooking_send_auto_html_email(__('Booking', 'ojabooking'), $booking->user_email, $body)) {
        echo 'ERROR while sending activation email';
        exit;
    }
}

function ojabooking_send_booking_canceled_email($booking)
{

    $body    = sprintf('<h1>%s</h1>', __('Booking cancellation', 'ojabooking'));

    $body    .= sprintf('<p>%s</p>', __('Your booking was cancelled.', 'ojabooking'));


    if (!ojabooking_send_auto_html_email(__('Booking cancellation', 'ojabooking'), $booking->user_email, $body)) {
        echo 'ERROR while sending activation email';
        exit;
    }
}

function ojabooking_send_auto_html_email($subject, $user_email, $body)
{
    $site_title = get_bloginfo('name');
    $admin_email = get_bloginfo('admin_email');
    $user_email = stripslashes($user_email);
    $subject  = sprintf('%s - %s', $site_title, $subject);

    $headers  = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf('From: %s <%s>', $site_title, $admin_email),
    );
    $email_sent = wp_mail($user_email, $subject, $body, $headers);
    if (!$email_sent) {
        return false;
    }
    return true;
}

function ojabooking_confirm_booking($booking_id, $key, $action, $language)
{
    $code = ojabooking_get_booking_code($booking_id);
    if (empty($code) || $code != $key) {
        return __('Booking does not exist.', 'ojabooking');
    } else if ($key == $code) {
        $is_active = ojabooking_is_created_booking_active($booking_id);
        if (!$is_active) {
            $term_event = ojabooking_get_booking_term_event($booking_id);
            $group = ojabooking_get_booking_group($booking_id);
            $can_be_created = ojabooking_booking_can_be_created($term_event->event_id, $term_event->ID, $group);
            if (!$can_be_created) {
                return __('Sorry, You are too late. Term is already full.', 'ojabooking');
            }
            $use_languages = get_option('ojabooking_use_booking_languages', 0);

            if ($use_languages && $term_event->language != $language) {
                return __('Sorry, You are too late. Term is not available in your selected language.', 'ojabooking');
            }
        }
        $booking = ojabooking_get_booking_by_id($booking_id);
        $updated = ojabooking_update_booking_status($booking_id, $action);
        if ($updated && $action == 'canceled') {
            ojabooking_send_booking_canceled_email($booking);
        }
        if ($updated) {
            return sprintf(wp_kses(__('Your booking has been %1$s.', 'ojabooking'), array()), $action);
        }
        if ($updated === 0) {
            return sprintf(wp_kses(__('Your booking is already %1$s.', 'ojabooking'), array()), $action);
        }
    }
    return __('Booking confirmation has failed', 'ojabooking');
}

function ojabooking_create_confirmation_code($salt)
{
    $salt2 = wp_generate_password();
    $code = sha1($salt2 . $salt . time());
    return $code;
}

function ojabooking_create_booking_action_link($code, $booking_id, $language = null, $action = 'confirmed')
{
    $booking_confirmation_page_id = get_option('ojabooking_booking_confirmation_page');
    $link = add_query_arg(array('key' => $code, 'booking_id' => $booking_id, 'action' => $action, 'language' => $language), get_permalink($booking_confirmation_page_id));
    return $link;
}

/**
 * Get terms
 * @return array of term objects(event_name,booking_count,group_size,accepted_booking_count,accepted_group_size)
 */
function  ojabooking_get_terms($page = 1, $search = "", $date_from = "", $date_to = "", $term_id = "", $limit = 10)
{
    $page -= 1;
    global $wpdb;
    $offset = $limit * $page;
    $search = "%{$search}%";
    $term_param = " %s ";
    if (!empty($term_id)) $term_param = " AND term.ID= %d ";

    if (empty($date_from)) $date_from = "1900-01-01 00:00:00";
    else $date_from .= " 00:00:00";
    if (empty($date_to)) $date_to = "2300-01-01 00:00:00";
    else $date_to .= " 23:59:59";
    $term_table = ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $booking_table = BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $booking_group = ojabooking_BOOKING_GROUP_TABLE_NAME;
    $query = "SELECT term.*, p.post_title AS event_name,
        c.booking_count AS booking_count,
        c.group_size AS group_size,
        b.booking_count AS accepted_booking_count,
        b.group_size AS accepted_group_size     
        FROM {$term_table} term

        LEFT JOIN {$wpdb->prefix}posts p ON p.ID=term.event_id

        LEFT JOIN (SELECT b1.term_id, COUNT(DISTINCT(b1.ID)) as booking_count, SUM(g1.count) group_size FROM {$booking_table} b1
        LEFT JOIN {$booking_group} g1 ON g1.booking_id = b1.ID
        WHERE  b1.status IN ('confirmed', 'accepted')
        GROUP BY b1.term_id) AS b ON term.id = b.term_id

        LEFT JOIN (SELECT b1.term_id, COUNT(DISTINCT(b1.ID)) as booking_count, SUM(g1.count) group_size FROM {$booking_table} b1
        LEFT JOIN {$booking_group} g1 ON g1.booking_id = b1.ID
        GROUP BY b1.term_id) AS c ON term.id = c.term_id

        WHERE (EXISTS(SELECT * FROM {$booking_table} WHERE user_email LIKE %s AND term.ID = term_id)
        OR EXISTS(SELECT * FROM {$wpdb->prefix}posts WHERE (post_title LIKE %s OR post_content LIKE %s) AND term.event_id= {$wpdb->prefix}posts.ID))
        AND term.term >= %s AND term.term <= %s
        {$term_param}
        GROUP BY term.ID
        ORDER BY term.term asc
        LIMIT %d, %d";

    $sql_query = $wpdb->prepare($query, $search, $search, $search, $date_from, $date_to, $term_id, $offset, $limit);

    $query_count = "SELECT COUNT(DISTINCT(term.ID)) FROM {$term_table} term
        
        WHERE (EXISTS(SELECT * FROM {$booking_table} bt WHERE user_email LIKE %s AND term.ID = bt.term_id)
        OR EXISTS(SELECT * FROM {$wpdb->prefix}posts WHERE (post_title LIKE %s OR post_content LIKE %s) AND term.event_id= {$wpdb->prefix}posts.ID))
        AND term.term >= %s AND term.term <= %s {$term_param}";

    $sql_query_count = $wpdb->prepare($query_count, $search, $search, $search, $date_from, $date_to, $term_id);
    $terms = $wpdb->get_results($sql_query);
    $terms_count = $wpdb->get_var($sql_query_count);
    return array('terms' => $terms, 'pages' => ceil($terms_count / $limit), 'terms_count' => $terms_count);
}

/**
 * Get terms
 * @return array of term objects(event_name,booking_count,group_size,accepted_booking_count,accepted_group_size)
 */
function ojabooking_get_terms_booking_by_date($date)
{
    global $wpdb;
    $date_from = $date . " 00:00:00";
    $date_to = $date . " 23:59:59";
    $booking_time_filter = get_created_booking_time_filter("b1");
    $term_table = ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $booking_table = BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $booking_group_table = ojabooking_BOOKING_GROUP_TABLE_NAME;
    $query = "SELECT term.*, p.post_title AS event_name,
        b.booking_count AS booking_count,
        b.group_size AS group_size,
        B.private_party AS private_party  
        FROM {$term_table} term

        LEFT JOIN {$wpdb->prefix}posts p ON p.ID=term.event_id

        LEFT JOIN (SELECT b1.term_id, COUNT(DISTINCT(b1.ID)) as booking_count, SUM(g1.count) group_size, 
            SUM(IF(g1.category IN (SELECT term_id FROM {$wpdb->prefix}termmeta m WHERE m.meta_key = 'private_party'),1,0)) > 0 AS private_party
            FROM {$booking_table} b1
            LEFT JOIN {$booking_group_table} g1 ON g1.booking_id = b1.ID
            WHERE  (b1.status IN ('confirmed', 'accepted') OR (b1.status='created' {$booking_time_filter}) )
            GROUP BY b1.term_id) 
            AS b ON term.id = b.term_id


        WHERE term.term >= %s AND term.term <= %s
        GROUP BY term.ID
        ORDER BY term.term asc";

    $sql_query = $wpdb->prepare($query, $date_from, $date_to);
    $terms = $wpdb->get_results($sql_query);
    return $terms;
}

/**
 * Get bookings
 * @return array of booking objects(id,user_email,term_id,term,status,created,group_size, event_name, event_id), pages and booking_count
 */
function ojabooking_get_bookings($page = 1, $search = "", $date_from = "", $date_to = "", $term_id = "", $status = "", $limit = 10)
{
    $page -= 1;
    global $wpdb;
    $offset = $limit * $page;
    $search = "%{$search}%";
    $term_param = " AND ''= %s ";
    if (!empty($term_id)) $term_param = " AND term_id= %d ";
    $status_param = " AND ''= %s ";
    if (!empty($status)) $status_param = " AND status= %s ";
    if (empty($date_from)) $date_from = "1900-01-01 00:00:00";
    else $date_from .= " 00:00:00";
    if (empty($date_to)) $date_to = "2300-01-01 00:00:00";
    else $date_to .= " 23:59:59";
    $term_table = ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $group_table = ojabooking_BOOKING_GROUP_TABLE_NAME;
    $booking_table = BOOKING_ojaojabooking_BOOKING_TERMS_EVENT_TABLE_NAME;
    $query = "SELECT b.id, b.name, b.user_email, b.tel, b.school_name_department, b.class_department, b.status, b.created, g.group_obj, g.group_size, b.term_id, t.term, t.event_id, p.post_title as event_name FROM {$booking_table} b
        LEFT JOIN (SELECT booking_id, JSON_OBJECTAGG(category, count) as group_obj, SUM(count) as group_size
            FROM {$group_table}
            GROUP BY booking_id) AS g ON g.booking_id = b.ID
        LEFT JOIN {$term_table} t ON t.ID = b.term_id
        LEFT JOIN {$wpdb->prefix}posts p ON p.ID=t.event_id
        WHERE (user_email LIKE %s OR p.post_title LIKE %s)
        {$term_param} {$status_param}
        AND t.term >= %s AND t.term <= %s
        ORDER BY t.term asc
        LIMIT %d, %d";

    $sql_query = $wpdb->prepare($query, $search, $search, $term_id, $status, $date_from, $date_to, $offset, $limit);
    $query_count = "SELECT COUNT(DISTINCT(b.ID)) FROM {$booking_table} b
        LEFT JOIN {$term_table} t ON t.ID = b.term_id
        LEFT JOIN {$wpdb->prefix}posts p ON p.ID=t.event_id
        WHERE (user_email LIKE %s OR p.post_title LIKE %s)
        {$term_param} {$status_param}
        AND t.term >= %s AND t.term <= %s";

    $sql_query_count = $wpdb->prepare($query_count, $search, $search, $term_id, $status, $date_from, $date_to);


    $bookings = $wpdb->get_results($sql_query);
    $booking_count = $wpdb->get_var($sql_query_count);

    $query_count = "SELECT COUNT(DISTINCT(ID)) FROM {$booking_table}";
    $sql_query_count = $wpdb->prepare($query_count);
    $booking_all_count = $wpdb->get_var($sql_query_count);
    return array(
        'bookings' => $bookings,
        'pages' => ceil($booking_count / $limit),
        'booking_count' => $booking_count,
        'booking_all_count' => $booking_all_count,
    );
}


function ojabooking_get_private_party_price_categories()
{
    $args = [
        'taxonomy'  => 'ojabooking_price_categories',
        'fields'     => 'ids',
        'hide_empty' => false,
        'meta_key'   => 'private_party',
        'meta_value' => true
    ];
    return get_terms($args);
}
/**
 * @param array $group Array of types and count of people array('adult'=>2) 
 * @return boolean True if group is private party
 */
function ojabooking_is_group_private_party(array $group)
{
    $private_party_cats = ojabooking_get_private_party_price_categories();
    //var_dump($private_party_cats);
    foreach ($private_party_cats as $cat) {
        if (array_key_exists($cat, $group) && $group[$cat] > 0) return true;
    }
    return false;
}
