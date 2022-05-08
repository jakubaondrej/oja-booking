<?php
add_action('wp_ajax_ojabooking_send_mail', 'ojabooking_send_mail');
add_action('wp_ajax_nopriv_ojabooking_send_mail', 'ojabooking_send_mail');

function ojabooking_send_mail()
{
    if (!wp_verify_nonce($_REQUEST['nonce'], "ojabooking_send_mail_nonce")) {
        exit("No naughty business please");
    }
    $errors = array();
    if ($_REQUEST['email_address']) {
        $email_address = $_REQUEST['email_address'];
        if (!preg_match("/^[+a-zA-Z0-9\._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i", $email_address)) {
            $errors['email_address'] = __("Invalid email format", 'jakubaondrej');
        }
    } else {
        $errors['email_address'] = __("Email address is required", 'jakubaondrej');
    }
    if (isset($_POST['message'])) {
        $message = $_POST['message'];
    } else {
        $errors['message'] = __("Message is required", 'jakubaondrej');
    }

    if (!empty($errors)) {
        wp_send_json_error($errors);
    }
    $postarr = array(
        'post_title' => $email_address,
        'post_content' => $message,
        'post_type' => 'jakubaondrej_message',
    );
    $admin_email = get_option('admin_email');
    $headers[] = 'From: jakubao.eu visitor <' . $email_address . '>';

    if (wp_mail($admin_email, __('New message on your web'), $message, $headers)) {
        wp_send_json_success('<div>' . __('Message was successfully sent', 'jakubaondrej') . '</div>');
    }
	else{
		wp_send_json_success('<div>' . __('Message was successfully sent', 'jakubaondrej') . '</div>');
	}
    exit("No naughty business please");
}

/**Post type: Messages */
function ojabooking_create_jakubaondrej_message()
{
    register_post_type(
        'jakubaondrej_message',
        array(
            'labels'      => array(
                'name'          => __('Messages', 'Post type General name', 'jakubaondrej'),
                'singular_name' => __('Message', 'Post type Singular name', 'jakubaondrej'),
            ),
            'public'      => true,
            'has_archive' => true,
            'rewrite'     => array('slug' => 'message'),
            'menu_icon'     => 'dashicons-buddicons-pm',
            'supports'      => array('title', 'editor'),
            'public'        => true,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'menu_position' => 2,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export'    => true,
            'has_archive'   => true,
            'hiearchical'   => false,
            'exclude_from_search' => false,
            'show_in_rest'  => true,
            'publicly_queryable'  => true,
            'capability_type' => 'post',
        )
    );
}
add_action('init', 'ojabooking_create_jakubaondrej_message', 0);