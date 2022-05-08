<?php
class Ojabooking_Price_Categories_Options_Page
{

    /**
     * Constructor.
     */
    function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    /**
     * Registers a new settings page under Settings.
     */
    function admin_menu()
    {
        add_options_page(
            __('Price Categories', 'ojabooking'),
            __('Price Categories', 'ojabooking'),
            'manage_options',
            'ojabooking_price_categories',
            array(
                $this,
                'settings_page'
            )
        );
    }

    /**
     * Settings page display callback.
     */
    function settings_page()
    {
        $this->ojabooking_booking_save_options();
       
/**
 * Renders the content of the submenu page for booking categories.
 */
wp_enqueue_script(
    'admin_booking-js',
    plugins_url('private/js/admin_booking.js',  __FILE__),
    array('jquery')
);
$currency_symbol = get_option('ojabooking_current_currency');
$categories = get_terms(array(
    'taxonomy' => 'ojabooking_price_categories',
    'hide_empty' => false,
));
$default_category = get_option('ojabooking_default_price_category','');

?>

<div class="wrap">
    <h1><?php _e('Price Categories', 'ojabooking'); ?></h1>
    <?php settings_errors('ojabooking_booking'); ?>
    <form action="" method="post">
        <tr>
            <th scope="row">
                <label for="ojabooking_booking"><h4><?php _e('Select default category', 'ojabooking'); ?></h4></label>
            </th>
            <td>
                <div id="ojabooking_booking_categories">
                    <?php foreach ($categories as $category) : ?>
                        <div>
                            <input type="radio" class="default-category" id="default-category_<?php echo $category->term_id; ?>" name="default-category" value="<?php echo esc_attr($category->term_id); ?>" title="<?php echo esc_attr($category->name); ?>" <?php checked($default_category, $category->term_id);?>>
                            <label class="category_name" for="default-category_<?php echo $category->term_id; ?>"><?php echo $category->name; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>

        <?php submit_button(__('Save', 'ojabooking')); ?>
        <?php wp_nonce_field('ojabooking_booking_categories-save', 'ojabooking_booking_categories-save-nonce'); ?>
    </form>
</div>
<?php
    }

    /**
     * Save options
     */
    function ojabooking_booking_save_options()
    {
        $message = null;
        $type = null;

        $action       = 'ojabooking_booking_categories-save';
        $nonce        = 'ojabooking_booking_categories-save-nonce';

        $is_nonce_set   = isset($_POST[$nonce]);
        $is_valid_nonce = false;

        if ($is_nonce_set) {
            $is_valid_nonce = wp_verify_nonce($_POST[$nonce], $action);
        } else {
            return;
        }

        $is_nonce_ok = $is_nonce_set && $is_valid_nonce;
        if (!$is_nonce_ok) {
            return;
            $message = __('Sorry, your data could not be saved', 'ojabooking');
            $type = 'error';
        } elseif (isset($_POST['default-category'])) {
            $default_price_category = $_POST['default-category'];
            //- Sanitize the code
            update_option('ojabooking_default_price_category', $default_price_category);
            $message = __('Successfully updated', 'ojabooking');
            $type = 'updated';
        } else {
            $message = __('Invalid request', 'ojabooking');
            $type = 'error';
        }
        /* Here is where you update your options. Depending on what you've implemented,
	   the code may vary, but it will generally follow something like this:
	*/
        add_settings_error(
            'ojabooking_booking_categories',
            esc_attr('settings_updated'),
            $message,
            $type
        );
    }
}
if (current_user_can('manage_options')) {
    new Ojabooking_Price_Categories_Options_Page;
}