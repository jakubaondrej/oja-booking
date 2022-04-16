<?php
class Oja_Booking_Language_Options_Page
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
            __('Booking Languages', 'oja'),
            __('Booking Languages', 'oja'),
            'manage_options',
            'oja_booking_language',
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
         * Renders the content of the submenu page for booking language.
         */
        wp_enqueue_script(
            'admin_booking-js',
            get_template_directory_uri() . '/assets/js/admin_booking.js',
            array('jquery')
        );
        //$language = get_option('oja_booking_language',array());
        $use_languages = get_option('oja_use_booking_languages', 0);
        $languages = get_terms(array(
            'taxonomy' => 'oja_languages',
            'hide_empty' => false,
        ));
        $default_language = get_option('oja_default_booking_language', '');
?>
        <template id="oja_booking_language_template">
            <div>
                <input type="radio" class="default-language" name="default-language" value="" title="<?php _e('Default language', 'oja'); ?>">
                <input class="booking_language_name" name="oja_booking_language[]" type="text" value="" maxlength="32">
                <button class="button remove-booking" style="margin: 0 1rem;"><?php _e('Remove', 'oja'); ?></button>
            </div>
        </template>
        <div class="wrap">
            <h1><?php _e('Booking languages', 'oja'); ?></h1>
            <?php settings_errors('oja_booking'); ?>
            <form action="" method="post">
                <h4><?php _e('Do you offer events in 2 or more languages?', 'oja'); ?></h4>

                <input type="radio" name="use_languages" value="0" id="use_languages_false" <?php checked(0, $use_languages); ?>>
                <label class="form-check-label" for="use_languages_false">
                    <?php _e('No', 'oja') ?>
                </label>

                <input type="radio" name="use_languages" value="1" id="use_languages_true" <?php checked(1, $use_languages); ?>>
                <label class="form-check-label" for="use_languages_true">
                    <?php _e('Yes', 'oja') ?>
                </label>
                <tr>
                    <th scope="row">
                        <label for="oja_booking">
                            <h4><?php _e('Default booking language', 'oja'); ?></h4>
                        </label>
                    </th>
                    <td>
                        <div id="oja_booking_languages">
                            <?php foreach ($languages as $languages) : ?>
                                <div>
                                    <input type="radio" class="default-language" name="default-language" value="<?php echo $languages->term_id; ?>" title="<?php echo $languages->name; ?>" <?php checked($default_language, $languages->term_id); ?>>
                                    <label class="booking_language_name" for="default-language"><?php echo $languages->name; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>

                <?php submit_button(__('Save', 'oja')); ?>
                <?php wp_nonce_field('oja_booking_language-save', 'oja_booking_language-save-nonce'); ?>
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

        $action       = 'oja_booking_language-save';
        $nonce        = 'oja_booking_language-save-nonce';

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
        } elseif (isset($_POST['use_languages'])) {
            $use_languages = $_POST['use_languages'];
            update_option('oja_use_booking_languages', $use_languages);

            $default_language = $_POST['default-language'];
            update_option('oja_default_booking_language', $default_language);

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
            'oja_booking_language',
            esc_attr('settings_updated'),
            $message,
            $type
        );
    }
}
if (current_user_can('manage_options')) {
    new Oja_Booking_Language_Options_Page;
}
