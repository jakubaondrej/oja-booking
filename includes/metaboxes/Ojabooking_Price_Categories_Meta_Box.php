<?php
abstract class Ojabooking_Price_Categories_Meta_Box
{
    /**
     * Set up and add the meta box.
     */
    public static function add()
    {
        $screens = ['ojabooking_event'];
        foreach ($screens as $screen) {
            add_meta_box(
                'ojabooking_price_category_meta_data',          // Unique ID
                'Price', // Box title
                [self::class, 'html'],   // Content callback, must be of type callable
                $screen,                  // Post type
                'advanced',
                'low'
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
        if (array_key_exists('ojabooking_price_category', $_POST)) {
            update_post_meta(
                $post_id,
                'ojabooking_price_category',
                $_POST['ojabooking_price_category']
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
        $categories = get_terms(array(
            'taxonomy' => 'ojabooking_price_categories',
            'hide_empty' => false,
        ));
        $ojabooking_price_category = get_post_meta($post->ID, 'ojabooking_price_category',true);
        $currency_symbol = get_option('ojabooking_current_currency');
?>
        <fieldset>
            <legend class="screen-reader-text">Set price</legend>
            <?php foreach ($categories as $category) : ?>
                <div>
                    <label for="ojabooking_price_category_<?php echo $category->term_id; ?>"><?php echo $category->name; ?></label>
                    <input type="number" step="0.01" min="0" max="9999999999" value="<?php echo $ojabooking_price_category[$category->term_id]??0; ?>" id="ojabooking_price_category_<?php echo $category->term_id; ?>" name="ojabooking_price_category[<?php echo $category->term_id; ?>]">
                    <span><?php echo $currency_symbol; ?></span>
                </div>

            <?php endforeach; ?>
        </fieldset>
<?php
    }
}

add_action('add_meta_boxes', ['Ojabooking_Price_Categories_Meta_Box', 'add']);
add_action('save_post', ['Ojabooking_Price_Categories_Meta_Box', 'save']);
