<?php
function ojabooking_get_customer_contact_placeholder()
{
?>
    <div class="form-floating">
        <input id="ojabooking_booking_name" type="string" class="form-control" name="name" maxlength="64" placeholder="<?php _e('Name', 'ojabooking'); ?>" required>
        <label for="ojabooking_booking_name"><?php _e('Name', 'ojabooking'); ?></label>
        <div id="ojabooking_booking_name-invalid-feedback" class="invalid-feedback">
            <?php _e('Please write your name.', 'ojabooking'); ?>
        </div>
    </div>
    <div class="form-floating">
        <input id="ojabooking_booking_email" type="email" class="form-control email_address" name="email" placeholder="<?php _e('Email', 'ojabooking'); ?>" pattern="^[+a-zA-Z0-9\._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$" required>
        <label for="ojabooking_booking_email"><?php _e('Email', 'ojabooking'); ?></label>
        <div id="ojabooking_booking_email-invalid-feedback" class="invalid-feedback">
            <?php _e('Please write correct email.', 'ojabooking'); ?>
        </div>
    </div>
    <div class="form-floating">
        <input id="ojabooking_booking_tel" type="tel" class="form-control" name="tel" placeholder="<?php _e('Phone number', 'ojabooking'); ?>" pattern="^\+?([0-9]{3})?\)?[-. ]?([0-9]{3})[-. ]?([0-9]{3})[-. ]?([0-9]{3})$" required>
        <label for="ojabooking_booking_tel"><?php _e('Phone number', 'ojabooking'); ?></label>
        <div id="ojabooking_booking_tel-invalid-feedback" class="invalid-feedback">
            <?php _e('Please write correct phone number. (+421 123 456 789)', 'ojabooking'); ?>
        </div>
    </div>
    <div id="private-party-contact-details" class="hidden">
        <div class="form-floating">
            <input id="ojabooking_booking_school_institution" type="string" class="form-control" name="school_name_department" placeholder="<?php _e('School name/Institution', 'ojabooking'); ?>">
            <label for="ojabooking_booking_school_institution"><?php _e('School name/Institution', 'ojabooking'); ?></label>
            <div id="ojabooking_booking_school_institution-invalid-feedback" class="invalid-feedback">
                <?php _e('Please write School name/Institution.', 'ojabooking'); ?>
            </div>
        </div>
        <div class="form-floating">
            <input id="ojabooking_booking_class_department" type="string" class="form-control" name="class_department" placeholder="<?php _e('Class/Department', 'ojabooking'); ?>">
            <label for="ojabooking_booking_class_department"><?php _e('Class/Department', 'ojabooking'); ?></label>
            <div id="ojabooking_booking_class_department-invalid-feedback" class="invalid-feedback">
                <?php _e('Please write Class/Department.', 'ojabooking'); ?>
            </div>
        </div>
    </div>
<?php
}

function ojabooking_get_customer_privacy_placeholder()
{
    $ojabooking_terms_and_conditions_page = get_option('ojabooking_terms_and_conditions', '');
    $privacy_page = get_privacy_policy_url();
?>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="termsCheck" name="termsCheck" required>
        <label class="form-check-label" for="termsCheck">
            <?php printf(wp_kses(__('By ticking this box, I confirm that I have read the <a href="%1$s">Terms and Conditions</a>.', 'ojabooking'), array('a' => array('href' => array()))), esc_url(get_permalink($ojabooking_terms_and_conditions_page))); ?></p>
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="privacyCheck" name="privacyCheck" required>
        <label class="form-check-label" for="privacyCheck">
            <?php printf(wp_kses(__('By ticking this box, I confirm that I have read the <a href="%1$s">Privacy Policy</a>.', 'ojabooking'), array('a' => array('href' => array()))), esc_url($privacy_page)); ?>
        </label>
    </div>
<?php
}
