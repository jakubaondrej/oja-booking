<?php
abstract class Oja_Date_Span_Meta_Box
{
    /**
     * Set up and add the meta box.
     */
    public static function add()
    {
       
            $screens = ['oja_event'];
            foreach ($screens as $screen) {
                add_meta_box(
                    'oja_date_span_meta_data',          // Unique ID
                    'Date span', // Box title
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
        if (array_key_exists('oja_start_term', $_POST) && array_key_exists('oja_reservation_type', $_POST) && $_POST['oja_reservation_type']=='periodical_event') {
            update_post_meta(
                $post_id,
                'oja_start_term',
                $_POST['oja_start_term']
            );
        }
        else{
            delete_post_meta($post_id, 'oja_start_term');
        }

        if (array_key_exists('oja_end_term', $_POST) && array_key_exists('oja_reservation_type', $_POST) && $_POST['oja_reservation_type']=='periodical_event') {
            update_post_meta(
                $post_id,
                'oja_end_term',
                $_POST['oja_end_term']
            );
        }
        else{
            delete_post_meta($post_id, 'oja_end_term');
        }
    }


    /**
     * Display the meta box HTML to the user.
     *
     * @param \WP_Post $post   Post object.
     */
    public static function html($post)
    {
        $oja_start_term = get_post_meta($post->ID, 'oja_start_term', true);
        $oja_end_term = get_post_meta($post->ID, 'oja_end_term', true);
        ?>
            <label for="oja_start_term"><?php _e('Start','oja');?>:</label>
            <input type="datetime-local" id="oja_start_term" name="oja_start_term" 
            value="<?php echo $oja_start_term;?>">

            <label for="oja_end_term"><?php _e('End','oja');?>:</label>
            <input type="datetime-local" id="oja_end_term" name="oja_end_term" 
            value="<?php echo  $oja_end_term;?>">
<?php
    }
}

add_action('add_meta_boxes', ['Oja_Date_Span_Meta_Box', 'add']);
add_action('save_post', ['Oja_Date_Span_Meta_Box', 'save']);
