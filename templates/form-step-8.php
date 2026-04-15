<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h2 class="sds-step-title">8. Consent & Privacy</h2>

<div class="sds-consent-text">
    <p>I consent to the sharing of relevant information between the referrer and service provider for the purpose of delivering supports under the NDIS.</p>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_consent_name">Participant / Representative Name <span class="required">*</span></label>
        <input type="text" id="sds_consent_name" name="consent_name" required />
    </div>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label>Signature <span class="required">*</span></label>
        <div class="sds-signature-wrapper">
            <canvas id="sds-signature-pad" width="500" height="200"></canvas>
            <input type="hidden" name="signature_data" id="sds_signature_data" />
        </div>
        <button type="button" class="sds-btn-small sds-clear-signature">Clear Signature</button>
        <p class="sds-field-error" id="sds-signature-error" style="display:none;">Please provide a signature.</p>
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
            <input type="checkbox" name="consent_agree" id="sds_consent_agree" value="yes" required />
            I agree to the above consent and privacy statement <span class="required">*</span>
        </label>
    </div>
</div>
