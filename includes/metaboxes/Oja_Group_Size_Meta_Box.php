<?php
abstract class Oja_Group_Size_Meta_Box
{
    /**
     * Set up and add the meta box.
     */
    public static function add()
    {
        $screens = ['oja_event'];
        foreach ($screens as $screen) {
            add_meta_box(
                'oja_group_size_meta_data',          // Unique ID
                'Maximum group size', // Box title
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
        if (array_key_exists('oja_group_size', $_POST)) {
            update_post_meta(
                $post_id,
                'oja_group_size',
                $_POST['oja_group_size']
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
        $oja_group_size = get_post_meta($post->ID, 'oja_group_size', true);
?>
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("What's the max group size?", 'oja'); ?></span></legend>
            
            <input type="number" value="<?php echo $oja_group_size; ?>" id="oja_group_size" name="oja_group_size"> 
        </fieldset>
<?php
    }
}

add_action('add_meta_boxes', ['Oja_Group_Size_Meta_Box', 'add']);
add_action('save_post', ['Oja_Group_Size_Meta_Box', 'save']);
