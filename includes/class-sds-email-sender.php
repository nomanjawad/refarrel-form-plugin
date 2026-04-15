<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SDS_Email_Sender {

    private $options;

    public function __construct() {
        $this->options = get_option( 'sds_referral_options', array() );
    }

    /**
     * Send referral notification emails.
     *
     * @param array  $form_data  Sanitized form data.
     * @param string $pdf_path   Path to the generated PDF.
     * @return bool True if at least one email was sent.
     */
    public function send( $form_data, $pdf_path ) {
        $from_email = $this->get_option( 'from_email', get_option( 'admin_email' ) );
        $from_name  = $this->get_option( 'from_name', 'Sydney Disability Support' );
        $admin_email     = $this->get_option( 'admin_email', get_option( 'admin_email' ) );
        $recipient_email = $this->get_option( 'recipient_email', '' );

        $participant_name = isset( $form_data['full_name'] ) ? $form_data['full_name'] : 'Unknown';
        $subject = 'New NDIS Referral - ' . $participant_name;

        // Build email body
        $body = $this->build_email_body( $form_data );

        // Temporarily set from address
        $from_filter = function( $email ) use ( $from_email ) {
            return $from_email;
        };
        $name_filter = function( $name ) use ( $from_name ) {
            return $from_name;
        };

        add_filter( 'wp_mail_from', $from_filter );
        add_filter( 'wp_mail_from_name', $name_filter );

        // Set content type to HTML
        $content_type_filter = function() {
            return 'text/html';
        };
        add_filter( 'wp_mail_content_type', $content_type_filter );

        $attachments = array();
        if ( $pdf_path && file_exists( $pdf_path ) ) {
            $attachments[] = $pdf_path;
        }

        $sent = false;

        // Send to admin
        if ( $admin_email && is_email( $admin_email ) ) {
            $result = wp_mail( $admin_email, $subject, $body, array(), $attachments );
            if ( $result ) {
                $sent = true;
            }
        }

        // Send to recipient
        if ( $recipient_email && is_email( $recipient_email ) && $recipient_email !== $admin_email ) {
            $result = wp_mail( $recipient_email, $subject, $body, array(), $attachments );
            if ( $result ) {
                $sent = true;
            }
        }

        // Remove temporary filters
        remove_filter( 'wp_mail_from', $from_filter );
        remove_filter( 'wp_mail_from_name', $name_filter );
        remove_filter( 'wp_mail_content_type', $content_type_filter );

        return $sent;
    }

    private function build_email_body( $form_data ) {
        $data = $form_data;
        ob_start();
        include SDS_PLUGIN_DIR . 'templates/email-template.php';
        return ob_get_clean();
    }

    private function get_option( $key, $default = '' ) {
        return isset( $this->options[ $key ] ) && $this->options[ $key ] !== '' ? $this->options[ $key ] : $default;
    }
}
