<?php
function oja_get_template_part_booking_filter()
{
    $date_today =  date("Y-m-d");
    $date_max = date("Y-m-d", strtotime("+7 months", strtotime($date_today)));
    $selected_day = isset($_GET['date']) ? $_GET['date'] : $date_today;
    $book_lang = $_GET['book_lang'];
    $event_id = $_GET['event_id'];

    $languages = get_terms(array(
        'taxonomy' => 'oja_languages',
        'hide_empty' => false,
    ));

    $use_languages = get_option('oja_use_booking_languages', 0);
    $default_language = get_option('oja_default_booking_language', '');
    if (!isset($book_lang)) $book_lang = $default_language;
?>
    <?php oja_get_alert_placeholder() ?>

    <form id="booking-filter" class="row g-3 needs-validation">
        <div class="col-auto">
            <div class="input-group" title="<?php _e('Date', 'oja'); ?>">
                <label class="input-group-text" for="date"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar4-event" viewBox="0 0 16 16">
                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v1h14V3a1 1 0 0 0-1-1H2zm13 3H1v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V5z" />
                        <path d="M11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z" />
                    </svg></label>
                <input type="date" name="date" id="date" class="form-control" value="<?php echo $selected_day; ?>" min="<?php echo $date_today; ?>" max="<?php echo $date_max; ?>">
            </div>

            </label>
        </div>

        <?php if ($use_languages) : ?>
            <div class="col-auto">
                <div class="input-group" title="<?php _e('Language', 'oja'); ?>">
                    <label class="input-group-text" for="booking-language"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-translate" viewBox="0 0 16 16">
                            <path d="M4.545 6.714 4.11 8H3l1.862-5h1.284L8 8H6.833l-.435-1.286H4.545zm1.634-.736L5.5 3.956h-.049l-.679 2.022H6.18z" />
                            <path d="M0 2a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v3h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-3H2a2 2 0 0 1-2-2V2zm2-1a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H2zm7.138 9.995c.193.301.402.583.63.846-.748.575-1.673 1.001-2.768 1.292.178.217.451.635.555.867 1.125-.359 2.08-.844 2.886-1.494.777.665 1.739 1.165 2.93 1.472.133-.254.414-.673.629-.89-1.125-.253-2.057-.694-2.82-1.284.681-.747 1.222-1.651 1.621-2.757H14V8h-3v1.047h.765c-.318.844-.74 1.546-1.272 2.13a6.066 6.066 0 0 1-.415-.492 1.988 1.988 0 0 1-.94.31z" />
                        </svg></label>
                    <select class="form-select" id="booking-language" name="booking-language">
                        <?php foreach ($languages as $language) : ?>
                            <option value="<?php echo $language->term_id; ?>" <?php selected($book_lang, $language->term_id); ?>><?php echo $language->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <div class="col">
            <div class="input-group" title="<?php _e('Select participants', 'oja'); ?>">
                <button class="btn btn-outline-primary" type="button" id="button-select-group" data-bs-toggle="modal" data-bs-target="#booking-group-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16" style="height: 16px;width: 16px;">
                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                        <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z" />
                        <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z" />
                    </svg></button>
                <label class="input-group-text selected-group" placeholder="<?php _e('Select participants', 'oja'); ?>" aria-label="Example text with button addon" aria-describedby="button-select-group"></label>
            </div>
        </div>

        <div id="booking-group-modal" class="modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php _e('Select participants', 'oja'); ?></h5>
                    </div>
                    <div class="modal-body">
                        <?php oja_get_template_part_select_categories(); ?>
                    </div>
                    <div class="modal-footer">
                        <button id="booking-group-select-btn" type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php _e('Select', 'oja'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <div id="booking-contact-modal" class="modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php _e('Contact', 'oja'); ?></h5>
                    </div>
                    <div class="modal-body">
                        <?php oja_get_customer_contact_placeholder(); ?>
                        <?php oja_get_customer_privacy_placeholder(); ?>


                        <input id="booking-event-id" type="hidden" name="event_id" value="">
                        <input id="booking-term" type="hidden" name="term" value="">
                    </div>
                    <div class="modal-footer">
                        <button id="booking-contact-modal-btn" class="btn btn-primary" type="submit" data-bs-dismiss="modal"><?php _e('Book', 'oja'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php
}

function oja_get_template_part_select_categories()
{
    $group = $_GET['group'];
    $categories = get_terms(array(
    'taxonomy' => 'oja_price_categories',
    'hide_empty' => false,
    'meta_key' => 'private_party',
    'meta_compare' => 'NOT EXISTS'
    ));
    if(!isset($categories)){
        echo "<p>Please create any price categories</p>";
        return;
    }
    $default_category = get_option('oja_default_price_category', '');
    //private_party
    ?>
    <?php foreach ($categories as $category) :
        $group_count = 0;
        if ($group && $group["$category->term_id"])
            $group_count = (int)$group["$category->term_id"];
        elseif (!$group && $category->term_id == $default_category)
            $group_count = 1;
    ?>
        <div id="oja_booking_category_<?php echo $key; ?>" class="form-floating price_category">
            <input type="number" id="oja_group_<?php echo $category->name; ?>" class="form-control" name="group[<?php echo $category->term_id; ?>]" value="<?php echo $group_count; ?>" min="0" max="100" size="3">
            <label for="oja_group_<?php echo $category->name; ?>"><?php echo $category->name; ?></label>
        </div>
    <?php endforeach; ?>

    <?php
    $categories = get_terms(array(
        'taxonomy' => 'oja_price_categories',
        'hide_empty' => false,
        'meta_key'   => 'private_party',
        'meta_compare' => 'EXISTS'
    ));
    ?>
    <div id="private-party-groups">
        <?php foreach ($categories as $category) : ?>
            <div id="oja_booking_category_<?php echo $key; ?>" class="private_party price_category">
                <button class="btn btn-secondary private-party-select" data-bs-dismiss="modal"><?php printf(esc_html__('Select %1$s', 'oja'), $category->name); ?></button>
                <label class="d-none" for="oja_group_<?php echo $category->name; ?>"><?php echo $category->name; ?></label>
                <input type="hidden" id="oja_group_<?php echo $category->name; ?>" name="group[<?php echo $category->term_id; ?>]" value="0">
            </div>
        <?php endforeach; ?>
    </div>
<?php
}


function oja_get_template_part_booking_list()
{
    oja_booking_enqueue();
?>
    <template id="booking-list-template">
        <section class="booking container bg-content shadow rounded-3 p-3 mb-2">
            <div class="row">
                <div class="col">
                    <h4 class="title"><a href="#"></a></h4>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z" />
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z" />
                    </svg><span class="time" title="<?php _e('Term', 'oja'); ?>"></span>
                </div>
                <div class="col">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                            <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z" />
                            <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z" />
                        </svg>
                        <span class="vacancies" title="<?php _e('Vacancies', 'oja'); ?>"></span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0z" />
                            <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1h-.003zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195l.054.012z" />
                            <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083c.058-.344.145-.678.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1H1z" />
                            <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 5.982 5.982 0 0 1 3.13-1.567z" />
                        </svg>
                        <span class="price" title="<?php _e('Your price', 'oja'); ?>"></span>
                    </div>
                </div>
                <div class="col align-self-center">
                    <button class="btn btn-primary book" data-bs-toggle="modal" data-bs-target="#booking-contact-modal" event_id="" term=""><?php _e('Book', 'oja'); ?></button>
                </div>
            </div>
        </section>
    </template>
<?php
}
