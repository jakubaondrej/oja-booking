<?php
abstract class Ojabooking_Repeat_Days_Meta_Box
{
    /**
     * Set up and add the meta box.
     */
    public static function add()
    {
        $screens = ['ojabooking_event'];
        foreach ($screens as $screen) {
            add_meta_box(
                'ojabooking_repeat_days_meta_data',          // Unique ID
                'Repeat - days', // Box title
                [self::class, 'html'],   // Content callback, must be of type callable
                $screen                  // Post type
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
        if (array_key_exists('ojabooking_repeat_days', $_POST) && array_key_exists('ojabooking_reservation_type', $_POST) && $_POST['ojabooking_reservation_type']=='periodical_event') {
            update_post_meta(
                $post_id,
                'ojabooking_repeat_days',
                $_POST['ojabooking_repeat_days']
            );
        }else{
            delete_post_meta($post_id, 'ojabooking_repeat_days');
        }
    }


    /**
     * Display the meta box HTML to the user.
     *
     * @param \WP_Post $post   Post object.
     */
    public static function html($post)
    {
        global $wp_locale;
        $ojabooking_repeat_days = get_post_meta($post->ID, 'ojabooking_repeat_days',true);
        if (!is_array($ojabooking_repeat_days)) {
            $ojabooking_repeat_days = array();
        }
        ?>
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Repeat', 'oja'); ?></span></legend>
            <?php
            for ($i = 0; $i < 7; $i++) {
                $day_name = $wp_locale->get_weekday($i);
            ?>
                <label for="ojabooking_repeat_days[]">
                    <input type="checkbox" <?php checked(in_array($i, $ojabooking_repeat_days)); ?> value="<?php echo $i; ?>" id="selected_day_<?php echo $i; ?>" name="ojabooking_repeat_days[]"> <?php echo $day_name; ?>
                </label>
                <br>
            <?php
            }
            ?>
            <label for="ojabooking_repeat_days[]">
                <input type="checkbox" <?php checked(in_array(8, $ojabooking_repeat_days)); ?> value="8" id="selected_day_8" name="ojabooking_repeat_days[]"> <?php _e('Bank holiday', 'oja'); ?>
            </label>
        </fieldset>
    <?php
    }
}

add_action('add_meta_boxes', ['Ojabooking_Repeat_Days_Meta_Box', 'add']);
add_action('save_post', ['Ojabooking_Repeat_Days_Meta_Box', 'save']);
