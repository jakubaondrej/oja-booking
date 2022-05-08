<?php
abstract class Ojabooking_Reservation_Type_Meta_Box
{
    /**
     * Set up and add the meta box.
     */
    public static function add()
    {
        $screens = ['ojabooking_event'];
        foreach ($screens as $screen) {
            add_meta_box(
                'ojabooking_reservation_type_meta_data',          // Unique ID
                'Type', // Box title
                [self::class, 'html'],   // Content callback, must be of type callable
                $screen,                  // Post type
                'advanced',
                'high'
            );
        }
    }


    /**
     * Save the meta box selections.
     *
     * @param int $post_id  The post ID.
     */
    public static function save(int $post_id)
    {
        if (array_key_exists('ojabooking_reservation_type', $_POST)) {
            update_post_meta(
                $post_id,
                'ojabooking_reservation_type',
                $_POST['ojabooking_reservation_type']
            );
        }
    }


    /**
     * Display the meta box HTML to the user.
     *
     * @param \WP_Post $post   Post object.
     */
    public static function html($post)
    {
        wp_enqueue_script(
            'admin_ojabooking_edit_event-js',
            plugins_url('../../admin/js/admin_ojabooking_edit_event.js',  __FILE__),
            array('jquery')
        );
        global $wp_locale;
        $ojabooking_reservation_type = get_post_meta($post->ID, 'ojabooking_reservation_type',true);
        ?>
        <fieldset>
            <input type="radio" id="one_day_event" name="ojabooking_reservation_type" value="one_day_event" <?php checked($ojabooking_reservation_type,'one_day_event') ?>>
            <label for="one_day_event"><?php _e('One day event','ojabooking'); ?></label>
            <input type="radio" id="periodical_event" name="ojabooking_reservation_type" value="periodical_event" <?php checked($ojabooking_reservation_type,'periodical_event') ?>>
            <label for="periodical_event"><?php _e('Periodical event','ojabooking'); ?></label>
        </fieldset>
        <?php
    }
}

add_action('add_meta_boxes', ['Ojabooking_Reservation_Type_Meta_Box', 'add']);
add_action('save_post', ['Ojabooking_Reservation_Type_Meta_Box', 'save']);
