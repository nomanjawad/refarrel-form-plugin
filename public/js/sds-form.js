(function($) {
    'use strict';

    var currentStep = 1;
    var totalSteps = 8;
    var signaturePad = null;

    $(document).ready(function() {
        bindNavigation();
        bindOtherService();
        bindFormSubmit();
        bindNewReferral();
        setDefaultDates();
        bindDateFields();
    });

    function setDefaultDates() {
        var today = new Date().toISOString().split('T')[0];
        var $referralDate = $('#sds_referral_date');
        var $consentDate = $('#sds_consent_date');
        if ($referralDate.length && !$referralDate.val()) {
            $referralDate.val(today);
        }
        if ($consentDate.length && !$consentDate.val()) {
            $consentDate.val(today);
        }
    }

    function initSignaturePad() {
        if (signaturePad) return; // Already initialized

        var canvas = document.getElementById('sds-signature-pad');
        if (!canvas) return;

        // Set explicit canvas dimensions from the visible container
        var wrapper = canvas.parentElement;
        var wrapperWidth = wrapper.offsetWidth || 500;
        var canvasHeight = 200;

        canvas.width = wrapperWidth;
        canvas.height = canvasHeight;
        canvas.style.width = wrapperWidth + 'px';
        canvas.style.height = canvasHeight + 'px';

        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(250, 250, 250)',
            penColor: 'rgb(0, 0, 0)'
        });

        // Fill background immediately so it's not transparent
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = 'rgb(250, 250, 250)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Resize canvas on window resize
        function resizeCanvas() {
            var ww = wrapper.offsetWidth || 500;
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = ww * ratio;
            canvas.height = canvasHeight * ratio;
            canvas.style.width = ww + 'px';
            canvas.style.height = canvasHeight + 'px';
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }

        window.addEventListener('resize', resizeCanvas);

        // Clear signature button
        $('.sds-clear-signature').on('click', function() {
            signaturePad.clear();
            $('#sds_signature_data').val('');
            $('#sds-signature-error').hide();
        });
    }

    function bindNavigation() {
        $('.sds-next').on('click', function() {
            if (validateStep(currentStep)) {
                goToStep(currentStep + 1);
            }
        });

        $('.sds-prev').on('click', function() {
            goToStep(currentStep - 1);
        });

        // Allow clicking on completed steps in progress bar
        $('.sds-progress-step').on('click', function() {
            var step = parseInt($(this).data('step'));
            if (step < currentStep) {
                goToStep(step);
            }
        });
    }

    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;

        // Hide current step
        $('.sds-step[data-step="' + currentStep + '"]').fadeOut(200, function() {
            // Show target step
            $('.sds-step[data-step="' + step + '"]').fadeIn(200, function() {
                // Initialize signature pad when step 8 becomes visible
                if (step === 8) {
                    initSignaturePad();
                }
            });
        });

        // Update progress bar
        $('.sds-progress-step').each(function() {
            var s = parseInt($(this).data('step'));
            $(this).removeClass('active completed');
            if (s === step) {
                $(this).addClass('active');
            } else if (s < step) {
                $(this).addClass('completed');
            }
        });

        currentStep = step;

        // Update navigation buttons
        if (currentStep === 1) {
            $('.sds-prev').hide();
        } else {
            $('.sds-prev').show();
        }

        if (currentStep === totalSteps) {
            $('.sds-next').hide();
            $('.sds-submit').show();
        } else {
            $('.sds-next').show();
            $('.sds-submit').hide();
        }

        // Scroll to top of form
        $('html, body').animate({
            scrollTop: $('#sds-referral-form-wrapper').offset().top - 20
        }, 300);
    }

    function validateStep(step) {
        var $step = $('.sds-step[data-step="' + step + '"]');
        var isValid = true;

        // Clear previous errors
        $step.find('.sds-invalid').removeClass('sds-invalid');
        $step.find('.sds-field-error').hide();

        // Validate required fields
        $step.find('input[required], textarea[required], select[required]').each(function() {
            var $field = $(this);

            if ($field.attr('type') === 'radio') {
                // Check if any radio in the group is selected
                var name = $field.attr('name');
                if (!$step.find('input[name="' + name + '"]:checked').length) {
                    $field.closest('.sds-radio-group').addClass('sds-invalid');
                    isValid = false;
                }
                return;
            }

            if ($field.attr('type') === 'checkbox') {
                if (!$field.is(':checked')) {
                    $field.addClass('sds-invalid');
                    isValid = false;
                }
                return;
            }

            if (!$field.val() || !$field.val().trim()) {
                $field.addClass('sds-invalid');
                isValid = false;
            }

            // Email validation
            if ($field.attr('type') === 'email' && $field.val()) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test($field.val())) {
                    $field.addClass('sds-invalid');
                    isValid = false;
                }
            }
        });

        // Special: signature validation on step 8
        if (step === 8 && signaturePad) {
            if (signaturePad.isEmpty()) {
                $('#sds-signature-error').show();
                isValid = false;
            } else {
                $('#sds-signature-error').hide();
            }
        }

        // Scroll to first error
        if (!isValid) {
            var $firstError = $step.find('.sds-invalid').first();
            if ($firstError.length) {
                $('html, body').animate({
                    scrollTop: $firstError.offset().top - 100
                }, 300);
            }
        }

        return isValid;
    }

    function bindOtherService() {
        $('#sds_service_other_check').on('change', function() {
            if ($(this).is(':checked')) {
                $('#sds-service-other-wrap').slideDown(200);
            } else {
                $('#sds-service-other-wrap').slideUp(200);
                $('#sds_service_other_text').val('');
            }
        });
    }

    function bindFormSubmit() {
        $('#sds-referral-form').on('submit', function(e) {
            e.preventDefault();

            // Final validation on all steps
            for (var i = 1; i <= totalSteps; i++) {
                if (!validateStep(i)) {
                    goToStep(i);
                    return;
                }
            }

            // Capture signature data - use JPEG at lower quality to reduce size
            if (signaturePad && !signaturePad.isEmpty()) {
                var sigCanvas = document.getElementById('sds-signature-pad');
                // Create a temp canvas with white background for JPEG
                var tmpCanvas = document.createElement('canvas');
                tmpCanvas.width = sigCanvas.width;
                tmpCanvas.height = sigCanvas.height;
                var tmpCtx = tmpCanvas.getContext('2d');
                tmpCtx.fillStyle = '#fafafa';
                tmpCtx.fillRect(0, 0, tmpCanvas.width, tmpCanvas.height);
                tmpCtx.drawImage(sigCanvas, 0, 0);
                $('#sds_signature_data').val(tmpCanvas.toDataURL('image/jpeg', 0.5));
            }

            // Show loading
            $('#sds-loading').show();

            var formData = $(this).serialize();

            $.ajax({
                url: sdsForm.ajaxurl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                timeout: 120000, // 2 minute timeout for PDF generation
                success: function(response) {
                    $('#sds-loading').hide();

                    if (response.success) {
                        // Download PDF
                        if (response.data && response.data.pdf_base64) {
                            downloadPDF(response.data.pdf_base64, response.data.filename);
                        }

                        // Show success message
                        $('#sds-referral-form').hide();
                        $('.sds-progress-bar').hide();
                        $('#sds-form-success').fadeIn(300);
                    } else {
                        var msg = response.data && response.data.message ? response.data.message : 'An error occurred. Please try again.';
                        alert(msg);
                    }
                },
                error: function(xhr, status, error) {
                    $('#sds-loading').hide();
                    var msg = 'An error occurred. ';
                    if (status === 'timeout') {
                        msg += 'The request timed out. Please try again.';
                    } else if (xhr.status === 413) {
                        msg += 'The form data is too large. Please try clearing and redrawing your signature.';
                    } else if (xhr.status === 0) {
                        msg += 'Could not reach the server. Please check your connection.';
                    } else if (status === 'parsererror') {
                        // Server returned 200 but response wasn't valid JSON (PHP error/warning)
                        var raw = xhr.responseText || '';
                        if (raw.length > 300) raw = raw.substring(0, 300);
                        msg += 'The server returned an unexpected response. Details: ' + raw;
                    } else {
                        msg += 'Server error (' + xhr.status + '). Please try again or contact support.';
                    }
                    alert(msg);
                }
            });
        });
    }

    function downloadPDF(base64Data, filename) {
        try {
            var byteCharacters = atob(base64Data);
            var byteNumbers = new Array(byteCharacters.length);
            for (var i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            var byteArray = new Uint8Array(byteNumbers);
            var blob = new Blob([byteArray], { type: 'application/pdf' });

            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename || 'SDS-Referral-Form.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        } catch (e) {
            console.error('PDF download error:', e);
        }
    }

    function bindNewReferral() {
        $('.sds-new-referral').on('click', function() {
            // Reset form
            $('#sds-referral-form')[0].reset();
            if (signaturePad) {
                signaturePad.clear();
            }
            $('#sds_signature_data').val('');

            // Reset UI
            currentStep = 1;
            $('.sds-step').hide();
            $('.sds-step[data-step="1"]').show();
            $('.sds-progress-step').removeClass('active completed');
            $('.sds-progress-step[data-step="1"]').addClass('active');
            $('.sds-prev').hide();
            $('.sds-next').show();
            $('.sds-submit').hide();

            $('#sds-form-success').hide();
            $('#sds-referral-form').show();
            $('.sds-progress-bar').show();

            setDefaultDates();

            $('html, body').animate({
                scrollTop: $('#sds-referral-form-wrapper').offset().top - 20
            }, 300);
        });
    }

    function bindDateFields() {
        // Open native date picker when clicking anywhere on the field
        $(document).on('click', '#sds-referral-form input[type="date"]', function() {
            if (typeof this.showPicker === 'function') {
                try { this.showPicker(); } catch(e) {}
            }
        });
    }

    // Clear validation on input
    $(document).on('input change', '#sds-referral-form input, #sds-referral-form textarea, #sds-referral-form select', function() {
        $(this).removeClass('sds-invalid');
    });

})(jQuery);
