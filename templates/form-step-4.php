<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h2 class="sds-step-title">4. NDIS Plan Details</h2>

<div class="sds-field-row">
    <div class="sds-field">
        <label>Plan Type <span class="required">*</span></label>
        <div class="sds-radio-group">
            <label class="sds-radio"><input type="radio" name="plan_type" value="Agency Managed" required /> Agency Managed</label>
            <label class="sds-radio"><input type="radio" name="plan_type" value="Plan Managed" /> Plan Managed</label>
            <label class="sds-radio"><input type="radio" name="plan_type" value="Self-Managed" /> Self-Managed</label>
        </div>
    </div>
</div>

<div class="sds-field-row sds-two-col">
    <div class="sds-field">
        <label for="sds_plan_start">Plan Start Date <span class="required">*</span></label>
        <input type="date" id="sds_plan_start" name="plan_start_date" required />
    </div>
    <div class="sds-field">
        <label for="sds_plan_end">Plan End Date <span class="required">*</span></label>
        <input type="date" id="sds_plan_end" name="plan_end_date" required />
    </div>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_plan_manager">Plan Manager (if applicable)</label>
        <input type="text" id="sds_plan_manager" name="plan_manager" />
    </div>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_plan_manager_contact">Plan Manager Contact Details</label>
        <input type="text" id="sds_plan_manager_contact" name="plan_manager_contact" />
    </div>
</div>
