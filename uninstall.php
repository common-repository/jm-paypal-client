<?php
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
delete_option( 'jm_paypal_client_option_name' );
