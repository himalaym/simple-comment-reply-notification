<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function crn_delete_plugin() {
    global $wpdb;

    // Goodbye data...
    $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE 'comment_notification'");

    // Goodbye options...
    delete_option( 'crn_name' );
    delete_option( 'crn_email' );
    delete_option( 'crn_subject' );
    delete_option( 'crn_checkbox_text' );
    delete_option( 'crn_checkbox_option' );
    delete_option( 'crn_message_body' );
}

crn_delete_plugin();

?>