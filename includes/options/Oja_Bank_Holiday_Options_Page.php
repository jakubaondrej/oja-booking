<?php
class Oja_Bank_Holiday_Options_Page
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
            __('Bank holidays', 'oja'),   //page title
            __('Bank holidays', 'oja'),   //menu title
            'edit_posts',   //capability
            'oja-bank-holidays', //menu_slug, 
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
        $this->oja_bank_holidays_save_options();
        wp_enqueue_script(
            'admin_bank_holidays-js',
            plugins_url('../../admin/js/admin_bank_holidays.js',  __FILE__),
            array('jquery')
        );
        
        $holidays = get_option('oja_bank_holidays');
        if(!is_array($holidays)){
            $holidays = array("");
        }
        ?>
        
        <div class="wrap">
        
            <h1><?php _e('Bank holidays', 'oja'); ?></h1>
            <?php settings_errors('oja-bank-holidays'); ?>
            <form action="" method="post">
                <tr>
                    <th scope="row">
                        <label for="oja_bank_holidays"><?php _e('Dates of Bank holidays', 'oja'); ?></label>
                    </th>
                    <td>
                        <div id="oja_bank_holidays">
                        <?php foreach ($holidays as $key => $value) : ?>
                            <div id="oja_bank_holidays_<?php echo $key; ?>_container">
                                <input type="date" id="oja_bank_holidays_<?php echo $key; ?>" name="oja_bank_holidays[]" value="<?php echo date('Y') . "-" . $value; ?>">
                                <button class="button remove-holiday" style="margin: 0 1rem;"><?php _e('Remove', 'oja'); ?></button>
                            </div>
                        <?php endforeach; ?>
                        </div>
                        <button id="add-holiday" class="button"><?php _e('Add bank holiday', 'oja'); ?></button>
                    </td>
                </tr>
        
                <?php submit_button(__('Save', 'oja')); ?>
                <?php wp_nonce_field('oja-bank_holidays-save', 'oja-bank_holidays-save-nonce'); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Save options
     */
    function oja_bank_holidays_save_options()
    {
        $message = null;
        $type = null;

        $action       = 'oja-bank_holidays-save';
        $nonce        = 'oja-bank_holidays-save-nonce';

        $is_nonce_set   = isset($_POST[$nonce]);
        $is_valid_nonce = false;

        if ($is_nonce_set) {
            $is_valid_nonce = wp_verify_nonce($_POST[$nonce], $action);
        }

        $is_nonce_ok = $is_nonce_set && $is_valid_nonce;
        if (!$is_nonce_ok) {
            return;
        }

        if (!current_user_can('manage_options')) {
            $message = __('You do not have enough permissions', 'oja');
            $type = 'error';
        } else {
            if (isset($_POST['oja_bank_holidays'])) {
                $bank_holidays = $_POST['oja_bank_holidays'];
                $formatted_days = array();
                foreach ($bank_holidays as $item) {
                    $formatted_days[] = date('m-d', strtotime($item));
                }

                //- Sanitize the code
                update_option('oja_bank_holidays', $formatted_days);
                $message = __('Successfully updated', 'oja');
                $type = 'updated';
            } else {
                $message = __('Something failed.', 'oja');
                $type = 'error';
            }
        }

        add_settings_error(
            'oja-bank-holidays',
            esc_attr('settings_updated'),
            $message,
            $type
        );
    }
}

if (current_user_can('manage_options')) {
    new Oja_Bank_Holiday_Options_Page;
}

register_activation_hook(__FILE__, 'oja_add_oja_bank_holidays_default_options');
function oja_add_oja_bank_holidays_default_options()
{
    if (FALSE === get_option('oja_bank_holidays'))
        add_option('oja_bank_holidays', '');
}
