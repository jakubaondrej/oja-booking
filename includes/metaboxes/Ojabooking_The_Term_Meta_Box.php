<?php
abstract class Ojabooking_The_Term_Meta_Box
{
    /**
     * Set up and add the meta box.
     */
    public static function add()
    {
       
            $screens = ['ojabooking_event'];
            foreach ($screens as $screen) {
                add_meta_box(
                    'ojabooking_the_term_meta_data',          // Unique ID
                    'Term', // Box title
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
        if (array_key_exists('ojabooking_the_term', $_POST) && array_key_exists('ojabooking_reservation_type', $_POST) && $_POST['ojabooking_reservation_type']=='one_day_event') {
            update_post_meta(
                $post_id,
                'ojabooking_the_term',
                $_POST['ojabooking_the_term']
            );
        }
        else{
            delete_post_meta($post_id, 'ojabooking_the_term');
        }
    }


    /**
     * Display the meta box HTML to the user.
     *
     * @param \WP_Post $post   Post object.
     */
    public static function html($post)
    {
        $ojabooking_the_term = get_post_meta($post->ID, 'ojabooking_the_term', true);
        ?>
           
            <input type="datetime-local" id="ojabooking_the_term" name="ojabooking_the_term" 
            value="<?php echo  $ojabooking_the_term;?>">
<?php
    }
}

add_action('add_meta_boxes', ['Ojabooking_The_Term_Meta_Box', 'add']);
add_action('save_post', ['Ojabooking_The_Term_Meta_Box', 'save']);
