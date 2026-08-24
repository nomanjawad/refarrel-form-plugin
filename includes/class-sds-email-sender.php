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
        $from_email = $this->normalize_email( $this->get_option( 'from_email', get_option( 'admin_email' ) ) );
        $from_name  = $this->get_option( 'from_name', 'Sydney Disability Support' );

        $admin_recipients  = $this->get_admin_recipients();
        $participant_email = $this->normalize_email( isset( $form_data['email'] ) ? $form_data['email'] : '' );

        $participant_name = isset( $form_data['full_name'] ) ? $form_data['full_name'] : 'Unknown';
        $admin_subject    = 'New NDIS Referral - ' . $participant_name;
        $admin_body       = $this->build_email_body( $form_data );

        $participant_subject = 'Your NDIS Referral Has Been Submitted - Sydney Disability Support';
        $participant_body    = $this->build_participant_email_body( $form_data );

        $attachments = array();
        if ( $pdf_path && file_exists( $pdf_path ) ) {
            $attachments[] = $pdf_path;
        }

        $from_filter = function( $email ) use ( $from_email ) {
            return $from_email ? $from_email : $email;
        };
        $name_filter = function( $name ) use ( $from_name ) {
            return $from_name ? $from_name : $name;
        };
        $content_type_filter = function() {
            return 'text/html';
        };

        add_filter( 'wp_mail_from', $from_filter );
        add_filter( 'wp_mail_from_name', $name_filter );
        add_filter( 'wp_mail_content_type', $content_type_filter );

        $sent = false;

        foreach ( $admin_recipients as $admin_email ) {
            $result = wp_mail( $admin_email, $admin_subject, $admin_body, array(), $attachments );
            if ( $result ) {
                $sent = true;
            } else {
                $this->log_mail_failure( 'admin notification', array( $admin_email ) );
            }
        }

        if ( $participant_email && ! in_array( $participant_email, $admin_recipients, true ) ) {
            $result = wp_mail( $participant_email, $participant_subject, $participant_body, array(), $attachments );
            if ( $result ) {
                $sent = true;
            } else {
                $this->log_mail_failure( 'participant confirmation', array( $participant_email ) );
            }
        }

        remove_filter( 'wp_mail_from', $from_filter );
        remove_filter( 'wp_mail_from_name', $name_filter );
        remove_filter( 'wp_mail_content_type', $content_type_filter );

        return $sent;
    }

    /**
     * Collect valid admin notification recipients from plugin settings.
     *
     * @return array
     */
    private function get_admin_recipients() {
        $recipients = array();

        $admin_email = $this->normalize_email(
            $this->get_option( 'admin_email', get_option( 'admin_email' ) )
        );
        if ( $admin_email ) {
            $recipients[] = $admin_email;
        }

        $recipient_email = $this->normalize_email( $this->get_option( 'recipient_email', '' ) );
        if ( $recipient_email ) {
            $recipients[] = $recipient_email;
        }

        return array_values( array_unique( $recipients ) );
    }

    private function build_email_body( $form_data ) {
        $data = $form_data;
        ob_start();
        include SDS_PLUGIN_DIR . 'templates/email-template.php';
        return ob_get_clean();
    }

    private function build_participant_email_body( $form_data ) {
        $participant_name = isset( $form_data['full_name'] ) ? $form_data['full_name'] : 'there';

        ob_start();
        ?>
        <p>Dear <?php echo esc_html( $participant_name ); ?>,</p>
        <p>Thank you for submitting your NDIS referral to Sydney Disability Support. Your referral has been received and a copy of your completed form is attached to this email.</p>
        <p>Our team will review your referral and contact you if any further information is required.</p>
        <p>Kind regards,<br>Sydney Disability Support</p>
        <?php
        return ob_get_clean();
    }

    private function normalize_email( $email ) {
        $email = sanitize_email( trim( (string) $email ) );
        return is_email( $email ) ? $email : '';
    }

    private function log_mail_failure( $context, $recipients ) {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        $recipient_list = is_array( $recipients ) ? implode( ', ', $recipients ) : (string) $recipients;
        error_log( sprintf( 'SDS Referral Form: Failed to send %s email to: %s', $context, $recipient_list ) );
    }

    private function get_option( $key, $default = '' ) {
        return isset( $this->options[ $key ] ) && $this->options[ $key ] !== '' ? $this->options[ $key ] : $default;
    }
}
