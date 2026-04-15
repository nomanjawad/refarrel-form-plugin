<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h2 class="sds-step-title">2. Referral Information</h2>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_referral_date">Referral Date <span class="required">*</span></label>
        <input type="date" id="sds_referral_date" name="referral_date" required />
    </div>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_referred_by">Referred By (Name & Organisation) <span class="required">*</span></label>
        <input type="text" id="sds_referred_by" name="referred_by" required />
    </div>
</div>

<div class="sds-field-row sds-two-col">
    <div class="sds-field">
        <label for="sds_referrer_phone">Referrer Contact Number</label>
        <input type="tel" id="sds_referrer_phone" name="referrer_contact_number" />
    </div>
    <div class="sds-field">
        <label for="sds_referrer_email">Referrer Email</label>
        <input type="email" id="sds_referrer_email" name="referrer_email" />
    </div>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_relationship">Relationship to Participant <span class="required">*</span></label>
        <input type="text" id="sds_relationship" name="relationship_to_participant" required />
    </div>
</div>
