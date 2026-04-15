<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SDS_Form_Handler {

    public function __construct() {
        add_action( 'wp_ajax_sds_submit_form', array( $this, 'handle_submission' ) );
        add_action( 'wp_ajax_nopriv_sds_submit_form', array( $this, 'handle_submission' ) );
    }

    public function handle_submission() {
        // Verify nonce
        if ( ! isset( $_POST['sds_nonce'] ) || ! wp_verify_nonce( $_POST['sds_nonce'], 'sds_form_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed. Please refresh the page and try again.' ) );
        }

        // Rate limiting: 5 submissions per IP per hour
        $ip = $this->get_client_ip();
        $rate_key = 'sds_rate_' . md5( $ip );
        $submissions = get_transient( $rate_key );

        if ( $submissions === false ) {
            $submissions = 0;
        }

        if ( $submissions >= 5 ) {
            wp_send_json_error( array( 'message' => 'Too many submissions. Please try again later.' ) );
        }

        set_transient( $rate_key, $submissions + 1, HOUR_IN_SECONDS );

        // Sanitize form data
        $form_data = $this->sanitize_form_data( $_POST );

        // Validate required fields
        $errors = $this->validate_form_data( $form_data );
        if ( ! empty( $errors ) ) {
            wp_send_json_error( array( 'message' => 'Please fill in all required fields: ' . implode( ', ', $errors ) ) );
        }

        // Generate PDF
        $pdf_generator = new SDS_PDF_Generator();
        $pdf_path = $pdf_generator->generate( $form_data );

        if ( ! $pdf_path ) {
            wp_send_json_error( array( 'message' => 'Failed to generate PDF. Please try again.' ) );
        }

        // Send emails
        $email_sender = new SDS_Email_Sender();
        $email_sender->send( $form_data, $pdf_path );

        // Read PDF for base64 response (for browser download)
        $pdf_content = file_get_contents( $pdf_path );
        $pdf_base64  = base64_encode( $pdf_content );

        $participant_name = sanitize_file_name( $form_data['full_name'] );
        $filename = 'SDS-Referral-' . $participant_name . '-' . date( 'Y-m-d' ) . '.pdf';

        wp_send_json_success( array(
            'message'    => 'Referral submitted successfully.',
            'pdf_base64' => $pdf_base64,
            'filename'   => $filename,
        ) );
    }

    private function sanitize_form_data( $post ) {
        $data = array();

        // Text fields
        $text_fields = array(
            'full_name', 'ndis_number', 'address', 'phone_number',
            'referred_by', 'referrer_contact_number', 'relationship_to_participant',
            'rep_name', 'rep_relationship', 'rep_contact_number',
            'plan_type', 'plan_manager', 'plan_manager_contact',
            'service_other_text',
            'primary_disability', 'additional_conditions', 'mobility_requirements',
            'communication_needs', 'behavioural_support_needs', 'allergies_risks',
            'consent_name',
        );

        foreach ( $text_fields as $field ) {
            $data[ $field ] = isset( $post[ $field ] ) ? sanitize_text_field( wp_unslash( $post[ $field ] ) ) : '';
        }

        // Email fields
        $email_fields = array( 'email', 'referrer_email', 'rep_email' );
        foreach ( $email_fields as $field ) {
            $data[ $field ] = isset( $post[ $field ] ) ? sanitize_email( wp_unslash( $post[ $field ] ) ) : '';
        }

        // Date fields - validate format
        $date_fields = array( 'date_of_birth', 'referral_date', 'plan_start_date', 'plan_end_date', 'consent_date' );
        foreach ( $date_fields as $field ) {
            $val = isset( $post[ $field ] ) ? sanitize_text_field( wp_unslash( $post[ $field ] ) ) : '';
            if ( $val && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $val ) ) {
                $data[ $field ] = $val;
            } else {
                $data[ $field ] = '';
            }
        }

        // Textarea fields
        $textarea_fields = array( 'service_details', 'participant_goals' );
        foreach ( $textarea_fields as $field ) {
            $data[ $field ] = isset( $post[ $field ] ) ? sanitize_textarea_field( wp_unslash( $post[ $field ] ) ) : '';
        }

        // Checkbox arrays
        $data['preferred_contact'] = array();
        if ( isset( $post['preferred_contact'] ) && is_array( $post['preferred_contact'] ) ) {
            $allowed_contact = array( 'Phone', 'Email', 'SMS' );
            foreach ( $post['preferred_contact'] as $val ) {
                $val = sanitize_text_field( $val );
                if ( in_array( $val, $allowed_contact, true ) ) {
                    $data['preferred_contact'][] = $val;
                }
            }
        }

        $data['services'] = array();
        if ( isset( $post['services'] ) && is_array( $post['services'] ) ) {
            $allowed_services = array(
                'Assistance with Personal Activities',
                'Community Participation',
                'Domestic Assistance',
                'Development of Life Skills (Capacity Building)',
                'Innovative Community Participation',
                'Community Nursing Care',
                'Travel/Transport',
                'Behaviour Support Implementation',
                'Personal Care',
                'Other',
            );
            foreach ( $post['services'] as $val ) {
                $val = sanitize_text_field( $val );
                if ( in_array( $val, $allowed_services, true ) ) {
                    $data['services'][] = $val;
                }
            }
        }

        // Signature data - validate format
        $data['signature_data'] = '';
        if ( isset( $post['signature_data'] ) ) {
            $sig = wp_unslash( $post['signature_data'] );
            if ( strpos( $sig, 'data:image/png;base64,' ) === 0 ) {
                $base64_part = substr( $sig, strlen( 'data:image/png;base64,' ) );
                // Validate it's valid base64 and not too large (500KB decoded)
                $decoded = base64_decode( $base64_part, true );
                if ( $decoded !== false && strlen( $decoded ) < 512000 ) {
                    $data['signature_data'] = $sig;
                }
            }
        }

        // Consent agreement
        $data['consent_agree'] = isset( $post['consent_agree'] ) && $post['consent_agree'] === 'yes' ? 'yes' : '';

        return $data;
    }

    private function validate_form_data( $data ) {
        $errors = array();

        if ( empty( $data['full_name'] ) ) {
            $errors[] = 'Full Name';
        }
        if ( empty( $data['date_of_birth'] ) ) {
            $errors[] = 'Date of Birth';
        }
        if ( empty( $data['ndis_number'] ) ) {
            $errors[] = 'NDIS Number';
        }
        if ( empty( $data['address'] ) ) {
            $errors[] = 'Address';
        }
        if ( empty( $data['phone_number'] ) ) {
            $errors[] = 'Phone Number';
        }
        if ( empty( $data['referral_date'] ) ) {
            $errors[] = 'Referral Date';
        }
        if ( empty( $data['referred_by'] ) ) {
            $errors[] = 'Referred By';
        }
        if ( empty( $data['relationship_to_participant'] ) ) {
            $errors[] = 'Relationship to Participant';
        }
        if ( empty( $data['plan_type'] ) ) {
            $errors[] = 'Plan Type';
        }
        if ( empty( $data['plan_start_date'] ) ) {
            $errors[] = 'Plan Start Date';
        }
        if ( empty( $data['plan_end_date'] ) ) {
            $errors[] = 'Plan End Date';
        }
        if ( empty( $data['primary_disability'] ) ) {
            $errors[] = 'Primary Disability';
        }
        if ( empty( $data['consent_name'] ) ) {
            $errors[] = 'Consent Name';
        }
        if ( empty( $data['consent_date'] ) ) {
            $errors[] = 'Consent Date';
        }
        if ( empty( $data['signature_data'] ) ) {
            $errors[] = 'Signature';
        }
        if ( $data['consent_agree'] !== 'yes' ) {
            $errors[] = 'Consent Agreement';
        }

        return $errors;
    }

    private function get_client_ip() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field( $ip );
    }
}
