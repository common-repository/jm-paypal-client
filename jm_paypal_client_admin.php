<?php

// Add a menu for our option page
// Hier niet nodig omdat galleriffic-bootstrap instellingen op de media pagina staan
// Draw the option page
// init text domain
class PaypalSettingsPage {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_paypal_plugin_page'));
        add_action('admin_init', array($this, 'paypal_page_init'));
    }

    /**
     * Add options page
     */
    public function add_paypal_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
                'Settings Admin', '' . __('Paypal Settings', 'jm_paypal_client') . '', 'manage_options', 'jm_paypal_client-setting-admin', array($this, 'create_paypal_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_paypal_admin_page() {
        // Set class property
        $this->options = get_option('jm_paypal_client_option_name');
        ?>
        <div class="wrap">

            <h2>Paypal </h2>           
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('jm_paypal_client_option_group');
                do_settings_sections('jm_paypal_client-setting-admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function paypal_page_init() {
        //Sandbox settings
        register_setting(
                'jm_paypal_client_option_group', // Option group
                'jm_paypal_client_option_name', // Option name
                array($this, 'sanitize') // Sanitize
        );
        //Sandbox settings        
        add_settings_section(
                'sandbox_section_id', // ID
                '' . __('Sandbox Settings', 'jm_paypal_client') . '', // Title
                array($this, 'print_sandbox_section_info'), // Callback
                'jm_paypal_client-setting-admin' // Page
        );
        add_settings_field(
                'use_sandbox', ' ' . __('Use Sandbox', 'jm_paypal_client') . '', array($this, 'general_paypal_checkbox_callback'), 'jm_paypal_client-setting-admin', 'sandbox_section_id', array(
            'options-name' => 'jm_paypal_client_option_name',
            'id' => 'use_sandbox',
            'class' => '',
            'label' => __('Select for testing the Sandbox ', 'jm_paypal_client'),
        ));
        add_settings_field(
                'sandbox_username', ' ' . __('Sandbox Username', 'jm_paypal_client') . '', array($this, 'general_paypal_textfield_callback'), // Callback
                'jm_paypal_client-setting-admin', // Page
                'sandbox_section_id', // Section 
                array(
            'options-name' => 'jm_paypal_client_option_name',
            'id' => 'sandbox_username',
            'class' => '',
            'value' => '',
            'size' => '40',
            'label' => __('Username for Sandbox testing. If empty the Sandbox default will be used ', 'jm_paypal_client'
            ),
        ));
        add_settings_field(
                'sandbox_password', ' ' . __('Sandbox Password', 'jm_paypal_client') . '', array($this, 'general_paypal_textfield_callback'), // Callback
                'jm_paypal_client-setting-admin', // Page
                'sandbox_section_id', // Section 
                array(
            'options-name' => 'jm_paypal_client_option_name',
            'id' => 'sandbox_password',
            'class' => '',
            'value' => '',
            'size' => '40',
            'label' => __('Password for Sandbox testing. If empty the Sandbox default will be used ', 'jm_paypal_client'
            ),
        ));
        add_settings_field(
                'sandbox_signature', ' ' . __('Sandbox Signature', 'jm_paypal_client') . '', array($this, 'general_paypal_textfield_callback'), // Callback
                'jm_paypal_client-setting-admin', // Page
                'sandbox_section_id', // Section 
                array(
            'options-name' => 'jm_paypal_client_option_name',
            'id' => 'sandbox_signature',
            'class' => '',
            'value' => '',
            'size' => '70',
            'label' => __('Signature for Sandbox testing. If empty the Sandbox default will be used ', 'jm_paypal_client'
            ),
        ));
        //Real life settings              
        add_settings_section(
                'real_life_section_id', // ID
                '' . __('Real Life Settings', 'jm_paypal_client') . '', // Title
                array($this, 'print_real_life_section_info'), // Callback
                'jm_paypal_client-setting-admin' // Page
        );
        add_settings_field(
                'real_life_username', ' ' . __('Real Life Username', 'jm_paypal_client') . '', array($this, 'general_paypal_textfield_callback'), // Callback
                'jm_paypal_client-setting-admin', // Page
                'real_life_section_id', // Section 
                array(
            'options-name' => 'jm_paypal_client_option_name',
            'id' => 'real_life_username',
            'class' => '',
            'value' => '',
            'size' => '40',
            'label' => __('Username for Real Life payment.', 'jm_paypal_client'
            ),
        ));
        add_settings_field(
                'real_life_password', ' ' . __('Real Life Password', 'jm_paypal_client') . '', array($this, 'general_paypal_textfield_callback'), // Callback
                'jm_paypal_client-setting-admin', // Page
                'real_life_section_id', // Section 
                array(
            'options-name' => 'jm_paypal_client_option_name',
            'id' => 'real_life_password',
            'class' => '',
            'value' => '',
            'size' => '40',
            'label' => __('Password for Real Life payment.', 'jm_paypal_client'
            ),
        ));
        add_settings_field(
                'real_life_signature', ' ' . __('Real Life Signature', 'jm_paypal_client') . '', array($this, 'general_paypal_textfield_callback'), // Callback
                'jm_paypal_client-setting-admin', // Page
                'real_life_section_id', // Section 
                array(
            'options-name' => 'jm_paypal_client_option_name',
            'id' => 'real_life_signature',
            'class' => '',
            'value' => '',
            'size' => '70',
            'label' => __('Signature for Real Life payment.', 'jm_paypal_client'
            ),
        ));
        add_settings_field(
                'checkin_description', ' ' . __('Description', 'jm_paypal_client') . '', array($this, 'general_paypal_checkbox_callback'), 'jm_paypal_client-setting-admin', 'real_life_section_id', array(
            'options-name' => 'jm_paypal_client_option_name',
            'id' => 'checkin_description',
            'class' => '',
            'label' => __('Use as description Checkin ', 'jm_paypal_client'),
        ));
        add_settings_field(
                'currency', ' ' . __('Currency', 'jm_paypal_client') . '', array($this, 'currency_callback'), 'jm_paypal_client-setting-admin', 'real_life_section_id', array(
            'options-name' => 'jm_paypal_client_option_name',
            'id' => 'currency',
            'class' => '',
            'label' => __('Select Currency ', 'jm_paypal_client'),
        ));
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $new_input = array();
        //Sandbox params
        if (isset($input['use_sandbox']))
            $new_input['use_sandbox'] = absint($input['use_sandbox']);
        if (isset($input['sandbox_username']))
            $new_input['sandbox_username'] = sanitize_text_field($input['sandbox_username']);
        if (isset($input['sandbox_password']))
            $new_input['sandbox_password'] = sanitize_text_field($input['sandbox_password']);
        if (isset($input['sandbox_signature']))
            $new_input['sandbox_signature'] = sanitize_text_field($input['sandbox_signature']);
        //Real Life params
        if (isset($input['real_life_username']))
            $new_input['real_life_username'] = sanitize_text_field($input['real_life_username']);
        if (isset($input['real_life_password']))
            $new_input['real_life_password'] = sanitize_text_field($input['real_life_password']);
        if (isset($input['real_life_signature']))
            $new_input['real_life_signature'] = sanitize_text_field($input['real_life_signature']);
        if (isset($input['checkin_description']))
            $new_input['checkin_description'] = absint($input['checkin_description']);
        if (isset($input['currency']))
            $new_input['currency'] = sanitize_text_field($input['currency']);
        return $new_input;
    }

    /*
     * 
     * General print functions
     */

    public function general_paypal_textfield_callback($args) {
        $name = $args['options-name'] . '[' . $args['id'] . ']';
        $value = $args['value'];
        if (isset($this->options[$args['id']])) {
            $value = $this->options[$args['id']];
        }
        //print text field        
        printf('<input id="' . $args['id'] . '" name="' . $name . '" size="' . $args['size'] . '" type="text" value="%s" />', $value);
        echo '<label for="' . $args['id'] . '" style="' . $args['style'] . '"> ' . $args['label'] . ' </label>';
    }

    public function general_paypal_checkbox_callback($args) {
        $name = $args['options-name'] . '[' . $args['id'] . ']';
        echo '<input type="checkbox" id="' . $args['id'] . '" name="' . $name . '" value="1" ' . checked(1, $this->options[$args['id']], false) . ' />';
        echo '<label for="' . $args['id'] . '" style="' . $args['style'] . '"> ' . $args['label'] . ' </label>';
    }

    public function currency_callback($args) {
        $currencies = PayPalClientFunctions::currency();
        $name = $args['options-name'] . '[' . $args['id'] . ']';
        ?>
        <select name="<?php echo $name ?>" id="<?php echo $args['id'] ?>" required>
            <?php
            foreach ($currencies as $key => $currency) {
                ?>                       

                <option value="<?php echo $key ?>" <?php if ($this->options[$args['id']] == $key) echo 'selected="selected"'; ?>><?php echo $currency['label'] ?></option>
                <?php
            }
            ?>                        
        </select>
        <?php
        echo '<label for="' . $args['id'] . '" style="' . $args['style'] . '"> ' . $args['label'] . ' </label>';
    }

    /**
     * Print the Sabdbox text
     */
    public function print_sandbox_section_info() {
        echo '<hr>';
        _e('Sandbox options only needed for testing:', 'jm_paypal_client');
    }

    /*
     * 
     * Print Real Life text
     */

    public function print_real_life_section_info() {
        echo '<hr>';
        _e('Real Life Settings. This settings are mandatory for real payments.', 'jm_paypal_client');
    }

}

if (is_admin())
    $my_settings_page = new PaypalSettingsPage();