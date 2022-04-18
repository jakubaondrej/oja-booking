<?php
class Oja_Booking_Terms_Admin_Page
{
    /**
     * Constructor.
     */
    function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    /**
     * Registers a new settings page under Settings.
     */
    function admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=oja_event',
            __('Booking Terms', 'oja'),
            __('Booking Terms', 'oja'),
            'edit_published_pages',
            'oja_booking_terms',
            array(
                $this,
                'settings_page'
            )
        );
        
    }

    /**
     * Settings page display callback.
     */
    function settings_page()
    {
        $this->oja_booking_save_options();
        $action       = 'oja_bookings_admin-search';
        $nonce        = 'oja_bookings_admin-search-nonce';

        $is_nonce_set   = isset($_GET[$nonce]);
        $is_valid_nonce = false;

        if ($is_nonce_set) {
            $is_valid_nonce = wp_verify_nonce($_GET[$nonce], $action);
        }


        $paged = filter_input(INPUT_GET, 'paged') ?? 1;
        $s = filter_input(INPUT_GET, 's');
        $date_from = filter_input(INPUT_GET, 'date_from') ?? "";
        $date_to = filter_input(INPUT_GET, 'date_to') ?? "";
        $term_id = filter_input(INPUT_GET, 'term_id') ?? "";
        if ((!$is_nonce_set || !$is_valid_nonce) && (!empty($s) || !empty($date_from) || !empty($date_to) || !empty($term_id))) {
            echo "BUSTED!";
            die();
        }
        $terms = oja_get_terms($paged, $s, $date_from, $date_to, $term_id, 15);
        $terms_count = $terms['terms_count'];
        $booking_uri_nonce = wp_create_nonce($action);
        $booking_uri = add_query_arg(array('page' => 'oja_booking', 'post_type' => 'oja_event', $nonce => $booking_uri_nonce), admin_url('edit.php'));
?>
        <div class="wrap">
            <h1><?php _e('Terms', 'oja'); ?></h1>
            <form id="posts-filter" method="get">

                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php _e('Search', 'oja'); ?>:</label>
                    <input type="search" id="post-search-input" name="s" value="<?php echo $s; ?>">
                    <input type="submit" id="search-submit" class="button" value="<?php _e('Search', 'oja'); ?>">
                </p>

                <input type="hidden" name="post_type" value="oja_event">
                <input type="hidden" name="page" class="post_type_page" value="oja_booking_terms">
                <?php wp_nonce_field($action, $nonce); ?>



                <div class="tablenav top">
                    <div class="alignleft actions">
                        <label for="filter-by-date-from"><?php _e('From', 'oja'); ?></label>
                        <input id="filter-by-date-from" type="date" value="<?php echo $date_from; ?>" name="date_from">
                        <label for="filter-by-date-to"><?php _e('to', 'oja'); ?></label>
                        <input id="filter-by-date-to" type="date" value="<?php echo $date_to; ?>" name="date_to">

                        <input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php _e('Filter', 'oja'); ?>">
                    </div>
                    <div class="tablenav-pages one-page"><span class="displaying-num"><?php esc_html_e("$terms_count terms", 'oja'); ?></span>
                        <?php oja_admin_pagination((int)$terms['pages'], (int)$paged); ?>
                    </div>
                    <br class="clear">
                </div>
                <table class="wp-list-table widefat fixed striped table-view-list posts" cellspacing="2">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column" scope="col">
                                <label class="screen-reader-text" for="cb-select-all-1"><?php _e("Select all", 'oja'); ?></label>
                                <input id="cb-select-all-1" type="checkbox">
                            </td>
                            <th id="event_name" class="manage-column column-event_name" scope="col" style="width: 25%;"><?php _e('Event name', 'oja'); ?></th>
                            <th id="term" class="manage-column column-term" scope="col"><?php _e('Term', 'oja'); ?></th>
                            <th id="booking_count" class="manage-column column-booking_count num" scope="col"><?php _e('Booking count', 'oja'); ?></th>
                            <th id="group_size" class="manage-column column-group_size num" scope="col"><?php _e('Group size', 'oja'); ?></th>
                            <th id="accepted_booking_count" class="manage-column column-accepted_booking_count num" scope="col"><?php _e('Accepted booking count', 'oja'); ?></th>
                            <th id="accepted_group_size" class="manage-column column-accepted_group_size num" scope="col"><?php _e('Accepted group size', 'oja'); ?></th>

                        </tr>
                    </thead>

                    <tfoot>
                        <tr>

                            <td id="cb" class="manage-column column-cb check-column" scope="col">
                                <label class="screen-reader-text" for="cb-select-all-1"><?php _e("Select all", 'oja'); ?></label>
                                <input id="cb-select-all-1" type="checkbox">
                            </td>
                            <th id="event_name" class="manage-column column-event_name" scope="col"><?php _e('Event name', 'oja'); ?></th>
                            <th id="term" class="manage-column column-term" scope="col"><?php _e('Term', 'oja'); ?></th>
                            <th id="booking_count" class="manage-column column-booking_count num" scope="col"><?php _e('Booking count', 'oja'); ?></th>
                            <th id="group_size" class="manage-column column-group_size num" scope="col"><?php _e('Group size', 'oja'); ?></th>
                            <th id="accepted_booking_count" class="manage-column column-accepted_booking_count num" scope="col"><?php _e('Accepted booking count', 'oja'); ?></th>
                            <th id="accepted_group_size" class="manage-column column-accepted_group_size num" scope="col"><?php _e('Accepted group size', 'oja'); ?></th>

                        </tr>
                    </tfoot>

                    <tbody>
                        <?php foreach ($terms['terms'] as $term) : ?>
                            <tr id="post-<?php echo $term->ID; ?>" class="iedit author-self level-0 post-<?php echo $term->ID; ?> type-post status-publish format-standard">
                                <th scope="row" class="check-column">
                                    <label class="screen-reader-text" for="cb-select-<?php echo $term->ID; ?>">
                                        <?php esc_html_e("Select $term->event_name", 'oja'); ?>
                                    </label>
                                    <input id="cb-select-<?php echo $term->ID; ?>" type="checkbox" name="post[]" value="<?php echo $term->ID; ?>">
                                </th>
                                <td class="title column-event_name has-row-actions column-primary event_name" data-colname="<?php _e('Event name', 'oja'); ?>">
                                    <strong>
                                        <a class="row-title" href="<?php echo get_edit_post_link($term->event_id); ?>" aria-label="<?php esc_html_e("„$term->event_name“ (Open)", 'oja'); ?> ">
                                            <?php echo $term->event_name; ?>
                                        </a>
                                    </strong>
                                    <div class="row-actions">
                                        <span><a href="<?php echo add_query_arg(array('term_id' => $term->ID), $booking_uri); ?>"><?php _e('Show bookings', 'oja'); ?></a> |</span>
                                        <span><a href="<?php echo get_edit_post_link($term->event_id); ?>"><?php _e('Edit Event', 'oja'); ?></a></span>
                                    </div>
                                </td>
                                <td class="term column-term" data-colname="<?php _e('Term', 'oja'); ?>">
                                    <a class="row-title" href="<?php echo add_query_arg(array('term_id' => $term->ID), $booking_uri); ?>" aria-label="<?php esc_html_e("„$term->event_name“ (Open)", 'oja'); ?> ">
                                        <?php echo oja_get_local_date_time($term->term); ?>
                                    </a>
                                </td>
                                <td class="booking_count column-booking_count num" data-colname="<?php _e('Booking count', 'oja'); ?>"><?php echo $term->booking_count; ?></td>
                                <td class="group_size column-group_size num" data-colname="<?php _e('Group size', 'oja'); ?>"><?php echo $term->group_size; ?></td>
                                <td class="accepted_booking_count column-accepted_booking_count num" data-colname="<?php _e('Accepted booking count', 'oja'); ?>"><?php echo $term->accepted_booking_count; ?></td>
                                <td class="accepted_group_size column-accepted_group_size num" data-colname="<?php _e('Accepted group size', 'oja'); ?>"><?php echo $term->accepted_group_size; ?></td>

                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
                <div class="tablenav bottom">
                    <div class="tablenav-pages one-page"><span class="displaying-num"><?php esc_html_e("$terms_count terms", 'oja'); ?></span>
                        <?php oja_admin_pagination((int)$terms['pages'], (int)$paged); ?>
                    </div>
                    <br class="clear">
                </div>

            </form>
        </div>
<?php
    }

    /**
     * Save options
     */
    function oja_booking_save_options()
    {
        $message = null;
        $type = null;
    }
}
if (current_user_can('edit_published_pages')) {
    new Oja_Booking_Terms_Admin_Page;
}
