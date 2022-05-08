<?php

function ojabooking_event_post_type()
{
    register_post_type(
        'ojabooking_event',
        array(
            'labels'      => array(
                'name'          => _x('Events', 'Post type General name', 'ojabooking'),
                'singular_name' => _x('Event', 'Post type Singular name', 'ojabooking'),
            ),
            'public'      => true,
            'has_archive' => true,
            'rewrite'     => array('slug' => _x('events', 'url slug', 'ojabooking')),
            'description'   => _x('Custom post type for guided tour', 'Post type description', 'ojabooking'),
            'menu_icon'     => 'dashicons-calendar-alt',
            'supports'      => array("title", "editor", "page-attributes", "thumbnail"),
            'taxonomies'    => array(),
            'show_ui'       => true,
            'show_in_menu'  => true,
            'menu_position' => 3,
            'hierarchical'   => false
        )
    );
}
add_action('init', 'ojabooking_event_post_type');

add_filter('single_template', 'ojabooking_use_single_ojabooking_event_template');

function ojabooking_use_single_ojabooking_event_template($single_template)
{
    global $post;

    if ('ojabooking_event' === $post->post_type) {
        $single_template = plugin_dir_path(__FILE__) . 'single-ojabooking_event.php';
    }

    return $single_template;
}

add_action('wp_ajax_ojabooking_get_events', 'ojabooking_get_events'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_ojabooking_get_events', 'ojabooking_get_events'); // wp_ajax_nopriv_{action}


function ojabooking_get_events()
{
    $nonce = $_POST['nextNonce'];
    if (!wp_verify_nonce($nonce, 'oja-events-next-nonce')) {
        die('Busted!');
    }
    $args = array();
    $date = $_POST['date'] ?? date("Y-m-d");
    $group = $_POST['group'];
    $language = $_POST['booking-language'];

    $group_size = isset($group) ? array_sum($group) : 0;
    if ($group_size < 1) {
        wp_send_json_error("Group is not set");
    }
    $use_languages = get_option('ojabooking_use_booking_languages', 0);

    $is_private_party = false;
    $private_party_ids = ojabooking_get_private_party_price_categories();
    foreach ($private_party_ids as $the_id) {
        if ($group[$the_id] > 0) {
            $is_private_party = true;
            break;
        }
    }
    if ($is_private_party && $group_size > 1) {
        return new WP_Error('wrong_input', __("Inputs are not correct.", "oja"));
    }

    $args['post_status'] = 'publish';
    $args['post_type'] = 'ojabooking_event';

    if ($use_languages) {
        $language_term = get_term($language, 'ojabooking_languages');
        if (!isset($language_term) || is_wp_error($language_term)) {
            wp_send_json_error("Language is not set");
        }
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'ojabooking_languages',
                'field'    => 'term_id',
                'terms'    => $language,
            ),
        );
    }

    $query = new WP_Query($args);

    $events = array();
    $output = array();

    if ($query->have_posts()) :
        $created_terms = ojabooking_get_terms_booking_by_date($date);
        while ($query->have_posts()) :
            $query->the_post();
            $id = get_the_id();
            $is_periodical = ojabooking_is_event_periodical($id);
            if (!$is_periodical && $is_private_party)
                continue; //single day event can not be booked as a private.

            if (ojabooking_can_be_shown_event($id, $date)) {
                $event_times = ojabooking_get_event_time_terms($id);
                $event_group_size = get_post_meta($id, 'ojabooking_group_size', true);

                $event_terms = array();
                //loop terms
                foreach ($event_times as $event_time) {
                    //get booking group size 
                    $event_term = $date . " " . $event_time;
                    if ($is_periodical && !ojabooking_is_periodical_event_actual($id, $event_term))
                        continue;
                    $already_existed = ojabooking_get_object_by_property_value($created_terms, 'term', $event_term . ":00");
                    $occupancy  = $already_existed == new stdClass() ? 0 : $already_existed->group_size;
                    $term_booked_as_private_party  = $already_existed == new stdClass() ? 0 : $already_existed->private_party;

                    if (($is_private_party && $occupancy > 0) || $term_booked_as_private_party) continue;


                    $term_language = ojabooking_get_existed_term_language($already_existed) ?? $language;
                    if ($already_existed)
                        if (($group_size <= $event_group_size - $occupancy) && ($term_language == $language || !isset($term_language))) {
                            $event_terms[] = array(
                                'id'        => $already_existed == new stdClass() ? null : $already_existed->ID,
                                'time'      => $event_time,
                                'occupancy' => $occupancy,
                                'term'      => $event_term,
                                'language'   => $already_existed == new stdClass() ? null : $already_existed->language,
                            );
                        }
                }
                $events[] = array(
                    'event' => get_post(),
                    'event_link' => get_permalink($id),
                    'terms' => $event_terms,
                    'max_group_size' => $event_group_size,
                    'price' => ojabooking_get_total_price($id, $group),
                    'times' => $event_times,
                );
            }
        endwhile;

        if (empty($events)) {
            $output['no_events'] = __('We are sorry, there is nothing for this date. Please, select another date.', 'ojabooking');
        }
        $output['created'] = $created_terms;
        $response = array(
            'events' => $events,
            'out' => $output,
            'date'  => $date,
        );
        if (wp_doing_ajax()) {
            wp_send_json_success($response);
        } else {
            return $response;
        }

    endif;
    if (wp_doing_ajax()) {
        wp_send_json_error($output);
    } else {
        return new WP_Error('no_data', __("There is no post found.", "oja"));
    }
}

