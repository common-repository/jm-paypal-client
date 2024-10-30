<?php
/*
  Plugin Name:  WP Paypal Client 
  Description:  Payment Client plugin to perform payements through Paypal
  Text Domain:jm_paypal_client
  Domain Path: /languages/
  Author: Jan Maat
  Version: 1.0
 */

/*  Copyright 2015  Jan Maat  (email : jenj.maat@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
// Include Class Autoloader
if (!class_exists('PaypalClientAutoloader')) {
    require_once('includes/paypalclientautoloader.php');
    spl_autoload_register('PaypalClientAutoloader::loader');
}

/*
 * Install 
 */
register_activation_hook(__FILE__, 'jm_paypal_client_install');

function jm_paypal_client_install() {
    if (version_compare(get_bloginfo('version'), '4.3.1', '<')) {
        die("This Plugin requires WordPress version 4.3.1 or higher");
    }
}

/*
 * 
 * Plugin uninstall
 */

function jm_paypal_client_init() {
// Localization    
    load_plugin_textdomain('jm_paypal_client', false, dirname(plugin_basename(__FILE__)) . '/languages');
// Start session for use in paypal answer
    if (!session_id())
        session_start();
    $url = plugin_dir_url(__FILE__);
    wp_enqueue_script('validator', $url . 'js/validator.min.js', array('jquery'), '0.4.5', true);
}

// Add actions
add_action('init', 'jm_paypal_client_init');

function jm_paypal_EndSession() {
    session_destroy();
}

add_action('wp_logout', 'jm_paypal_EndSession');
add_action('wp_login', 'jm_paypal_EndSession');


add_shortcode('paypal', 'jm_paypal_client_shortcode');

/**
 * 
 * @param array $attr Attributes of the shortcode.
 * @return string HTML content to display gallery.
 */
function jm_paypal_client_shortcode($atts) {
    $options = get_option('jm_paypal_client_option_name');
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        if (!isset($_GET["token"])) {
            $paypal_form = '<div class="jm_paypal_client">';
            $paypal_form .= '<form id="attributeForm" class="form-horizontal" role="form" method="post"
                            data-toggle="validator">';
            if ($options['checkin_description']) {
                $paypal_form .= ' <div class="form-group">
                            <label for="checkin" class="col-sm-4 control-label">' . __('Checkin date', 'jm_paypal_client') . '</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="checkin" placeholder="YYYY-MM-DD"  data-error="' . __('Use the correct date format, yyyy-mm-dd', 'jm_paypal_client') . '" required />
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>';
            } else {
                $paypal_form .= ' <div class="form-group">
                            <label for="checkin" class="col-sm-4 control-label">' . __('Description', 'jm_paypal_client') . '</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="checkin"   data-error="' . __('Mandatory field', 'jm_paypal_client') . '" required />
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>';
            }
            $paypal_form .= '<div class="form-group">
                            <label for="emailadr" class="col-sm-4 control-label">' . __('Email', 'jm_paypal_client') . ' </label>
                            <div class="col-sm-8">
                                <input type="email" class="form-control" id="emailadr" name="emailadr" placeholder="' . __('Email', 'jm_paypal_client') . '" data-error="' . __('This is not a valid email address', 'jm_paypal_client') . '" required>
                                <div class="help-block with-errors"></div>
                               </div>
                          </div>';
            $paypal_form .= '<div class="form-group">
                                <label for="amount" class="col-sm-4 control-label">' . __('Amount', 'jm_paypal_client') . '</label>
                                <div class="col-sm-8">
                                  <input type="text" class="form-control" id="amount" name="amount" placeholder="xxxx.xx" pattern="^\d{2,4}\.\d{2}$" data-error="' . __('Input the amount in the format xxxx.xx', 'jm_paypal_client') . '" required />
                                  <div class="help-block with-errors"></div>
                                </div>
                          </div>';
            $paypal_form .= '';
            $paypal_form .= '<div class="form-group">
                            <div class="col-sm-offset-4 col-sm-8">
                              <button type="submit" class="btn btn-default">' . __('Pay', 'jm_paypal_client') . '</button>
                            </div>
                          </div>
                        </form>
                      ';
            return $paypal_form;
        } else {
            if (isset($_GET["PayerID"])) {
                $param = $_SESSION['jm_paypal'];
                $param['PAYERID'] = $_GET["PayerID"];
                $paypal = new wp_paypal_gateway(true);
                $paypal->doExpressCheckout($param);
                $responds = $paypal->getResponse();
                if ($responds['ACK'] == "Failure") {
                    echo 'Code:  ' . $responds['L_ERRORCODE0'] . ' message:  ' . $responds['L_LONGMESSAGE0'];
                    die();
                } else {
                    do_action('jm_paypal_result', 'paid', $param);
                    return __('<h2>The Payment is done.</h2>', 'jm_paypal_client');
                }
            } else {
                return __('<h2>The Payment is cancelled.</h2>', 'jm_paypal_client');
            }
        }
    } else {

        $paypal = new Wp_Paypal_Gateway();
        $param = array(
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale'
        );
        $param['email'] = $_POST["emailadr"];
        $param['checkin'] = $_POST["checkin"];
        $param['PAYMENTREQUEST_0_AMT'] = $_POST["amount"];
        $param = apply_filters('jm_paypal_meta', $param);
        $paypal->setExpressCheckout($param);
        $responds = $paypal->getResponse();
        if ($responds['ACK'] == "Failure") {
            echo 'Code:  ' . $responds['L_ERRORCODE0'] . ' message:  ' . $responds['L_LONGMESSAGE0'];
            die();
        } else {
            $param['TOKEN'] = $responds['TOKEN'];
            $_SESSION['jm_paypal'] = $param;
        }
        $redirect = $paypal->getRedirectURL();
        ?>
        <script type="text/javascript">
            <!--
            window.location = <?php echo "'" . $redirect . "'"; ?>;
            //-->
        </script>
        <?php
        _e('<h2>Wait for PayPal</h2>', 'jm_paypal_client');
    }
}

if (is_admin()) {
    require_once('jm_paypal_client_admin.php');
}
    