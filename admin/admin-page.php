<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap sds-admin-wrap">
    <h1><span class="dashicons dashicons-clipboard" style="font-size: 30px; margin-right: 10px;"></span> SDS Referral Form Settings</h1>

    <div class="sds-admin-header">
        <p>Configure your referral form, email notifications, and PDF settings. Use the shortcode <code>[sds_referral_form]</code> to display the form on any page.</p>
    </div>

    <form method="post" action="options.php">
        <?php
        settings_fields( 'sds_referral_options_group' );
        ?>

        <div class="sds-settings-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#sds-tab-email" class="nav-tab nav-tab-active" data-tab="sds-tab-email">Email Settings</a>
                <a href="#sds-tab-appearance" class="nav-tab" data-tab="sds-tab-appearance">Form Appearance</a>
                <a href="#sds-tab-pdf-header" class="nav-tab" data-tab="sds-tab-pdf-header">PDF Header</a>
                <a href="#sds-tab-pdf-footer" class="nav-tab" data-tab="sds-tab-pdf-footer">PDF Footer</a>
                <a href="#sds-tab-pdf-styling" class="nav-tab" data-tab="sds-tab-pdf-styling">PDF Styling</a>
            </nav>

            <div class="sds-tab-content" id="sds-tab-email">
                <table class="form-table">
                    <?php do_settings_fields( 'sds-referral-settings', 'sds_email_section' ); ?>
                </table>
            </div>

            <div class="sds-tab-content" id="sds-tab-appearance" style="display:none;">
                <table class="form-table">
                    <?php do_settings_fields( 'sds-referral-settings', 'sds_appearance_section' ); ?>
                </table>
            </div>

            <div class="sds-tab-content" id="sds-tab-pdf-header" style="display:none;">
                <table class="form-table">
                    <?php do_settings_fields( 'sds-referral-settings', 'sds_pdf_header_section' ); ?>
                </table>
            </div>

            <div class="sds-tab-content" id="sds-tab-pdf-footer" style="display:none;">
                <table class="form-table">
                    <?php do_settings_fields( 'sds-referral-settings', 'sds_pdf_footer_section' ); ?>
                </table>
            </div>

            <div class="sds-tab-content" id="sds-tab-pdf-styling" style="display:none;">
                <table class="form-table">
                    <?php do_settings_fields( 'sds-referral-settings', 'sds_pdf_styling_section' ); ?>
                </table>
            </div>
        </div>

        <?php submit_button( 'Save Settings' ); ?>
    </form>
</div>
