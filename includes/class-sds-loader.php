<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SDS_Loader {

    public function run() {
        // Load dependencies
        require_once SDS_PLUGIN_DIR . 'includes/class-sds-admin-settings.php';
        require_once SDS_PLUGIN_DIR . 'includes/class-sds-form.php';
        require_once SDS_PLUGIN_DIR . 'includes/class-sds-form-handler.php';
        require_once SDS_PLUGIN_DIR . 'includes/class-sds-pdf-generator.php';
        require_once SDS_PLUGIN_DIR . 'includes/class-sds-email-sender.php';

        // Admin settings
        if ( is_admin() ) {
            new SDS_Admin_Settings();
        }

        // Frontend form
        new SDS_Form();

        // AJAX handler
        new SDS_Form_Handler();

        // Cron cleanup
        add_action( 'sds_cleanup_temp_pdfs', array( $this, 'cleanup_temp_pdfs' ) );
    }

    public function cleanup_temp_pdfs() {
        $upload_dir = wp_upload_dir();
        $sds_dir    = $upload_dir['basedir'] . '/sds-referrals';

        if ( ! is_dir( $sds_dir ) ) {
            return;
        }

        $files = glob( $sds_dir . '/referral-*.pdf' );
        if ( ! $files ) {
            return;
        }

        $expiry = time() - ( 24 * 60 * 60 ); // 24 hours
        foreach ( $files as $file ) {
            if ( filemtime( $file ) < $expiry ) {
                wp_delete_file( $file );
            }
        }
    }
}
