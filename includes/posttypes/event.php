<?php

function oja_event_post_type()
{
    register_post_type(
        'oja_event',
        array(
            'labels'      => array(
                'name'          => _x('Events', 'Post type General name', 'oja'),
                'singular_name' => _x('Event', 'Post type Singular name', 'oja'),
            ),
            'public'      => true,
            'has_archive' => true,
            'rewrite'     => array('slug' => _x('events', 'url slug', 'oja')),
            'description'   => _x('Custom post type for guided tour', 'Post type description', 'oja'),
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
add_action('init', 'oja_event_post_type');

add_filter('single_template', 'oja_use_single_oja_event_template');

function oja_use_single_oja_event_template($single_template)
{
    global $post;

    if ('oja_event' === $post->post_type) {
        $single_template = plugin_dir_path(__FILE__) . 'single-oja_event.php';
    }

    return $single_template;
}

add_action('wp_ajax_oja_get_events', 'oja_get_events'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_oja_get_events', 'oja_get_events'); // wp_ajax_nopriv_{action}


function oja_get_events()
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
    $use_languages = get_option('oja_use_booking_languages', 0);

    $is_private_party = false;
    $private_party_ids = oja_get_private_party_price_categories();
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
    $args['post_type'] = 'oja_event';

    if ($use_languages) {
        $language_term = get_term($language, 'oja_languages');
        if (!isset($language_term) || is_wp_error($language_term)) {
            wp_send_json_error("Language is not set");
        }
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'oja_languages',
                'field'    => 'term_id',
                'terms'    => $language,
            ),
        );
    }

    $query = new WP_Query($args);

    $events = array();
    $output = array();

    if ($query->have_posts()) :
        $created_terms = oja_get_terms_booking_by_date($date);
        while ($query->have_posts()) :
            $query->the_post();
            $id = get_the_id();
            $is_periodical = oja_is_event_periodical($id);
            if (!$is_periodical && $is_private_party)
                continue; //single day event can not be booked as a private.

            if (oja_can_be_shown_event($id, $date)) {
                $event_times = oja_get_event_time_terms($id);
                $event_group_size = get_post_meta($id, 'oja_group_size', true);

                $event_terms = array();
                //loop terms
                foreach ($event_times as $event_time) {
                    //get booking group size 
                    $event_term = $date . " " . $event_time;
                    if ($is_periodical && !oja_is_periodical_event_actual($id, $event_term))
                        continue;

                    $already_existed = oja_get_object_by_property_value($created_terms, 'term', $event_term . ":00");
                    $occupancy  = $already_existed == new stdClass() ? 0 : $already_existed->group_size;
                    $term_booked_as_private_party  = $already_existed == new stdClass() ? 0 : $already_existed->private_party;

                    if (($is_private_party && $occupancy > 0) || $term_booked_as_private_party) continue;


                    $term_language = oja_get_existed_term_language($already_existed) ?? $language;

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
                    'price' => oja_get_total_price($id, $group),
                );
            }
        endwhile;

        if (empty($events)) {
            $output['no_events'] = __('We are sorry, there is nothing for this date. Please, select another date.', 'oja');
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

function oja_get_existed_term_language($term)
{
    if ($term == new stdClass()) return null;
    if ($term->group_size == 0) return null;
    return $term->language;
}

add_action('wp_ajax_oja_create_booking', 'oja_create_booking_ajax'); // wp_ajax_{action}
add_action('wp_ajax_nopriv_oja_create_booking', 'oja_create_booking_ajax'); // wp_ajax_nopriv_{action}


function oja_create_booking_ajax()
{
    if (!wp_doing_ajax()) {
        return new WP_Error('wrong_using', __("Method only for ajax.", "oja"));
    }
    $nonce = $_POST['bookingNonce'];
    if (!wp_verify_nonce($nonce, 'oja-create-booking-nonce')) {
        die('Busted!');
    }
    $use_languages = get_option('oja_use_booking_languages', 0);

    $term = $_POST['term'];
    $language = $_POST['booking-language'];
    $group = $_POST['group'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $event_id = $_POST['event_id'];
    $termsCheck = $_POST['termsCheck'];
    $privacyCheck = $_POST['privacyCheck'];

    $errors = array();
    if (empty($term)) {
        $errors[] = __('Term is required', 'oja');
    }
    if (empty($group) && !is_array($group)) {
        $errors[] = __('Group is required', 'oja');
    } elseif (array_sum($group) < 1) {
        $errors[] = __('Please, change group size. It looks like it is empty.', 'oja');
    }
    if (empty($email)) {
        $errors[] = __('Email is required', 'oja');
    } elseif (!is_email($email)) {
        $errors[] = __('Please, write correct email address.', 'oja');
    }
    if (empty($name)) {
        $errors[] = __('Name is required', 'oja');
    }
    if ($use_languages) {
        if (empty($language)) {
            $errors[] = __('Language is required', 'oja');
        } else {
            $language_term = get_term($language, 'oja_languages');
            if (!isset($language_term) || is_wp_error($language_term)) {
                $errors[] = __('Language has unsupported value', 'oja');
            }
        }
    }else{
        $language='';
    }

    if (empty($event_id)) {
        $errors[] = __('Event ID is required', 'oja');
    }
    if (!isset($termsCheck)) {
        $errors[] = __('Terms and Conditions acceptation is required', 'oja');
    }
    if (!isset($privacyCheck)) {
        $errors[] = __('Privacy Policy acceptation is required', 'oja');
    }
    if (!empty($errors)) {
        wp_send_json_error($errors);
    }
    $term = date("Y-m-d H:i:s", strtotime($term));
    if (oja_create_booking($email, $name, $group, $event_id, $term, $language)) {
        wp_send_json_success(__('The booking was successful. Please confirm booking in email.', 'oja'));
    }
    wp_send_json_error(__('Booking could not be created.', 'oja'));
}

function oja_get_total_price($event_id, $group)
{
    $oja_price_category = get_post_meta($event_id, 'oja_price_category', true);
    $total_price = 0;
    foreach ($group as $key => $value) {
        $total_price += $oja_price_category[$key] * $value;
    }
    return $total_price;
}
