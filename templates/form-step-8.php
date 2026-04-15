<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h2 class="sds-step-title">8. Consent & Privacy</h2>

<div class="sds-consent-text">
    <p>I consent to Sydney Disability Support Pty Ltd collecting and using my information to process this referral.</p>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_consent_name">Participant / Representative Name <span class="required">*</span></label>
        <input type="text" id="sds_consent_name" name="consent_name" required />
    </div>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_signature_text">Signature (Type your full name) <span class="required">*</span></label>
        <input type="text" id="sds_signature_text" name="signature_text" required placeholder="Type your full name as signature" />
    </div>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_consent_date">Date <span class="required">*</span></label>
        <input type="date" id="sds_consent_date" name="consent_date" required />
    </div>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label class="sds-checkbox">
            <input type="checkbox" name="consent_privacy" id="sds_consent_privacy" value="yes" required />
            I agree to the <a href="<?php echo esc_url( $privacy_policy_url ); ?>" target="_blank" rel="noopener noreferrer">Privacy Policy</a> and consent to my information being used for referral purposes. <span class="required">*</span>
        </label>
    </div>
</div>
