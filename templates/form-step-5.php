<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h2 class="sds-step-title">5. Services Requested</h2>
<p class="sds-step-desc">Please tick the services required and provide details.</p>

<div class="sds-field-row">
    <div class="sds-field">
        <div class="sds-checkbox-group sds-services-list">
            <label class="sds-checkbox"><input type="checkbox" name="services[]" value="Assistance with Personal Activities" /> Assistance with Personal Activities</label>
            <label class="sds-checkbox"><input type="checkbox" name="services[]" value="Community Participation" /> Community Participation</label>
            <label class="sds-checkbox"><input type="checkbox" name="services[]" value="Domestic Assistance" /> Domestic Assistance</label>
            <label class="sds-checkbox"><input type="checkbox" name="services[]" value="Development of Life Skills (Capacity Building)" /> Development of Life Skills (Capacity Building)</label>
            <label class="sds-checkbox"><input type="checkbox" name="services[]" value="Innovative Community Participation" /> Innovative Community Participation</label>
            <label class="sds-checkbox"><input type="checkbox" name="services[]" value="Community Nursing Care" /> Community Nursing Care</label>
            <label class="sds-checkbox"><input type="checkbox" name="services[]" value="Travel/Transport" /> Travel/Transport</label>
            <label class="sds-checkbox"><input type="checkbox" name="services[]" value="Behaviour Support Implementation" /> Behaviour Support Implementation</label>
            <label class="sds-checkbox"><input type="checkbox" name="services[]" value="Personal Care" /> Personal Care</label>
            <label class="sds-checkbox">
                <input type="checkbox" name="services[]" value="Other" id="sds_service_other_check" /> Other
            </label>
        </div>
        <div id="sds-service-other-wrap" style="display:none; margin-top: 10px;">
            <input type="text" name="service_other_text" id="sds_service_other_text" placeholder="Please specify other service..." />
        </div>
    </div>
</div>

<div class="sds-field-row">
    <div class="sds-field">
        <label for="sds_service_details">Service Details / Goals</label>
        <textarea id="sds_service_details" name="service_details" rows="5" placeholder="Describe the specific services and goals..."></textarea>
    </div>
</div>
