<?php
function oja_get_customer_contact_placeholder()
{
?>
    <div class="form-floating">
        <input id="oja_booking_name" type="string" class="form-control" name="name" maxlength="64" placeholder="<?php _e('Name', 'oja'); ?>" required>
        <label for="oja_booking_name"><?php _e('Name', 'oja'); ?></label>
        <div id="oja_booking_name-invalid-feedback" class="invalid-feedback">
            <?php _e('Please write your name.', 'oja'); ?>
        </div>
    </div>
    <div class="form-floating">
        <input id="oja_booking_email" type="email" class="form-control email_address" name="email" placeholder="<?php _e('Email', 'oja'); ?>" required>
        <label for="oja_booking_email"><?php _e('Email', 'oja'); ?></label>
        <div id="oja_booking_email-invalid-feedback" class="invalid-feedback">
            <?php _e('Please write correct email.', 'oja'); ?>
        </div>
    </div>
<?php
}

function oja_get_customer_privacy_placeholder()
{
?>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="termsCheck" name="termsCheck" required>
        <label class="form-check-label" for="termsCheck">
            <?php printf(wp_kses(__('By ticking this box, I confirm that I have read the <a href="%1$s">Terms and Conditions</a>.', 'oja'), array('a' => array('href' => array()))), esc_url(get_permalink($terms_page_id))); ?></p>
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="privacyCheck" name="privacyCheck" required>
        <label class="form-check-label" for="privacyCheck">
            <?php printf(wp_kses(__('By ticking this box, I confirm that I have read the <a href="%1$s">Privacy Policy</a>.', 'oja'), array('a' => array('href' => array()))), esc_url(get_permalink($privacy_page_id))); ?>
        </label>
    </div>
<?php
}