function ojabooking_get_existed_term_language($term)
{
    if ($term == new stdClass()) return null;
    if ($term->group_size == 0) return null;
    return $term->language;
}

add_action('wp_ajax_ojabooking_create_booking', 'ojabooking_create_booking_ajax'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_ojabooking_create_booking', 'ojabooking_create_booking_ajax'); // wp_ajax_nopriv_{action}


function ojabooking_create_booking_ajax()
{
    if (!wp_doing_ajax()) {
        return new WP_Error('wrong_using', __("Method only for ajax.", "oja"));
    }
    $nonce = $_POST['bookingNonce'];
    if (!wp_verify_nonce($nonce, 'oja-create-booking-nonce')) {
        die('Busted!');
    }
    $use_languages = get_option('ojabooking_use_booking_languages', 0);

    $term = sanitize_option("date_format",$_POST['term']);
    $language = sanitize_key($_POST['booking-language']);
    $group = isset( $_POST['group'] ) ? (array) $_POST['group'] : array();
    // Any of the WordPress data sanitization functions can be used here
    $group = array_map( 'esc_attr', $group );

    $email = sanitize_email($_POST['email']);
    $name = sanitize_text_field($_POST['name']);
    $tel = sanitize_text_field($_POST['tel']);
    $school_name_department = sanitize_text_field($_POST['school_name_department']);
    $class_department = sanitize_text_field($_POST['class_department']);
    $event_id = sanitize_key($_POST['event_id']);
    $termsCheck = isset($_POST['termsCheck']);
    $privacyCheck = isset($_POST['privacyCheck']);

    $errors = array();
    if (empty($term)) {
        $errors[] = __('Term is required', 'ojabooking');
    }
    if (empty($group) && !is_array($group)) {
        $errors[] = __('Group is required', 'ojabooking');
    } elseif (array_sum($group) < 1) {
        $errors[] = __('Please, change group size. It looks like it is empty.', 'ojabooking');
    }
    if (empty($email)) {
        $errors[] = __('Email is required', 'ojabooking');
    } elseif (!is_email($email)) {
        $errors[] = __('Please, write correct email address.', 'ojabooking');
    }

    if (empty($tel)) {
        $errors[] = __('Phone number is required', 'ojabooking');
    } elseif (!ojabooking_is_phone_correct($tel)) {
        $errors[] = __('Please, write valid phone number.', 'ojabooking');
        $errors[] = $tel;
    }

    if (empty($name)) {
        $errors[] = __('Name is required', 'ojabooking');
    }
    if ($use_languages) {
        if (empty($language)) {
            $errors[] = __('Language is required', 'ojabooking');
        } else {
            $language_term = get_term($language, 'ojabooking_languages');
            if (!isset($language_term) || is_wp_error($language_term)) {
                $errors[] = __('Language has unsupported value', 'ojabooking');
            }
        }
    } else {
        $language = '';
    }
    if (!empty($group) && is_array($group) && ojabooking_is_group_private_party($group)) {
        if (empty($school_name_department)) {
            $errors[] = __('School name/Institution is required', 'ojabooking');
        }
        if (empty($class_department)) {
            $errors[] = __('Class/Department is required', 'ojabooking');
        }
    }
    if (empty($event_id)) {
        $errors[] = __('Event ID is required', 'ojabooking');
    }
    if (!$termsCheck) {
        $errors[] = __('Terms and Conditions acceptation is required', 'ojabooking');
    }
    if (!$privacyCheck) {
        $errors[] = __('Privacy Policy acceptation is required', 'ojabooking');
    }
    if (!empty($errors)) {
        wp_send_json_error($errors);
    }

    $term = date("Y-m-d H:i:s", strtotime($term));
    if (ojabooking_create_booking($email, $name, $group, $event_id, $term, $language, $tel, $school_name_department, $class_department)) {
        wp_send_json_success(__('The booking was successful. Please confirm booking in email.', 'ojabooking'));
    }
    wp_send_json_error(__('Booking could not be created.', 'ojabooking'));
}

function ojabooking_get_total_price($event_id, $group)
{
    $ojabooking_price_category = get_post_meta($event_id, 'ojabooking_price_category', true);
    $total_price = 0;
    foreach ($group as $key => $value) {
        $total_price += $ojabooking_price_category[$key] * $value;
    }
    return $total_price;
}
