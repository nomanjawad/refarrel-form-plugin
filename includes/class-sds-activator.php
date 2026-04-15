<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SDS_Activator {

    public static function activate() {
        $defaults = array(
            'from_email'          => get_option( 'admin_email' ),
            'from_name'           => 'Sydney Disability Support',
            'admin_email'         => get_option( 'admin_email' ),
            'recipient_email'     => '',
            'primary_color'       => '#5B2D8E',
            'pdf_logo'            => '',
            'pdf_company_name'    => 'Sydney Disability Support',
            'pdf_contact_phone'   => '02 8119 5878',
            'pdf_contact_email'   => 'info@sydneydisabilitysupport.com',
            'pdf_contact_address' => '',
            'pdf_footer_website'  => 'www.sydneydisabilitysupport.com',
            'pdf_footer_email'    => 'info@sydneydisabilitysupport.com',
            'pdf_footer_phone'    => '02 8119 5878',
            'pdf_footer_abn'      => '93 650 405 057',
            'pdf_font'            => 'helvetica',
            'pdf_custom_css'      => '',
        );

        $existing = get_option( 'sds_referral_options', array() );
        if ( empty( $existing ) ) {
            update_option( 'sds_referral_options', $defaults );
        }

        // Create upload directory for temp PDFs
        $upload_dir = wp_upload_dir();
        $sds_dir    = $upload_dir['basedir'] . '/sds-referrals';

        if ( ! file_exists( $sds_dir ) ) {
            wp_mkdir_p( $sds_dir );
        }

        // Protect the directory
        $htaccess = $sds_dir . '/.htaccess';
        if ( ! file_exists( $htaccess ) ) {
            file_put_contents( $htaccess, "Order deny,allow\nDeny from all\n" );
        }

        $index = $sds_dir . '/index.php';
        if ( ! file_exists( $index ) ) {
            file_put_contents( $index, '<?php // Silence is golden.' );
        }

        // Schedule cleanup cron
        if ( ! wp_next_scheduled( 'sds_cleanup_temp_pdfs' ) ) {
            wp_schedule_event( time(), 'daily', 'sds_cleanup_temp_pdfs' );
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook( 'sds_cleanup_temp_pdfs' );
    }
}
