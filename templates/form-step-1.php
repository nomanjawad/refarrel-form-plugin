<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h2 class="sds-step-title">1. Participant Details</h2>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_full_name">Full Name <span class="required">*</span></label>
        <input type="text" id="sds_full_name" name="full_name" required />
    </div>
</div>

<div class="sds-field-row sds-two-col">
    <div class="sds-field">
        <label for="sds_dob">Date of Birth <span class="required">*</span></label>
        <div class="sds-dob-wrap">
            <input type="text" id="sds_dob" name="date_of_birth" placeholder="DD/MM/YYYY" pattern="\d{2}/\d{2}/\d{4}" maxlength="10" inputmode="numeric" required autocomplete="bday" />
            <input type="date" id="sds_dob_picker" class="sds-dob-picker" tabindex="-1" aria-hidden="true" />
            <button type="button" class="sds-dob-btn" id="sds_dob_btn" aria-label="Open calendar">
                <span class="dashicons dashicons-calendar-alt"></span>
            </button>
        </div>
        <small class="sds-field-hint">Type DD/MM/YYYY or use the calendar</small>
    </div>
    <div class="sds-field">
        <label>Gender / Sex <span class="required">*</span></label>
        <div class="sds-radio-group">
            <label class="sds-radio"><input type="radio" name="gender" value="Male" required /> Male</label>
            <label class="sds-radio"><input type="radio" name="gender" value="Female" /> Female</label>
            <label class="sds-radio"><input type="radio" name="gender" value="Other" /> Other</label>
            <label class="sds-radio"><input type="radio" name="gender" value="Prefer not to say" /> Prefer not to say</label>
        </div>
    </div>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_address">Address <span class="required">*</span></label>
        <input type="text" id="sds_address" name="address" required />
    </div>
</div>

<div class="sds-field-row sds-two-col">
    <div class="sds-field">
        <label for="sds_phone">Phone Number <span class="required">*</span></label>
        <input type="tel" id="sds_phone" name="phone_number" required />
    </div>
    <div class="sds-field">
        <label for="sds_email">Email</label>
        <input type="email" id="sds_email" name="email" />
    </div>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label>Preferred Contact Method</label>
        <div class="sds-checkbox-group">
            <label class="sds-checkbox"><input type="checkbox" name="preferred_contact[]" value="Phone" /> Phone</label>
            <label class="sds-checkbox"><input type="checkbox" name="preferred_contact[]" value="Email" /> Email</label>
            <label class="sds-checkbox"><input type="checkbox" name="preferred_contact[]" value="SMS" /> SMS</label>
        </div>
    </div>
</div>
