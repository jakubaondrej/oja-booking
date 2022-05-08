<?php
class Ojabooking_Currency_Options_Page
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
            __('Currency', 'oja'),
            __('Currency', 'oja'),
            'manage_options',
            'ojabooking_currency',
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
        $this->ojabooking_currency_save_options();

        /**
         * Renders the content of the submenu page for currency categories.
         */
        $currency_list = ojabooking_get_curencies();

        $current_currency = get_option('ojabooking_current_currency');
?>
        <div class="wrap">
            <h1><?php _e('Currency', 'oja'); ?></h1>
            <?php settings_errors('ojabooking_currency'); ?>

            <form action="" method="post">
                <tr>
                    <th scope="row">
                        <label for="ojabooking_currency">
                            <h4><?php _e('Select currency', 'oja'); ?></h4>
                        </label>
                    </th>
                    <td>
                        <div id="ojabooking_currency_selection">
                            <?php foreach ($currency_list as $key => $value) : ?>
                                <div>
                                    <input type="radio" id="<?php echo esc_attr($key); ?>" name="ojabooking_current_currency" value='<?php echo esc_attr($key); ?>' <?php checked($key == $current_currency); ?>>
                                    <label for="<?php echo esc_attr($key); ?>"><?php echo $key . " - " . $value[0]; ?></label>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    </td>

                </tr>

                <?php submit_button(__('Save', 'oja')); ?>
                <?php wp_nonce_field('oja-currency-save', 'oja-currency-save-nonce'); ?>
            </form>
        </div>
<?php
    }

    /**
     * Save options
     */
    function ojabooking_currency_save_options()
    {
        $message = null;
        $type = null;

        $action       = 'oja-currency-save';
        $nonce        = 'oja-currency-save-nonce';

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
        } elseif (isset($_POST['ojabooking_current_currency'])) {
            $current_currency = $_POST['ojabooking_current_currency'];

            update_option('ojabooking_current_currency', $current_currency);
            $message = __('Successfully updated', 'oja');
            $type = 'success';
        } else {
            $message = __('Invalid request', 'oja');
            $type = 'error';
        }

        add_settings_error(
            'ojabooking_currency',
            esc_attr('settings_updated'),
            $message,
            $type
        );
    }
}
if (current_user_can('manage_options')) {
    new Ojabooking_Currency_Options_Page;
}

