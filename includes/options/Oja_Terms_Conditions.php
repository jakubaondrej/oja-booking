<?php
class Oja_Terms_Conditions
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
            __('Terms and Conditions', 'oja'),
            __('Terms and Conditions', 'oja'),
            'manage_options',
            'oja_terms_conditions',
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

        $pages = get_pages();
        $oja_terms_and_conditions_page = get_option('oja_terms_and_conditions', '');

?>

        <div class="wrap privacy-settings-body">
            <h1><?php _e('Terms and Conditions', 'oja'); ?></h1>

            <table class="form-table tools-privacy-policy-page" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="oja_booking">
                                <?php _e('Create new Terms and Conditions page', 'oja'); ?>
                            </label>
                        </th>
                        <td>
                            <div id="oja_terms_conditions">
                                <form action="" method="post">
                                    <?php submit_button(
                                        __('Create', 'oja'),
                                        'primary',
                                        'submit',
                                        true,
                                        array(
                                            'value' => 'new'
                                        )
                                    ); ?>
                                    <?php wp_nonce_field('oja_terms_conditions-save', 'oja_terms_conditions-save-nonce'); ?>
                                </form>

                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="oja_booking">
                                <?php _e('Use already existing page', 'oja'); ?>
                            </label>
                        </th>
                        <td>
                            <form action="" method="post">
                                <select name="oja_terms_conditions_page">
                                    <?php foreach ($pages as $page) : ?>
                                        <option value="<?php echo $page->ID; ?>" <?php selected($oja_terms_and_conditions_page, $page->ID); ?>><?php echo $page->post_title; ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <?php submit_button(__('Use this page', 'oja'),'primary large', 'submit', false ); ?>
                                <?php wp_nonce_field('oja_terms_conditions-save', 'oja_terms_conditions-save-nonce'); ?>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php settings_errors('oja_booking'); ?>


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

        $action       = 'oja_terms_conditions-save';
        $nonce        = 'oja_terms_conditions-save-nonce';

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
        }elseif (isset($_POST['submit']) && $_POST['submit'] == 'Create') {
            $new_page_id = oja_create_page_if_not_exists('Terms and Conditions');
            update_option('oja_terms_and_conditions', $new_page_id);
            $message = __('Page was created.', 'oja');
            $type = 'updated';
        }  elseif (isset($_POST['oja_terms_conditions_page'])) {
            $oja_terms_conditions_page = $_POST['doja_terms_conditions_page'];
            //- Sanitize the code
            update_option('oja_terms_and_conditions', $oja_terms_conditions_page);
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
            'oja_terms_conditions',
            esc_attr('settings_updated'),
            $message,
            $type
        );
    }
}
if (current_user_can('manage_options')) {
    new Oja_Terms_Conditions;
}
