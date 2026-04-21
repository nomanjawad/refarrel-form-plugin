<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 0; background: #f5f5f5; }
    .email-wrapper { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .email-header { background: #5B2D8E; color: #fff; padding: 20px 30px; }
    .email-header h1 { margin: 0; font-size: 22px; }
    .email-header p { margin: 5px 0 0; opacity: 0.9; font-size: 14px; }
    .email-body { padding: 30px; }
    .email-section { margin-bottom: 20px; }
    .email-section h3 { color: #5B2D8E; font-size: 16px; margin: 0 0 10px; padding-bottom: 5px; border-bottom: 2px solid #5B2D8E; }
    .email-table { width: 100%; border-collapse: collapse; }
    .email-table td { padding: 6px 0; font-size: 14px; vertical-align: top; }
    .email-table td:first-child { font-weight: bold; width: 180px; color: #555; }
    .email-footer { background: #f5f5f5; padding: 15px 30px; text-align: center; font-size: 12px; color: #888; }
    .email-footer a { color: #5B2D8E; text-decoration: none; }
</style>
</head>
<body>
<div class="email-wrapper">
    <div class="email-header">
        <h1>New NDIS Referral Received</h1>
        <p>A new referral form has been submitted.</p>
    </div>

    <div class="email-body">
        <div class="email-section">
            <h3>Participant Details</h3>
            <table class="email-table">
                <tr><td>Full Name</td><td><?php echo esc_html( $data['full_name'] ?? '' ); ?></td></tr>
                <tr><td>Date of Birth</td><td><?php echo esc_html( $data['date_of_birth'] ?? '' ); ?></td></tr>
                <tr><td>Gender / Sex</td><td><?php echo esc_html( $data['gender'] ?? '' ); ?></td></tr>
                <tr><td>Phone</td><td><?php echo esc_html( $data['phone_number'] ?? '' ); ?></td></tr>
                <tr><td>Email</td><td><?php echo esc_html( $data['email'] ?? '' ); ?></td></tr>
            </table>
        </div>

        <div class="email-section">
            <h3>Referral Information</h3>
            <table class="email-table">
                <tr><td>Referral Date</td><td><?php echo esc_html( $data['referral_date'] ?? '' ); ?></td></tr>
                <tr><td>Referred By</td><td><?php echo esc_html( $data['referred_by'] ?? '' ); ?></td></tr>
                <tr><td>Relationship</td><td><?php echo esc_html( $data['relationship_to_participant'] ?? '' ); ?></td></tr>
            </table>
        </div>

        <div class="email-section">
            <h3>Services Requested</h3>
            <p style="font-size: 14px;">
                <?php
                $services = isset( $data['services'] ) ? $data['services'] : array();
                if ( is_array( $services ) ) {
                    echo esc_html( implode( ', ', $services ) );
                } else {
                    echo esc_html( $services );
                }
                $other = isset( $data['service_other_text'] ) ? $data['service_other_text'] : '';
                if ( $other ) {
                    echo '<br>Other: ' . esc_html( $other );
                }
                ?>
            </p>
        </div>

        <p style="font-size: 14px; color: #666; margin-top: 20px;">
            The full referral form is attached as a PDF. Please review and process accordingly.
        </p>
    </div>

    <div class="email-footer">
        <p>Sydney Disability Support | <a href="https://sydneydisabilitysupport.com">sydneydisabilitysupport.com</a></p>
        <p>This is an automated notification from the SDS Referral Form.</p>
    </div>
</div>
</body>
</html>
