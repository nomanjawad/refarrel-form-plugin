<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SDS_Admin_Settings {

    private $options;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function add_menu_page() {
        add_menu_page(
            'SDS Referral Form',
            'SDS Referral',
            'manage_options',
            'sds-referral-settings',
            array( $this, 'render_settings_page' ),
            'dashicons-clipboard',
            80
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_sds-referral-settings' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_media();
        wp_enqueue_style( 'sds-admin-css', SDS_PLUGIN_URL . 'admin/css/sds-admin.css', array(), SDS_VERSION );
        wp_enqueue_script( 'sds-admin-js', SDS_PLUGIN_URL . 'admin/js/sds-admin.js', array( 'jquery', 'wp-color-picker' ), SDS_VERSION, true );

        // CodeMirror for CSS editor
        if ( function_exists( 'wp_enqueue_code_editor' ) ) {
            $settings = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
            if ( false !== $settings ) {
                wp_localize_script( 'sds-admin-js', 'sdsCodeEditor', $settings );
            }
        }
    }

    public function register_settings() {
        register_setting( 'sds_referral_options_group', 'sds_referral_options', array(
            'sanitize_callback' => array( $this, 'sanitize_options' ),
        ) );

        // Email Settings Section
        add_settings_section( 'sds_email_section', 'Email Settings', array( $this, 'section_email_cb' ), 'sds-referral-settings' );

        add_settings_field( 'from_email', 'From Email Address', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_email_section', array(
            'field' => 'from_email',
            'type'  => 'email',
            'desc'  => 'The email address notifications will be sent from. Works with your SMTP plugin.',
        ) );
        add_settings_field( 'from_name', 'From Name', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_email_section', array(
            'field' => 'from_name',
            'desc'  => 'The sender name for email notifications.',
        ) );
        add_settings_field( 'admin_email', 'Admin Notification Email', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_email_section', array(
            'field' => 'admin_email',
            'type'  => 'email',
            'desc'  => 'Admin will receive referral notifications at this email.',
        ) );
        add_settings_field( 'recipient_email', 'Recipient Email', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_email_section', array(
            'field' => 'recipient_email',
            'type'  => 'email',
            'desc'  => 'Additional recipient for referral notifications (e.g., intake coordinator).',
        ) );

        // Form Appearance Section
        add_settings_section( 'sds_appearance_section', 'Form Appearance', array( $this, 'section_appearance_cb' ), 'sds-referral-settings' );

        add_settings_field( 'primary_color', 'Primary Color', array( $this, 'field_color' ), 'sds-referral-settings', 'sds_appearance_section', array(
            'field' => 'primary_color',
        ) );
        add_settings_field( 'privacy_policy_url', 'Privacy Policy URL', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_appearance_section', array(
            'field' => 'privacy_policy_url',
            'type'  => 'url',
            'desc'  => 'Link to your Privacy Policy page. This is shown in the consent checkbox on the last step of the form.',
        ) );

        // PDF Header Section
        add_settings_section( 'sds_pdf_header_section', 'PDF Header', array( $this, 'section_pdf_header_cb' ), 'sds-referral-settings' );

        add_settings_field( 'pdf_logo', 'Company Logo', array( $this, 'field_media' ), 'sds-referral-settings', 'sds_pdf_header_section', array(
            'field' => 'pdf_logo',
            'desc'  => 'Upload a logo for the PDF header. Recommended: PNG or JPG, max 300px wide.',
        ) );
        add_settings_field( 'pdf_company_name', 'Company Name', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_pdf_header_section', array(
            'field' => 'pdf_company_name',
        ) );
        add_settings_field( 'pdf_contact_phone', 'Contact Phone', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_pdf_header_section', array(
            'field' => 'pdf_contact_phone',
        ) );
        add_settings_field( 'pdf_contact_email', 'Contact Email', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_pdf_header_section', array(
            'field' => 'pdf_contact_email',
            'type'  => 'email',
        ) );
        add_settings_field( 'pdf_contact_address', 'Contact Address', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_pdf_header_section', array(
            'field' => 'pdf_contact_address',
        ) );

        // PDF Footer Section
        add_settings_section( 'sds_pdf_footer_section', 'PDF Footer', array( $this, 'section_pdf_footer_cb' ), 'sds-referral-settings' );

        add_settings_field( 'pdf_footer_website', 'Website', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_pdf_footer_section', array(
            'field' => 'pdf_footer_website',
        ) );
        add_settings_field( 'pdf_footer_email', 'Email', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_pdf_footer_section', array(
            'field' => 'pdf_footer_email',
            'type'  => 'email',
        ) );
        add_settings_field( 'pdf_footer_phone', 'Phone', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_pdf_footer_section', array(
            'field' => 'pdf_footer_phone',
        ) );
        add_settings_field( 'pdf_footer_abn', 'ABN', array( $this, 'field_text' ), 'sds-referral-settings', 'sds_pdf_footer_section', array(
            'field' => 'pdf_footer_abn',
        ) );

        // PDF Styling Section
        add_settings_section( 'sds_pdf_styling_section', 'PDF Styling', array( $this, 'section_pdf_styling_cb' ), 'sds-referral-settings' );

        add_settings_field( 'pdf_font', 'PDF Font', array( $this, 'field_font_select' ), 'sds-referral-settings', 'sds_pdf_styling_section', array(
            'field' => 'pdf_font',
        ) );
        add_settings_field( 'pdf_custom_css', 'Custom PDF CSS', array( $this, 'field_code_editor' ), 'sds-referral-settings', 'sds_pdf_styling_section', array(
            'field' => 'pdf_custom_css',
            'desc'  => 'Custom CSS applied to PDF HTML content. Use standard CSS properties.',
        ) );
    }

    // Section callbacks
    public function section_email_cb() {
        echo '<p>Configure email notification settings. This plugin uses <code>wp_mail()</code> and is compatible with SMTP plugins like WP Mail SMTP.</p>';
    }

    public function section_appearance_cb() {
        echo '<p>Customize the appearance of the referral form on the frontend.</p>';
    }

    public function section_pdf_header_cb() {
        echo '<p>Configure the header that appears on every page of the generated PDF.</p>';
    }

    public function section_pdf_footer_cb() {
        echo '<p>Configure the footer that appears on every page of the generated PDF.</p>';
    }

    public function section_pdf_styling_cb() {
        echo '<p>Customize fonts and styling for the generated PDF.</p>';
    }

    // Field renderers
    public function field_text( $args ) {
        $options = get_option( 'sds_referral_options', array() );
        $value   = isset( $options[ $args['field'] ] ) ? $options[ $args['field'] ] : '';
        $type    = isset( $args['type'] ) ? $args['type'] : 'text';
        printf(
            '<input type="%s" name="sds_referral_options[%s]" value="%s" class="regular-text" />',
            esc_attr( $type ),
            esc_attr( $args['field'] ),
            esc_attr( $value )
        );
        if ( ! empty( $args['desc'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['desc'] ) );
        }
    }

    public function field_color( $args ) {
        $options = get_option( 'sds_referral_options', array() );
        $value   = isset( $options[ $args['field'] ] ) ? $options[ $args['field'] ] : '#5B2D8E';
        printf(
            '<input type="text" name="sds_referral_options[%s]" value="%s" class="sds-color-picker" data-default-color="#5B2D8E" />',
            esc_attr( $args['field'] ),
            esc_attr( $value )
        );
    }

    public function field_media( $args ) {
        $options = get_option( 'sds_referral_options', array() );
        $value   = isset( $options[ $args['field'] ] ) ? $options[ $args['field'] ] : '';
        $image   = '';
        if ( $value ) {
            $image = wp_get_attachment_image_url( $value, 'medium' );
        }
        ?>
        <div class="sds-media-upload">
            <input type="hidden" name="sds_referral_options[<?php echo esc_attr( $args['field'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="sds-media-id" />
            <div class="sds-media-preview">
                <?php if ( $image ) : ?>
                    <img src="<?php echo esc_url( $image ); ?>" style="max-width: 200px; height: auto;" />
                <?php endif; ?>
            </div>
            <button type="button" class="button sds-upload-btn">Upload Logo</button>
            <button type="button" class="button sds-remove-btn" <?php echo $value ? '' : 'style="display:none;"'; ?>>Remove</button>
        </div>
        <?php
        if ( ! empty( $args['desc'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['desc'] ) );
        }
    }

    public function field_font_select( $args ) {
        $options = get_option( 'sds_referral_options', array() );
        $value   = isset( $options[ $args['field'] ] ) ? $options[ $args['field'] ] : 'helvetica';
        $fonts   = array(
            'helvetica' => 'Helvetica (Sans-serif)',
            'times'     => 'Times New Roman (Serif)',
            'courier'   => 'Courier (Monospace)',
            'dejavusans' => 'DejaVu Sans (UTF-8 Support)',
        );
        echo '<select name="sds_referral_options[' . esc_attr( $args['field'] ) . ']">';
        foreach ( $fonts as $key => $label ) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr( $key ),
                selected( $value, $key, false ),
                esc_html( $label )
            );
        }
        echo '</select>';
    }

    public function field_code_editor( $args ) {
        $options = get_option( 'sds_referral_options', array() );
        $value   = isset( $options[ $args['field'] ] ) ? $options[ $args['field'] ] : '';
        printf(
            '<textarea name="sds_referral_options[%s]" id="sds_pdf_custom_css" rows="10" class="large-text code">%s</textarea>',
            esc_attr( $args['field'] ),
            esc_textarea( $value )
        );
        if ( ! empty( $args['desc'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['desc'] ) );
        }
    }

    public function sanitize_options( $input ) {
        $sanitized = array();

        $email_fields = array( 'from_email', 'admin_email', 'recipient_email', 'pdf_contact_email', 'pdf_footer_email' );
        $text_fields  = array( 'from_name', 'pdf_company_name', 'pdf_contact_phone', 'pdf_contact_address', 'pdf_footer_website', 'pdf_footer_phone', 'pdf_footer_abn' );

        foreach ( $email_fields as $field ) {
            $sanitized[ $field ] = isset( $input[ $field ] ) ? sanitize_email( $input[ $field ] ) : '';
        }

        foreach ( $text_fields as $field ) {
            $sanitized[ $field ] = isset( $input[ $field ] ) ? sanitize_text_field( $input[ $field ] ) : '';
        }

        // Privacy Policy URL
        $sanitized['privacy_policy_url'] = isset( $input['privacy_policy_url'] ) ? esc_url_raw( $input['privacy_policy_url'] ) : '';

        // Color
        $sanitized['primary_color'] = isset( $input['primary_color'] ) && preg_match( '/^#[a-fA-F0-9]{6}$/', $input['primary_color'] ) ? $input['primary_color'] : '#5B2D8E';

        // Logo (attachment ID)
        $sanitized['pdf_logo'] = isset( $input['pdf_logo'] ) ? absint( $input['pdf_logo'] ) : '';

        // Font
        $allowed_fonts          = array( 'helvetica', 'times', 'courier', 'dejavusans' );
        $sanitized['pdf_font'] = isset( $input['pdf_font'] ) && in_array( $input['pdf_font'], $allowed_fonts, true ) ? $input['pdf_font'] : 'helvetica';

        // Custom CSS - strip any tags but allow CSS
        $sanitized['pdf_custom_css'] = isset( $input['pdf_custom_css'] ) ? wp_strip_all_tags( $input['pdf_custom_css'] ) : '';

        return $sanitized;
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        include SDS_PLUGIN_DIR . 'admin/admin-page.php';
    }
}
