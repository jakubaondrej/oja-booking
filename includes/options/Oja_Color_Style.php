<?php
class Oja_Color_Style
{
    private static $instance = null;
    /* Saved options */
    public $options;
    /**
     * Constructor.
     */
    public static function get_instance()
    {

        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array(&$this, 'register_page_options'));
        wp_enqueue_style('wp-color-picker');
        $this->options = get_option('oja_color_style', array(
            'primary_color' => '#2707bd',
            'light_color' => '#8ed1fc',
            'secondary_color' => '#f78da7',
            'primary_hover_color' => '#bd077e',
            'body_color' => '#FFF',
        ));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_js'));
    }

    /**
     * Registers a new settings page under Settings.
     */
    function admin_menu()
    {
        add_options_page(
            __('Color style booking', 'oja'),
            __('Color style booking', 'oja'),
            'manage_options',
            'oja_color_style',
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
        settings_errors('oja_color_style');
        $this->save_options();
?>
        <div class="wrap privacy-settings-body">
            <h1><?php _e('Booking - Color settings', 'oja'); ?></h1>
            <form method="post" action="">

                <?php wp_nonce_field('oja_color_style-save', 'oja_color_style-save-nonce'); ?>
                <?php
                settings_fields(__FILE__);
                do_settings_sections(__FILE__);
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }
    /**
     * Function that will register admin page options.
     */
    public function register_page_options()
    {

        // Add Section for option fields
        add_settings_section('oja_section', 'Theme Options', array($this, 'display_section'), __FILE__); // id, title, display cb, page

        // Add Background Color Field
        add_settings_field('primary_color', 'Primary Color', array($this, 'primary_color_settings_field'), __FILE__, 'oja_section'); // id, title, display cb, page, section
        add_settings_field('light_color', 'Light Color', array($this, 'light_color_settings_field'), __FILE__, 'oja_section'); // id, title, display cb, page, section
        add_settings_field('secondary_color', 'Secondary Color', array($this, 'secondary_color_settings_field'), __FILE__, 'oja_section'); // id, title, display cb, page, section
        add_settings_field('primary_hover_color', 'Primary hover Color', array($this, 'primary_hover_color_settings_field'), __FILE__, 'oja_section'); // id, title, display cb, page, section
        add_settings_field('body_color', 'Body Color', array($this, 'body_color_settings_field'), __FILE__, 'oja_section'); // id, title, display cb, page, section
    }
    public function primary_color_settings_field()
    {
        $val = (isset($this->options['primary_color'])) ? $this->options['primary_color'] : '';
        echo '<input type="text" name="oja_color_style[primary_color]" value="' . $val . '" class="oja-color-picker" >';
    }
    public function light_color_settings_field()
    {
        $val = (isset($this->options['light_color'])) ? $this->options['light_color'] : '';
        echo '<input type="text" name="oja_color_style[light_color]" value="' . $val . '" class="oja-color-picker" >';
    }
    public function secondary_color_settings_field()
    {
        $val = (isset($this->options['secondary_color'])) ? $this->options['secondary_color'] : '';
        echo '<input type="text" name="oja_color_style[secondary_color]" value="' . $val . '" class="oja-color-picker" >';
    }
    public function primary_hover_color_settings_field()
    {
        $val = (isset($this->options['primary_hover_color'])) ? $this->options['primary_hover_color'] : '';
        echo '<input type="text" name="oja_color_style[primary_hover_color]" value="' . $val . '" class="oja-color-picker" >';
    }
    public function body_color_settings_field()
    {
        $val = (isset($this->options['body_color'])) ? $this->options['body_color'] : '';
        echo '<input type="text" name="oja_color_style[body_color]" value="' . $val . '" class="oja-color-picker" >';
    }

    /**
     * Function that will add javascript file for Color Piker.
     */
    public function enqueue_admin_js()
    {
        // Make sure to add the wp-color-picker dependecy to js file
        wp_enqueue_script('oja_custom_js', plugins_url('../../admin/js/admin_color_picker.js', __FILE__), array('jquery', 'wp-color-picker'), '', true);
    }

    /**
     * Function that will check if value is a valid HEX color.
     */
    public function check_color($value)
    {

        if (preg_match('/^#[a-f0-9]{6}$/i', $value)) { // if user insert a HEX color with #     
            return true;
        }

        return false;
    }
    public function display_section()
    { /* Leave blank */
    }

    /**
     * Save options
     */
    function save_options()
    {
        $message = null;
        $type = null;

        $action       = 'oja_color_style-save';
        $nonce        = 'oja_color_style-save-nonce';

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
        } elseif (isset($_POST['oja_color_style'])) {
            $colors = $_POST['oja_color_style'];
            if (FALSE === $this->check_color($colors['primary_color'])) {
                $message = __('Please, set valid primary color', 'oja');
                $type = 'error';
            } 
            elseif (FALSE === $this->check_color($colors['secondary_color'])) {
                $message = __('Please, set valid secondary color', 'oja');
                $type = 'error';
            }
            elseif (FALSE === $this->check_color($colors['primary_hover_color'])) {
                $message = __('Please, set valid primary hover color', 'oja');
                $type = 'error';
            }
            elseif (FALSE === $this->check_color($colors['body_color'])) {
                $message = __('Please, set valid body color', 'oja');
                $type = 'error';
            } 
            elseif (FALSE === $this->check_color($colors['light_color'])) {
                $message = __('Please, set valid light color', 'oja');
                $type = 'error';
            }
            else {
                update_option('oja_color_style', $colors);
                $this->options = $colors;
                $message = __('Successfully updated', 'oja');
                $type = 'updated';
            }
        } else {
            $message = __('Invalid request', 'oja');
            $type = 'error';
        }
        /* Here is where you update your options. Depending on what you've implemented,
	   the code may vary, but it will generally follow something like this:
	*/


        add_settings_error(
            'oja_color_style',
            esc_attr('settings_updated'),
            $message,
            $type
        );
    }
}

