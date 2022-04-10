<?php
class Oja_Price_Categories_Options_Page
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
            __('Price Categories', 'oja'),
            __('Price Categories', 'oja'),
            'manage_options',
            'oja_price_categories',
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
        $this->oja_booking_save_options();
       
/**
 * Renders the content of the submenu page for booking categories.
 */
wp_enqueue_script(
    'admin_booking-js',
    plugins_url('private/js/admin_booking.js',  __FILE__),
    array('jquery')
);
$currency_symbol = get_option('oja_current_currency');
$categories = get_terms(array(
    'taxonomy' => 'oja_price_categories',
    'hide_empty' => false,
));
$default_category = get_option('oja_default_price_category','');

?>

<div class="wrap">
    <h1><?php _e('Price Categories', 'oja'); ?></h1>
    <?php settings_errors('oja_booking'); ?>
    <form action="" method="post">
        <tr>
            <th scope="row">
                <label for="oja_booking"><h4><?php _e('Select default category', 'oja'); ?></h4></label>
            </th>
            <td>
                <div id="oja_booking_categories">
                    <?php foreach ($categories as $category) : ?>
                        <div>
                            <input type="radio" class="default-category" id="default-category_<?php echo $category->term_id; ?>" name="default-category" value="<?php echo $category->term_id; ?>" title="<?php echo $category->name; ?>" <?php checked($default_category, $category->term_id);?>>
                            <label class="category_name" for="default-category_<?php echo $category->term_id; ?>"><?php echo $category->name; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>

        <?php submit_button(__('Save', 'oja')); ?>
        <?php wp_nonce_field('oja_booking_categories-save', 'oja_booking_categories-save-nonce'); ?>
    </form>
</div>
<?php
    }

    /**
     * Save options
     */
    function oja_booking_save_options()
    {
        $message = null;
        $type = null;

        $action       = 'oja_booking_categories-save';
        $nonce        = 'oja_booking_categories-save-nonce';

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
            $message = __('Sorry, your data could not be saved', 'oja');
            $type = 'error';
        } elseif (isset($_POST['default-category'])) {
            $default_price_category = $_POST['default-category'];
            //- Sanitize the code
            update_option('oja_default_price_category', $default_price_category);
            $message = __('Successfully updated', 'oja');
            $type = 'updated';
        } else {
            $message = __('Invalid request', 'oja');
            $type = 'error';
        }
        /* Here is where you update your options. Depending on what you've implemented,
	   the code may vary, but it will generally follow something like this:
	*/
        add_settings_error(
            'oja_booking_categories',
            esc_attr('settings_updated'),
            $message,
            $type
        );
    }
}
if (current_user_can('manage_options')) {
    new Oja_Price_Categories_Options_Page;
}