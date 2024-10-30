<?php

class wp_paypal_gateway {

    /**
     * PayPal API Version
     * @string
     */
    public $version;

    /**
     * PayPal account username
     * @string
     */
    public $user;

    /**
     * PayPal account password
     * @string
     */
    public $password;

    /**
     * PayPal account signature
     * @string
     */
    public $signature;

    /**
     * Period of time (in seconds) after which the connection ends
     * @integer
     */
    public $time_out = 60;

    /**
     * Requires SSL Verification
     * @boolean
     */
    public $ssl_verify;

    /**
     * PayPal API Server
     * @string
     */
    private $server;

    /**
     * PayPal API Redirect URL
     * @string
     */
    private $redirect_url;

    /**
     * Real world PayPal API Server
     * @string
     */
    private $real_server = 'https://api-3t.paypal.com/nvp';

    /**
     * Read world PayPal redirect URL
     * @string
     */
    private $real_redirect_url = 'https://www.paypal.com/cgi-bin/webscr';

    /**
     * Sandbox PayPal Server
     * @string
     */
    private $sandbox_server = 'https://api-3t.sandbox.paypal.com/nvp';

    /**
     * Sandbox PayPal redirect URL
     * @string
     */
    private $sandbox_redirect_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    

    /**
     * When something goes wrong, the debug_info variable will be set
     * with a string, array, or object explaining the problem
     * @mixed
     */
    public $debug_info;

    /**
     * Saves the full response once a request succeed
     * @mixed
     */
    public $full_response = false;

    /**
     * Creates a new PayPal gateway object
     * @param boolean $sandbox Set to true if you want to enable the Sandbox mode
     */
    public function __construct() {
        $options = get_option('jm_paypal_client_option_name');
        $this->version = '95.0';
        // Set the Server and Redirect URL
        if ($options['use_sandbox']) {
            $this->server = $this->sandbox_server;
            $this->redirect_url = $this->sandbox_redirect_url;
            $this->user = (empty($options['sandbox_username'])) ? 'sdk-three_api1.sdk.com' : $options['sandbox_username'];
            $this->password = (empty($options['sandbox_password'])) ? 'QFZCWN5HZM8VBG7Q' : $options['sandbox_password'];
            $this->signature = (empty($options['sandbox_signature'])) ? 'A-IzJhZZjhg29XQ2qnhapuwxIDzyAZQ92FRP5dqBzVesOkzbdUONzmOU' : $options['sandbox_signature'];
        } else {
            $this->server = $this->real_server;
            $this->redirect_url = $this->real_redirect_url;
            $this->user = $options['real_life_username'];
            $this->password = $options['real_life_password'];
            $this->signature = $options['real_life_signature'];
        }
        //Set Return Links
        global $wp;
        $this->RETURNURL = $current_url = home_url(add_query_arg(array(),$wp->request));
        $this->CANCELURL = $current_url = home_url(add_query_arg(array(),$wp->request));
        $this->PAYMENTREQUEST_0_CURRENCYCODE = $options['currency'];

        // Set the SSL Verification
        $this->ssl_verify = apply_filters('https_local_ssl_verify', false);
    }

    /**
     * Executes a setExpressCheckout command
     * @param array $param
     * @return boolean
     */
    public function setExpressCheckout($param) {
        return $this->requestExpressCheckout('SetExpressCheckout', $param);
    }

    /**
     * Executes a getExpressCheckout command
     * @param array $param
     * @return boolean
     */
    public function getExpressCheckout($param) {
        return $this->requestExpressCheckout('GetExpressCheckoutDetails', $param);
    }

    /**
     * Executes a doExpressCheckout command
     * @param array $param
     * @return boolean
     */
    public function doExpressCheckout($param) {
        return $this->requestExpressCheckout('DoExpressCheckoutPayment', $param);
    }

    /**
     * @param string $type
     * @param array $param
     * @return boolean Specifies if the request is successful and the response property
     *                 is filled
     */
    private function requestExpressCheckout($type, $param) {
        // Construct the request array        
        $request = $this->build_request($type, $param);

        // Makes the HTTP request
        $response = wp_remote_post($this->server, $request);

        // HTTP Request fails
        if (is_wp_error($response)) {
            $this->debug_info = $response;
            return false;
        }

        // Status code returned other than 200
        if ($response['response']['code'] != 200) {
            $this->debug_info = 'Response code different than 200';
            return false;
        }

        // Saves the full response
        $this->full_response = $response;

        // Request succeeded
        return true;
    }

    

    /**
     * Builds the request array from the object, param and type parameters
     * @param string $type
     * @param array $param
     * @return array $body
     */
    private function build_request($type, $param) {
        // Request Body
        $body = $param;
        $body['METHOD'] = $type;
        $body['VERSION'] = $this->version;
        $body['USER'] = $this->user;
        $body['PWD'] = $this->password;
        $body['SIGNATURE'] = $this->signature;
        $body['RETURNURL'] = $this->RETURNURL;
        $body['CANCELURL'] = $this->CANCELURL;
        $body['PAYMENTREQUEST_0_CURRENCYCODE'] = $this->PAYMENTREQUEST_0_CURRENCYCODE;
        // Request Array
        $request = array(
            'method' => 'POST',
            'body' => $body,
            'timeout' => $this->time_out,
            'sslverify' => $this->ssl_verify
        );

        return $request;
    }

    /**
     * Returns the PayPal Body response
     * @return array $reponse
     */
    public function getResponse() {
        if ($this->full_response) {
            parse_str(urldecode($this->full_response['body']), $output);
            return $output;
        }
        return false;
    }

    /**
     * Returns the redirect URL
     * @return string $url
     */
    public function getRedirectURL() {
        $output = $this->getResponse();
        if ($output['ACK'] === 'Success') {
            $query_data = array(
                'cmd' => '_express-checkout',
                'useraction' => 'commit',
                'token' => $output['TOKEN']
            );
            $url = $this->redirect_url . '?' . http_build_query($query_data);
            return $url;
        }
        return false;
    }

    /**
     * Returns the response Token
     * @return string $token
     */
    public function getToken() {
        $output = $this->getResponse();
        if ($output['ACK'] === 'Success') {
            return $output['TOKEN'];
        }
        return false;
    }

}
