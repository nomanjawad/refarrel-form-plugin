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
        <input type="date" id="sds_dob" name="date_of_birth" required />
    </div>
    <div class="sds-field">
        <label for="sds_ndis_number">NDIS Number <span class="required">*</span></label>
        <input type="text" id="sds_ndis_number" name="ndis_number" required />
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
