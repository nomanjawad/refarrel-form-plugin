<?php
/**
 * Fired when the plugin is uninstalled.
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options
delete_option( 'sds_referral_options' );

// Clean up scheduled cron events
wp_clear_scheduled_hook( 'sds_cleanup_temp_pdfs' );

// Remove temp PDF directory
$upload_dir = wp_upload_dir();
$sds_dir    = $upload_dir['basedir'] . '/sds-referrals';

if ( is_dir( $sds_dir ) ) {
    $files = glob( $sds_dir . '/*' );
    if ( $files ) {
        foreach ( $files as $file ) {
            if ( is_file( $file ) ) {
                wp_delete_file( $file );
            }
        }
    }
    rmdir( $sds_dir );
}

// Clean up rate limiting transients
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_sds_rate_%' OR option_name LIKE '_transient_timeout_sds_rate_%'" );
