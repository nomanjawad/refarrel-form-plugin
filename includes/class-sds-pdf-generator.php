<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SDS_PDF_Generator {

    private $options;
    private $form_data;

    public function __construct() {
        $this->options = get_option( 'sds_referral_options', array() );
    }

    /**
     * Generate the referral PDF.
     *
     * @param array $form_data Sanitized form data.
     * @return string|false File path on success, false on failure.
     */
    public function generate( $form_data ) {
        $this->form_data = $form_data;

        // Load TCPDF
        if ( ! class_exists( 'TCPDF' ) ) {
            require_once SDS_PLUGIN_DIR . 'vendor/tcpdf/tcpdf.php';
        }

        $font = isset( $this->options['pdf_font'] ) ? $this->options['pdf_font'] : 'helvetica';
        $primary_color = isset( $this->options['primary_color'] ) ? $this->options['primary_color'] : '#5B2D8E';

        // Parse hex color to RGB
        $rgb = $this->hex_to_rgb( $primary_color );

        $pdf = new TCPDF( 'P', 'mm', 'A4', true, 'UTF-8', false );

        // Set document information
        $pdf->SetCreator( 'SDS Referral Form Plugin' );
        $pdf->SetAuthor( $this->get_option( 'pdf_company_name', 'Sydney Disability Support' ) );
        $pdf->SetTitle( 'SDS Referral Form - ' . $this->get_data( 'full_name', 'Unknown' ) );
        $pdf->SetSubject( 'NDIS Referral Form' );

        // Remove default header/footer
        $pdf->setPrintHeader( false );
        $pdf->setPrintFooter( false );

        // Set margins
        $pdf->SetMargins( 15, 15, 15 );
        $pdf->SetAutoPageBreak( true, 25 ); // Leave space for footer

        // Set font
        $pdf->SetFont( $font, '', 10 );

        // Add first page
        $pdf->AddPage();

        // Draw header
        $this->draw_header( $pdf, $rgb, $font );

        // Title
        $pdf->SetY( 33 );
        $pdf->SetFont( $font, 'B', 18 );
        $pdf->SetTextColor( $rgb['r'], $rgb['g'], $rgb['b'] );
        $pdf->Cell( 0, 10, 'SDS REFERRAL FORM', 0, 1, 'L' );
        $pdf->SetTextColor( 0, 0, 0 );
        $pdf->Ln( 2 );

        // Section 1: Participant Details
        $this->draw_section( $pdf, $font, $rgb, '1. Participant Details', array(
            'Full Name'                => $this->get_data( 'full_name' ),
            'Date of Birth'            => $this->format_date( $this->get_data( 'date_of_birth' ) ),
            'Gender / Sex'             => $this->get_data( 'gender' ),
            'Address'                  => $this->get_data( 'address' ),
            'Phone Number'             => $this->get_data( 'phone_number' ),
            'Email'                    => $this->get_data( 'email' ),
            'Preferred Contact Method' => $this->get_data( 'preferred_contact' ),
        ) );

        // Section 2: Referral Information
        $this->draw_section( $pdf, $font, $rgb, '2. Referral Information', array(
            'Referral Date'                => $this->format_date( $this->get_data( 'referral_date' ) ),
            'Referred By (Name & Org)'     => $this->get_data( 'referred_by' ),
            'Referrer Contact Number'      => $this->get_data( 'referrer_contact_number' ),
            'Referrer Email'               => $this->get_data( 'referrer_email' ),
            'Relationship to Participant'  => $this->get_data( 'relationship_to_participant' ),
        ) );

        // Section 3: Participant Representative
        $this->draw_section( $pdf, $font, $rgb, '3. Participant Representative (if applicable)', array(
            'Name'           => $this->get_data( 'rep_name' ),
            'Relationship'   => $this->get_data( 'rep_relationship' ),
            'Contact Number' => $this->get_data( 'rep_contact_number' ),
            'Email'          => $this->get_data( 'rep_email' ),
        ) );

        // Check if we need a new page
        if ( $pdf->GetY() > 220 ) {
            $pdf->AddPage();
            $this->draw_header( $pdf, $rgb, $font );
            $pdf->SetY( 33 );
        }

        // Section 4: NDIS Plan Details
        $this->draw_section( $pdf, $font, $rgb, '4. NDIS Plan Details', array(
            'Plan Type'            => $this->get_data( 'plan_type' ),
            'Plan Manager'         => $this->get_data( 'plan_manager' ),
            'Plan Manager Contact' => $this->get_data( 'plan_manager_contact' ),
        ) );

        // Section 5: Services Requested
        $services = $this->get_data( 'services' );
        $service_other = $this->get_data( 'service_other_text' );
        if ( $service_other ) {
            $services .= ( $services ? ', ' : '' ) . 'Other: ' . $service_other;
        }

        $this->check_page_space( $pdf, 60, $rgb, $font );
        $this->draw_section_title( $pdf, $font, $rgb, '5. Services Requested' );

        $pdf->SetFont( $font, 'B', 9 );
        $pdf->Cell( 55, 7, 'Services:', 0, 0 );
        $pdf->SetFont( $font, '', 9 );
        $pdf->MultiCell( 0, 7, $services ?: 'N/A', 0, 'L' );
        $pdf->Ln( 1 );

        $pdf->SetFont( $font, 'B', 9 );
        $pdf->Cell( 55, 7, 'Service Details / Goals:', 0, 1 );
        $pdf->SetFont( $font, '', 9 );
        $pdf->MultiCell( 0, 6, $this->get_data( 'service_details' ) ?: 'N/A', 0, 'L' );
        $pdf->Ln( 4 );

        // Section 6: Participant Goals
        $this->check_page_space( $pdf, 40, $rgb, $font );
        $this->draw_section_title( $pdf, $font, $rgb, '6. Participant Goals' );
        $pdf->SetFont( $font, '', 9 );
        $pdf->MultiCell( 0, 6, $this->get_data( 'participant_goals' ) ?: 'N/A', 0, 'L' );
        $pdf->Ln( 4 );

        // Section 7: Consent & Privacy
        $this->check_page_space( $pdf, 80, $rgb, $font );
        $this->draw_section_title( $pdf, $font, $rgb, '7. Consent & Privacy' );

        $pdf->SetFont( $font, 'I', 9 );
        $pdf->MultiCell( 0, 6, 'I consent to Sydney Disability Support Pty Ltd collecting and using my information to process this referral.', 0, 'L' );
        $pdf->Ln( 4 );

        $pdf->SetFont( $font, 'B', 9 );
        $pdf->Cell( 55, 7, 'Name:', 0, 0 );
        $pdf->SetFont( $font, '', 9 );
        $pdf->Cell( 0, 7, $this->get_data( 'consent_name' ) ?: 'N/A', 0, 1 );

        // Signature (typed)
        $pdf->SetFont( $font, 'B', 9 );
        $pdf->Cell( 55, 7, 'Signature:', 0, 0 );
        $sig_text = $this->get_data( 'signature_text' );
        $pdf->SetFont( 'times', 'BI', 14 );
        $pdf->Cell( 0, 7, $sig_text ?: 'N/A', 0, 1 );
        $pdf->SetFont( $font, '', 9 );

        $pdf->SetFont( $font, 'B', 9 );
        $pdf->Cell( 55, 7, 'Date:', 0, 0 );
        $pdf->SetFont( $font, '', 9 );
        $pdf->Cell( 0, 7, $this->format_date( $this->get_data( 'consent_date' ) ), 0, 1 );

        // Draw footer on all pages
        $total_pages = $pdf->getNumPages();
        for ( $i = 1; $i <= $total_pages; $i++ ) {
            $pdf->setPage( $i );
            $this->draw_footer( $pdf, $rgb, $font );
        }

        // Save PDF
        $upload_dir = wp_upload_dir();
        $sds_dir    = $upload_dir['basedir'] . '/sds-referrals';
        $filename   = 'referral-' . time() . '-' . wp_rand( 1000, 9999 ) . '.pdf';
        $filepath   = $sds_dir . '/' . $filename;

        $pdf->Output( $filepath, 'F' );

        if ( file_exists( $filepath ) ) {
            return $filepath;
        }

        return false;
    }

    private function draw_header( $pdf, $rgb, $font ) {
        $page_width = $pdf->getPageWidth();

        // Purple header bar
        $pdf->SetFillColor( $rgb['r'], $rgb['g'], $rgb['b'] );
        $pdf->Rect( 0, 0, $page_width, 3, 'F' );

        // Logo
        $logo_id = isset( $this->options['pdf_logo'] ) ? $this->options['pdf_logo'] : '';
        $logo_path = '';
        if ( $logo_id ) {
            $logo_path = get_attached_file( $logo_id );
        }
        if ( ! $logo_path || ! file_exists( $logo_path ) ) {
            $logo_path = SDS_PLUGIN_DIR . 'assets/pdf/default-logo.png';
        }

        $logo_x = 15;
        $logo_y = 5;
        if ( file_exists( $logo_path ) ) {
            $pdf->Image( $logo_path, $logo_x, $logo_y, 0, 18, '', '', '', true, 300 );
        }

        // Company name and contact info
        $company_name = $this->get_option( 'pdf_company_name', 'Sydney Disability Support' );
        $contact_email = $this->get_option( 'pdf_contact_email', 'info@sydneydisabilitysupport.com' );
        $contact_phone = $this->get_option( 'pdf_contact_phone', '02 8119 5878' );

        $pdf->SetY( 8 );
        $pdf->SetX( 60 );
        $pdf->SetFont( $font, 'B', 14 );
        $pdf->SetTextColor( $rgb['r'], $rgb['g'], $rgb['b'] );
        $pdf->Cell( 0, 7, $company_name, 0, 1, 'R' );

        $pdf->SetX( 60 );
        $pdf->SetFont( $font, '', 9 );
        $pdf->SetTextColor( 100, 100, 100 );
        $pdf->Cell( 0, 5, $contact_email . '    ' . $contact_phone, 0, 1, 'R' );
        $pdf->SetTextColor( 0, 0, 0 );

        // Line separator
        $pdf->SetDrawColor( $rgb['r'], $rgb['g'], $rgb['b'] );
        $pdf->SetLineWidth( 0.5 );
        $pdf->Line( 15, 28, $page_width - 15, 28 );
    }

    private function draw_footer( $pdf, $rgb, $font ) {
        $page_width  = $pdf->getPageWidth();
        $page_height = $pdf->getPageHeight();

        // Disable auto page break so footer text doesn't spill to next page
        $pdf->SetAutoPageBreak( false, 0 );

        // Footer purple bar - full width at bottom
        $bar_height = 20;
        $bar_y = $page_height - $bar_height;

        $pdf->SetFillColor( $rgb['r'], $rgb['g'], $rgb['b'] );
        $pdf->Rect( 0, $bar_y, $page_width, $bar_height, 'F' );

        // Footer text - all white, centered on the purple bar
        $website = $this->get_option( 'pdf_footer_website', 'www.sydneydisabilitysupport.com' );
        $email   = $this->get_option( 'pdf_footer_email', 'info@sydneydisabilitysupport.com' );
        $phone   = $this->get_option( 'pdf_footer_phone', '02 8119 5878' );
        $abn     = $this->get_option( 'pdf_footer_abn', '93 650 405 057' );

        $pdf->SetTextColor( 255, 255, 255 );

        // Line 1: website | email | phone - centered
        $pdf->SetXY( 15, $bar_y + 3 );
        $pdf->SetFont( $font, '', 9 );
        $footer_line = $website . '          ' . $email . '          ' . $phone;
        $pdf->Cell( $page_width - 30, 5, $footer_line, 0, 0, 'C' );

        // Line 2: ABN - centered
        $pdf->SetXY( 15, $bar_y + 10 );
        $pdf->SetFont( $font, '', 8 );
        $pdf->Cell( $page_width - 30, 5, 'ABN: ' . $abn, 0, 0, 'C' );

        // Reset text color and re-enable auto page break
        $pdf->SetTextColor( 0, 0, 0 );
        $pdf->SetAutoPageBreak( true, 25 );
    }

    private function draw_section( $pdf, $font, $rgb, $title, $fields ) {
        // Check if we need a new page (estimate: title + fields)
        $estimated_height = 12 + ( count( $fields ) * 8 );
        $this->check_page_space( $pdf, $estimated_height, $rgb, $font );

        $this->draw_section_title( $pdf, $font, $rgb, $title );

        foreach ( $fields as $label => $value ) {
            $pdf->SetFont( $font, 'B', 9 );
            $pdf->Cell( 55, 7, $label . ':', 0, 0 );
            $pdf->SetFont( $font, '', 9 );
            $pdf->Cell( 0, 7, $value ?: 'N/A', 0, 1 );
        }

        $pdf->Ln( 4 );
    }

    private function draw_section_title( $pdf, $font, $rgb, $title ) {
        $pdf->SetFont( $font, 'B', 12 );
        $pdf->SetTextColor( $rgb['r'], $rgb['g'], $rgb['b'] );
        $pdf->Cell( 0, 8, $title, 0, 1 );

        // Underline
        $pdf->SetDrawColor( $rgb['r'], $rgb['g'], $rgb['b'] );
        $pdf->SetLineWidth( 0.3 );
        $pdf->Line( $pdf->GetX(), $pdf->GetY(), $pdf->getPageWidth() - 15, $pdf->GetY() );
        $pdf->Ln( 3 );

        $pdf->SetTextColor( 0, 0, 0 );
    }

    private function check_page_space( $pdf, $needed, $rgb, $font ) {
        if ( $pdf->GetY() + $needed > ( $pdf->getPageHeight() - 25 ) ) {
            $pdf->AddPage();
            $this->draw_header( $pdf, $rgb, $font );
            $pdf->SetY( 33 );
        }
    }

    private function get_data( $key, $default = '' ) {
        if ( ! isset( $this->form_data[ $key ] ) ) {
            return $default;
        }

        $val = $this->form_data[ $key ];

        if ( is_array( $val ) ) {
            return implode( ', ', $val );
        }

        return $val;
    }

    private function get_option( $key, $default = '' ) {
        return isset( $this->options[ $key ] ) && $this->options[ $key ] !== '' ? $this->options[ $key ] : $default;
    }

    private function format_date( $date_str ) {
        if ( ! $date_str ) {
            return 'N/A';
        }
        $timestamp = strtotime( $date_str );
        if ( ! $timestamp ) {
            return $date_str;
        }
        return date( 'd/m/Y', $timestamp );
    }

    private function hex_to_rgb( $hex ) {
        $hex = ltrim( $hex, '#' );
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return array(
            'r' => hexdec( substr( $hex, 0, 2 ) ),
            'g' => hexdec( substr( $hex, 2, 2 ) ),
            'b' => hexdec( substr( $hex, 4, 2 ) ),
        );
    }
}