if (current_user_can('manage_options')) {
    new Oja_Color_Style;
}


function oja_get_booking_style()
{
    $oja_color_style = get_option('oja_color_style');
    ?>
    <style type="text/css">
        :root {
            --bs-primary: <?php echo $oja_color_style['primary_color']; ?>;
            <?php
            $hex = $oja_color_style['primary_color'];
            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            ?>--bs-primary-r: <?php echo "$r"; ?>;
            --bs-primary-g: <?php echo "$g"; ?>;
            --bs-primary-b: <?php echo "$b"; ?>;
            <?php
            $hex = $oja_color_style['primary_color'];
            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            ?>--bs-primary-hover: <?php echo $oja_color_style['primary_hover_color']; ?>;
            <?php
            $hex = $oja_color_style['primary_hover_color'];
            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            ?>--bs-primary-hover-r: <?php echo "$r"; ?>;
            --bs-primary-hover-g: <?php echo "$g"; ?>;
            --bs-primary-hover-b: <?php echo "$b"; ?>;

            <?php
            $hex = $oja_color_style['secondary_color'];
            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            ?>;
            --bs-secondary: <?php echo $oja_color_style['secondary_color']; ?>;
            --bs-secondary-hover-r: <?php echo "$r"; ?>;
            --bs-secondary-hover-g: <?php echo "$g"; ?>;
            --bs-secondary-hover-b: <?php echo "$b"; ?>;

            --bs-body-color: <?php echo $oja_color_style['body_color'];  ?>;
            --bs-light: <?php echo $oja_color_style['light_color'];  ?>;
            --bs-light-rgb: <?php
                            $hex = $oja_color_style['light_color'];
                            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
                            echo "$r, $g, $b";
                            ?>;


        }
    </style>
<?php
}
