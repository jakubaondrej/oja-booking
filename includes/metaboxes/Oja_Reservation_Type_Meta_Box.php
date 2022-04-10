<?php
abstract class Oja_Reservation_Type_Meta_Box
{
    /**
     * Set up and add the meta box.
     */
    public static function add()
    {
        $screens = ['oja_event'];
        foreach ($screens as $screen) {
            add_meta_box(
                'oja_reservation_type_meta_data',          // Unique ID
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
        if (array_key_exists('oja_reservation_type', $_POST)) {
            update_post_meta(
                $post_id,
                'oja_reservation_type',
                $_POST['oja_reservation_type']
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
            'admin_oja_edit_event-js',
            plugins_url('../../admin/js/admin_oja_edit_event.js',  __FILE__),
            array('jquery')
        );
        global $wp_locale;
        $oja_reservation_type = get_post_meta($post->ID, 'oja_reservation_type',true);
        ?>
        <fieldset>
            <input type="radio" id="one_day_event" name="oja_reservation_type" value="one_day_event" <?php checked($oja_reservation_type,'one_day_event') ?>>
            <label for="one_day_event"><?php _e('One day event','oja'); ?></label>
            <input type="radio" id="periodical_event" name="oja_reservation_type" value="periodical_event" <?php checked($oja_reservation_type,'periodical_event') ?>>
            <label for="periodical_event"><?php _e('Periodical event','oja'); ?></label>
        </fieldset>
        <?php
    }
}

add_action('add_meta_boxes', ['Oja_Reservation_Type_Meta_Box', 'add']);
add_action('save_post', ['Oja_Reservation_Type_Meta_Box', 'save']);