function ojabooking_get_curencies()
{
    return array(
        'AFA' => array('Afghan Afghani', '971'),
        'AWG' => array('Aruban Florin', '533'),
        'AUD' => array('Australian Dollars', '036'),
        'ARS' => array('Argentine Pes', '032'),
        'AZN' => array('Azerbaijanian Manat', '944'),
        'BSD' => array('Bahamian Dollar', '044'),
        'BDT' => array('Bangladeshi Taka', '050'),
        'BBD' => array('Barbados Dollar', '052'),
        'BYR' => array('Belarussian Rouble', '974'),
        'BOB' => array('Bolivian Boliviano', '068'),
        'BRL' => array('Brazilian Real', '986'),
        'GBP' => array('British Pounds Sterling', '826'),
        'BGN' => array('Bulgarian Lev', '975'),
        'KHR' => array('Cambodia Riel', '116'),
        'CAD' => array('Canadian Dollars', '124'),
        'KYD' => array('Cayman Islands Dollar', '136'),
        'CLP' => array('Chilean Peso', '152'),
        'CNY' => array('Chinese Renminbi Yuan', '156'),
        'COP' => array('Colombian Peso', '170'),
        'CRC' => array('Costa Rican Colon', '188'),
        'HRK' => array('Croatia Kuna', '191'),
        'CPY' => array('Cypriot Pounds', '196'),
        'CZK' => array('Czech Koruna', '203'),
        'DKK' => array('Danish Krone', '208'),
        'DOP' => array('Dominican Republic Peso', '214'),
        'XCD' => array('East Caribbean Dollar', '951'),
        'EGP' => array('Egyptian Pound', '818'),
        'ERN' => array('Eritrean Nakfa', '232'),
        'EEK' => array('Estonia Kroon', '233'),
        'EUR' => array('Euro', '978'),
        'GEL' => array('Georgian Lari', '981'),
        'GHC' => array('Ghana Cedi', '288'),
        'GIP' => array('Gibraltar Pound', '292'),
        'GTQ' => array('Guatemala Quetzal', '320'),
        'HNL' => array('Honduras Lempira', '340'),
        'HKD' => array('Hong Kong Dollars', '344'),
        'HUF' => array('Hungary Forint', '348'),
        'ISK' => array('Icelandic Krona', '352'),
        'INR' => array('Indian Rupee', '356'),
        'IDR' => array('Indonesia Rupiah', '360'),
        'ILS' => array('Israel Shekel', '376'),
        'JMD' => array('Jamaican Dollar', '388'),
        'JPY' => array('Japanese yen', '392'),
        'KZT' => array('Kazakhstan Tenge', '368'),
        'KES' => array('Kenyan Shilling', '404'),
        'KWD' => array('Kuwaiti Dinar', '414'),
        'LVL' => array('Latvia Lat', '428'),
        'LBP' => array('Lebanese Pound', '422'),
        'LTL' => array('Lithuania Litas', '440'),
        'MOP' => array('Macau Pataca', '446'),
        'MKD' => array('Macedonian Denar', '807'),
        'MGA' => array('Malagascy Ariary', '969'),
        'MYR' => array('Malaysian Ringgit', '458'),
        'MTL' => array('Maltese Lira', '470'),
        'BAM' => array('Marka', '977'),
        'MUR' => array('Mauritius Rupee', '480'),
        'MXN' => array('Mexican Pesos', '484'),
        'MZM' => array('Mozambique Metical', '508'),
        'NPR' => array('Nepalese Rupee', '524'),
        'ANG' => array('Netherlands Antilles Guilder', '532'),
        'TWD' => array('New Taiwanese Dollars', '901'),
        'NZD' => array('New Zealand Dollars', '554'),
        'NIO' => array('Nicaragua Cordoba', '558'),
        'NGN' => array('Nigeria Naira', '566'),
        'KPW' => array('North Korean Won', '408'),
        'NOK' => array('Norwegian Krone', '578'),
        'OMR' => array('Omani Riyal', '512'),
        'PLN' => array('Poland złoty', '985'),
        'PKR' => array('Pakistani Rupee', '586'),
        'PYG' => array('Paraguay Guarani', '600'),
        'PEN' => array('Peru New Sol', '604'),
        'PHP' => array('Philippine Pesos', '608'),
        'QAR' => array('Qatari Riyal', '634'),
        'RON' => array('Romanian New Leu', '946'),
        'RUB' => array('Russian Federation Ruble', '643'),
        'SAR' => array('Saudi Riyal', '682'),
        'CSD' => array('Serbian Dinar', '891'),
        'SCR' => array('Seychelles Rupee', '690'),
        'SGD' => array('Singapore Dollars', '702'),
        'SKK' => array('Slovak Koruna', '703'),
        'SIT' => array('Slovenia Tolar', '705'),
        'ZAR' => array('South African Rand', '710'),
        'KRW' => array('South Korean Won', '410'),
        'LKR' => array('Sri Lankan Rupee', '144'),
        'SRD' => array('Surinam Dollar', '968'),
        'SEK' => array('Swedish Krona', '752'),
        'CHF' => array('Swiss Francs', '756'),
        'TZS' => array('Tanzanian Shilling', '834'),
        'THB' => array('Thai Baht', '764'),
        'TTD' => array('Trinidad and Tobago Dollar', '780'),
        'TRY' => array('Turkish New Lira', '949'),
        'AED' => array('UAE Dirham', '784'),
        'USD' => array('US Dollars', '840'),
        'UGX' => array('Ugandian Shilling', '800'),
        'UAH' => array('Ukraine Hryvna', '980'),
        'UYU' => array('Uruguayan Peso', '858'),
        'UZS' => array('Uzbekistani Som', '860'),
        'VEB' => array('Venezuela Bolivar', '862'),
        'VND' => array('Vietnam Dong', '704'),
        'AMK' => array('Zambian Kwacha', '894'),
        'ZWD' => array('Zimbabwe Dollar', '716'),
    );
}


