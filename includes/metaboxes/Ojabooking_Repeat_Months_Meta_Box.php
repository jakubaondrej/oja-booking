<?php
abstract class Ojabooking_Repeat_Months_Meta_Box
{
    /**
     * Set up and add the meta box.
     */
    public static function add()
    {
        $screens = ['ojabooking_event'];
        foreach ($screens as $screen) {
            add_meta_box(
                'ojabooking_repeat_months_meta_data',          // Unique ID
                'Repeat - Months', // Box title
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
        if (array_key_exists('ojabooking_repeat_months', $_POST) && array_key_exists('ojabooking_reservation_type', $_POST) && $_POST['ojabooking_reservation_type']=='periodical_event') { 
            update_post_meta(
                $post_id,
                'ojabooking_repeat_months',
                $_POST['ojabooking_repeat_months']
            );
        }else{
            delete_post_meta($post_id, 'ojabooking_repeat_months');
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
        $ojabooking_repeat_months = get_post_meta($post->ID, 'ojabooking_repeat_months', true);
        if (!is_array($ojabooking_repeat_months)) {
            $ojabooking_repeat_months = array();
        }
        ?>
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Repeat', 'ojabooking'); ?></span></legend>
            <?php
            for ($i = 1; $i < 13; $i++) {
                $month_name = $wp_locale->get_month($i);
            ?>
                <label for="ojabooking_repeat_months[]">
                    <input type="checkbox" <?php checked(in_array($i, $ojabooking_repeat_months)); ?> value="<?php echo $i; ?>" id="selected_day_<?php echo $i; ?>" name="ojabooking_repeat_months[]"> <?php echo $month_name; ?>
                </label>
                <br>
            <?php
            }
            ?>
        </fieldset>
    <?php
    }
}

add_action('add_meta_boxes', ['Ojabooking_Repeat_Months_Meta_Box', 'add']);
add_action('save_post', ['Ojabooking_Repeat_Months_Meta_Box', 'save']);
