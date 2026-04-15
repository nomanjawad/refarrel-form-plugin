<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SDS_Form {

    public function __construct() {
        add_shortcode( 'sds_referral_form', array( $this, 'render_form' ) );
    }

    public function render_form( $atts ) {
        // Enqueue assets only when shortcode is used
        $this->enqueue_assets();

        $options       = get_option( 'sds_referral_options', array() );
        $primary_color = isset( $options['primary_color'] ) ? $options['primary_color'] : '#5B2D8E';

        ob_start();
        ?>
        <div id="sds-referral-form-wrapper" style="--sds-primary: <?php echo esc_attr( $primary_color ); ?>;">

            <!-- Progress Bar -->
            <div class="sds-progress-bar">
                <div class="sds-progress-step active" data-step="1"><span class="sds-step-num">1</span><span class="sds-step-label">Participant</span></div>
                <div class="sds-progress-step" data-step="2"><span class="sds-step-num">2</span><span class="sds-step-label">Referral</span></div>
                <div class="sds-progress-step" data-step="3"><span class="sds-step-num">3</span><span class="sds-step-label">Representative</span></div>
                <div class="sds-progress-step" data-step="4"><span class="sds-step-num">4</span><span class="sds-step-label">NDIS Plan</span></div>
                <div class="sds-progress-step" data-step="5"><span class="sds-step-num">5</span><span class="sds-step-label">Services</span></div>
                <div class="sds-progress-step" data-step="6"><span class="sds-step-num">6</span><span class="sds-step-label">Goals</span></div>
                <div class="sds-progress-step" data-step="7"><span class="sds-step-num">7</span><span class="sds-step-label">Medical</span></div>
                <div class="sds-progress-step" data-step="8"><span class="sds-step-num">8</span><span class="sds-step-label">Consent</span></div>
            </div>

            <form id="sds-referral-form" novalidate>
                <?php wp_nonce_field( 'sds_form_nonce', 'sds_nonce' ); ?>
                <input type="hidden" name="action" value="sds_submit_form" />

                <?php
                for ( $i = 1; $i <= 8; $i++ ) {
                    $display = ( $i === 1 ) ? '' : 'style="display:none;"';
                    echo '<div class="sds-step" data-step="' . $i . '" ' . $display . '>';
                    include SDS_PLUGIN_DIR . 'templates/form-step-' . $i . '.php';
                    echo '</div>';
                }
                ?>

                <!-- Navigation -->
                <div class="sds-form-nav">
                    <button type="button" class="sds-btn sds-prev" style="display:none;">
                        <span class="dashicons dashicons-arrow-left-alt2"></span> Previous
                    </button>
                    <button type="button" class="sds-btn sds-next">
                        Next <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                    <button type="submit" class="sds-btn sds-submit" style="display:none;">
                        Submit Referral
                    </button>
                </div>
            </form>

            <!-- Loading overlay -->
            <div id="sds-loading" style="display:none;">
                <div class="sds-spinner"></div>
                <p>Generating PDF and sending notifications...</p>
            </div>

            <!-- Success message -->
            <div id="sds-form-success" style="display:none;">
                <div class="sds-success-icon">&#10003;</div>
                <h3>Referral Submitted Successfully!</h3>
                <p>Your referral PDF is downloading. A copy has also been emailed to the relevant parties.</p>
                <button type="button" class="sds-btn sds-new-referral">Submit Another Referral</button>
            </div>

        </div>
        <?php
        return ob_get_clean();
    }

    private function enqueue_assets() {
        wp_enqueue_style( 'sds-form-css', SDS_PLUGIN_URL . 'public/css/sds-form.css', array(), SDS_VERSION );
        wp_enqueue_script( 'sds-signature-pad', SDS_PLUGIN_URL . 'public/js/signature_pad.min.js', array(), '4.2.0', true );
        wp_enqueue_script( 'sds-form-js', SDS_PLUGIN_URL . 'public/js/sds-form.js', array( 'jquery', 'sds-signature-pad' ), SDS_VERSION, true );

        wp_localize_script( 'sds-form-js', 'sdsForm', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'sds_form_nonce' ),
        ) );
    }
}