function ojabooking_get_currency_symbol($cur){
    if(!$cur){
        return false;
    }
    $currency_symbols = array(
        'AED' => '&#1583;.&#1573;', // ?
        'AFN' => '&#65;&#102;',
        'ALL' => '&#76;&#101;&#107;',
        'AMD' => '',
        'ANG' => '&#402;',
        'AOA' => '&#75;&#122;', // ?
        'ARS' => '&#36;',
        'AUD' => '&#36;',
        'AWG' => '&#402;',
        'AZN' => '&#1084;&#1072;&#1085;',
        'BAM' => '&#75;&#77;',
        'BBD' => '&#36;',
        'BDT' => '&#2547;', // ?
        'BGN' => '&#1083;&#1074;',
        'BHD' => '.&#1583;.&#1576;', // ?
        'BIF' => '&#70;&#66;&#117;', // ?
        'BMD' => '&#36;',
        'BND' => '&#36;',
        'BOB' => '&#36;&#98;',
        'BRL' => '&#82;&#36;',
        'BSD' => '&#36;',
        'BTN' => '&#78;&#117;&#46;', // ?
        'BWP' => '&#80;',
        'BYR' => '&#112;&#46;',
        'BZD' => '&#66;&#90;&#36;',
        'CAD' => '&#36;',
        'CDF' => '&#70;&#67;',
        'CHF' => '&#67;&#72;&#70;',
        'CLF' => '', // ?
        'CLP' => '&#36;',
        'CNY' => '&#165;',
        'COP' => '&#36;',
        'CRC' => '&#8353;',
        'CUP' => '&#8396;',
        'CVE' => '&#36;', // ?
        'CZK' => '&#75;&#269;',
        'DJF' => '&#70;&#100;&#106;', // ?
        'DKK' => '&#107;&#114;',
        'DOP' => '&#82;&#68;&#36;',
        'DZD' => '&#1583;&#1580;', // ?
        'EGP' => '&#163;',
        'ETB' => '&#66;&#114;',
        'EUR' => '&#8364;',
        'FJD' => '&#36;',
        'FKP' => '&#163;',
        'GBP' => '&#163;',
        'GEL' => '&#4314;', // ?
        'GHS' => '&#162;',
        'GIP' => '&#163;',
        'GMD' => '&#68;', // ?
        'GNF' => '&#70;&#71;', // ?
        'GTQ' => '&#81;',
        'GYD' => '&#36;',
        'HKD' => '&#36;',
        'HNL' => '&#76;',
        'HRK' => '&#107;&#110;',
        'HTG' => '&#71;', // ?
        'HUF' => '&#70;&#116;',
        'IDR' => '&#82;&#112;',
        'ILS' => '&#8362;',
        'INR' => '&#8377;',
        'IQD' => '&#1593;.&#1583;', // ?
        'IRR' => '&#65020;',
        'ISK' => '&#107;&#114;',
        'JEP' => '&#163;',
        'JMD' => '&#74;&#36;',
        'JOD' => '&#74;&#68;', // ?
        'JPY' => '&#165;',
        'KES' => '&#75;&#83;&#104;', // ?
        'KGS' => '&#1083;&#1074;',
        'KHR' => '&#6107;',
        'KMF' => '&#67;&#70;', // ?
        'KPW' => '&#8361;',
        'KRW' => '&#8361;',
        'KWD' => '&#1583;.&#1603;', // ?
        'KYD' => '&#36;',
        'KZT' => '&#1083;&#1074;',
        'LAK' => '&#8365;',
        'LBP' => '&#163;',
        'LKR' => '&#8360;',
        'LRD' => '&#36;',
        'LSL' => '&#76;', // ?
        'LTL' => '&#76;&#116;',
        'LVL' => '&#76;&#115;',
        'LYD' => '&#1604;.&#1583;', // ?
        'MAD' => '&#1583;.&#1605;.', //?
        'MDL' => '&#76;',
        'MGA' => '&#65;&#114;', // ?
        'MKD' => '&#1076;&#1077;&#1085;',
        'MMK' => '&#75;',
        'MNT' => '&#8366;',
        'MOP' => '&#77;&#79;&#80;&#36;', // ?
        'MRO' => '&#85;&#77;', // ?
        'MUR' => '&#8360;', // ?
        'MVR' => '.&#1923;', // ?
        'MWK' => '&#77;&#75;',
        'MXN' => '&#36;',
        'MYR' => '&#82;&#77;',
        'MZN' => '&#77;&#84;',
        'NAD' => '&#36;',
        'NGN' => '&#8358;',
        'NIO' => '&#67;&#36;',
        'NOK' => '&#107;&#114;',
        'NPR' => '&#8360;',
        'NZD' => '&#36;',
        'OMR' => '&#65020;',
        'PAB' => '&#66;&#47;&#46;',
        'PEN' => '&#83;&#47;&#46;',
        'PGK' => '&#75;', // ?
        'PHP' => '&#8369;',
        'PKR' => '&#8360;',
        'PLN' => '&#122;&#322;',
        'PYG' => '&#71;&#115;',
        'QAR' => '&#65020;',
        'RON' => '&#108;&#101;&#105;',
        'RSD' => '&#1044;&#1080;&#1085;&#46;',
        'RUB' => '&#8381;',
        'RWF' => '&#1585;.&#1587;',
        'SAR' => '&#65020;',
        'SBD' => '&#36;',
        'SCR' => '&#8360;',
        'SDG' => '&#163;', // ?
        'SEK' => '&#107;&#114;',
        'SGD' => '&#36;',
        'SHP' => '&#163;',
        'SLL' => '&#76;&#101;', // ?
        'SOS' => '&#83;',
        'SRD' => '&#36;',
        'STD' => '&#68;&#98;', // ?
        'SVC' => '&#36;',
        'SYP' => '&#163;',
        'SZL' => '&#76;', // ?
        'THB' => '&#3647;',
        'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
        'TMT' => '&#109;',
        'TND' => '&#1583;.&#1578;',
        'TOP' => '&#84;&#36;',
        'TRY' => '&#8378;', // New Turkey Lira (old symbol used)
        'TTD' => '&#36;',
        'TWD' => '&#78;&#84;&#36;',
        'TZS' => '',
        'UAH' => '&#8372;',
        'UGX' => '&#85;&#83;&#104;',
        'USD' => '&#36;',
        'UYU' => '&#36;&#85;',
        'UZS' => '&#1083;&#1074;',
        'VEF' => '&#66;&#115;',
        'VND' => '&#8363;',
        'VUV' => '&#86;&#84;',
        'WST' => '&#87;&#83;&#36;',
        'XAF' => '&#70;&#67;&#70;&#65;',
        'XCD' => '&#36;',
        'XDR' => '',
        'XOF' => '',
        'XPF' => '&#70;',
        'YER' => '&#65020;',
        'ZAR' => '&#82;',
        'ZMK' => '&#90;&#75;', // ?
        'ZWL' => '&#90;&#36;',
    );

    if(array_key_exists($cur,$currency_symbols)){
        return $currency_symbols[$cur];
    }else{
        return $cur;
    }
}