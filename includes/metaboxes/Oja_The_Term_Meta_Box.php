<?php
abstract class Oja_The_Term_Meta_Box
{
    /**
     * Set up and add the meta box.
     */
    public static function add()
    {
       
            $screens = ['oja_event'];
            foreach ($screens as $screen) {
                add_meta_box(
                    'oja_the_term_meta_data',          // Unique ID
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
        if (array_key_exists('oja_the_term', $_POST) && array_key_exists('oja_reservation_type', $_POST) && $_POST['oja_reservation_type']=='one_day_event') {
            update_post_meta(
                $post_id,
                'oja_the_term',
                $_POST['oja_the_term']
            );
        }
        else{
            delete_post_meta($post_id, 'oja_the_term');
        }
    }


    /**
     * Display the meta box HTML to the user.
     *
     * @param \WP_Post $post   Post object.
     */
    public static function html($post)
    {
        $oja_the_term = get_post_meta($post->ID, 'oja_the_term', true);
        ?>
           
            <input type="datetime-local" id="oja_the_term" name="oja_the_term" 
            value="<?php echo  $oja_the_term;?>">
<?php
    }
}

add_action('add_meta_boxes', ['Oja_The_Term_Meta_Box', 'add']);
add_action('save_post', ['Oja_The_Term_Meta_Box', 'save']);
