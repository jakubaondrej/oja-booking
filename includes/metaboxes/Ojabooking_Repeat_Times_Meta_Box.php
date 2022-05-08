<?php
abstract class Ojabooking_Repeat_Times_Meta_Box
{
    /**
     * Set up and add the meta box.
     */
    public static function add()
    {
       
            $screens = ['ojabooking_event'];
            foreach ($screens as $screen) {
                add_meta_box(
                    'ojabooking_repeat_times_meta_data',          // Unique ID
                    'Repeat - times', // Box title
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
        if (array_key_exists('ojabooking_repeat_times', $_POST)  && array_key_exists('ojabooking_reservation_type', $_POST) && $_POST['ojabooking_reservation_type']=='periodical_event') {
            update_post_meta(
                $post_id,
                'ojabooking_repeat_times',
                $_POST['ojabooking_repeat_times']
            );
        }else{
            delete_post_meta($post_id, 'ojabooking_repeat_times');
        }
    }


    /**
     * Display the meta box HTML to the user.
     *
     * @param \WP_Post $post   Post object.
     */
    public static function html($post)
    {
        $ojabooking_repeat_times = get_post_meta($post->ID, 'ojabooking_repeat_times', true);
        if (!is_array($ojabooking_repeat_times)) {
            $ojabooking_repeat_times = array("");
        }
?>
        <div id="ojabooking_repeat_times">
            <?php
            foreach ($ojabooking_repeat_times as $key => $value) {
            ?>
                <div id="ojabooking_repeat_times_<?php echo $key; ?>_container">
                    <input type="time" value="<?php echo $value; ?>" id="ojabooking_repeat_times_<?php echo $i; ?>" name="ojabooking_repeat_times[]">
                    <span class="button remove-ojabooking_repeat_times" style="margin: 0 1rem;"><?php _e('Remove', 'ojabooking'); ?></span>
                </div>
            <?php
            }
            ?>
        </div>
        <span id="add-ojabooking_repeat_times" class="button"><?php _e('Add a next time', 'ojabooking'); ?></span>
<?php
    }
}

add_action('add_meta_boxes', ['Ojabooking_Repeat_Times_Meta_Box', 'add']);
add_action('save_post', ['Ojabooking_Repeat_Times_Meta_Box', 'save']);
