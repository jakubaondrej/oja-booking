<?php
class Ojabooking_Booking_Admin_Page
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
            'edit.php?post_type=ojabooking_event',
            __('Booking', 'ojabooking'),
            __('Booking', 'ojabooking'),
            'edit_published_pages',
            'ojabooking_booking',
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
        $this->ojabooking_booking_save_options();
        settings_errors('ojabooking_update_booking_status');
        $action       = 'ojabooking_bookings_admin-search';
        $nonce        = 'ojabooking_bookings_admin-search-nonce';

        $is_nonce_set   = isset($_GET[$nonce]);
        $is_valid_nonce = false;

        if ($is_nonce_set) {
            $is_valid_nonce = wp_verify_nonce($_GET[$nonce], $action);
        }

        $update_uri_nonce = wp_create_nonce('ojabooking_update_booking_status');

        $nonce_value=wp_create_nonce($action); // wp_nonce_field($action, $nonce); 

        $paged = filter_input(INPUT_GET, 'paged') ?? 1;
        $s = filter_input(INPUT_GET, 's');
        $date_from = filter_input(INPUT_GET, 'date_from') ?? "";
        $date_to = filter_input(INPUT_GET, 'date_to') ?? "";
        $booking_id = filter_input(INPUT_GET, 'booking_id') ?? "";
        $term_id = filter_input(INPUT_GET, 'term_id') ?? "";
        $status = filter_input(INPUT_GET, 'status') ?? "";
        if ((!$is_nonce_set || !$is_valid_nonce) && (!empty($s) || !empty($date_from) || !empty($date_to) || !empty($term_id) || !empty($status))) {
            echo "BUSTED!";
            die();
        }
        $bookings = ojabooking_get_bookings($paged, $s, $date_from, $date_to, $term_id,  $status, 15);
        $bookings_count = $bookings['booking_count'];
        $terms_uri_nonce = wp_create_nonce($action);
        $terms_uri = add_query_arg(array('page' => 'ojabooking_booking_terms', 'post_type' => 'ojabooking_event', $nonce => $terms_uri_nonce), admin_url('edit.php'));
        $update_uri_nonce = wp_create_nonce('ojabooking_update_booking_status');
        $update_status_uri = add_query_arg(
            array(
                'page' => 'ojabooking_booking',
                'post_type' => 'ojabooking_event',
                'ojabooking_update_booking_status-nonce' => $update_uri_nonce,
                'filter-by-date-from' => $date_from,
                'filter-by-date-to' => $date_to,
                'status' => $status,
                's' => $s,
                $nonce =>$nonce_value
            ),
            admin_url('edit.php')
        );

        $price_categories = get_terms(array(
            'taxonomy' => 'ojabooking_price_categories',
            'hide_empty' => false,
        ));
?>
        <div class="wrap">
            <h1><?php _e('Bookings', 'ojabooking'); ?></h1>
            <div class="wrap">
                <ul class="subsubsub">
                    <li class="all"><a href="<?php echo add_query_arg(array('post_type' => 'ojabooking_event','page' => 'ojabooking_booking'), admin_url('edit.php')); ?>" class="current" aria-current="page">
                            <?php _e('All', 'ojabooking'); ?> <span class="count">(<?php echo $bookings['booking_all_count'];; ?>)</span></a> |
                    </li>
                </ul>
            </div>
            <form id="posts-filter" method="get">

                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php _e('Search', 'ojabooking'); ?>:</label>
                    <input type="search" id="post-search-input" name="s" value="<?php echo $s; ?>">
                    <input type="submit" id="search-submit" class="button" value="<?php _e('Search', 'ojabooking'); ?>">
                </p>

                <input type="hidden" name="post_type" value="ojabooking_event">
                <input type="hidden" name="page" value="ojabooking_booking">
                <input type="hidden" name="term_id" value="<?php echo $term_id; ?>">
                <input type="hidden" name="<?php echo $nonce;?>" value="<?php echo $nonce_value; ?>">
                
                <div class="tablenav top">
                    <div class="alignleft actions">
                        <label for="filter-by-date-from"><?php _e('From', 'ojabooking'); ?></label>
                        <input id="filter-by-date-from" type="date" value="<?php echo $date_from; ?>" name="date_from">
                        <label for="filter-by-date-to"><?php _e('to', 'ojabooking'); ?></label>
                        <input id="filter-by-date-to" type="date" value="<?php echo $date_to; ?>" name="date_to">
                        <label class="screen-reader-text" for="cat"><?php _e('Filter by status', 'ojabooking'); ?></label>
                        <select name="status" id="status">
                            <option value=""><?php _e('All statuses', 'ojabooking'); ?></option>
                            <?php foreach (ojabooking_get_booking_statuses() as $status_item) {
                                echo '<option class="level-0" value="' . $status_item . '" ' . selected($status_item, $status) . '>' . $status_item . '</option>';
                            }
                            ?>
                        </select>

                        <input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php _e('Filter', 'ojabooking'); ?>">
                    </div>
                    <div class="tablenav-pages one-page"><span class="displaying-num"><?php esc_html_e("$bookings_count bookings", 'ojabooking'); ?></span>
                        <?php ojabooking_admin_pagination((int)$bookings['pages'], (int)$paged); ?>
                    </div>
                    <br class="clear">

                </div>
                <table class="wp-list-table widefat fixed striped table-view-list posts" cellspacing="2">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column" scope="col">
                                <label class="screen-reader-text" for="cb-select-all-1"><?php _e("Select all", 'ojabooking'); ?></label>
                                <input id="cb-select-all-1" type="checkbox">
                            </td>
                            <th id="column-email" class="manage-column column-column-email" scope="col" style="width: 25%;"><?php _e('Contact', 'ojabooking'); ?></th>
                            <th id="term" class="manage-column column-term" scope="col" style="width: 15ch;"><?php _e('Term', 'ojabooking'); ?></th>
                            <th id="status" class="manage-column column-status" scope="col" style="width: 10ch;"><?php _e('Status', 'ojabooking'); ?></th>
                            <th id="group_size" class="manage-column column-group_size num" scope="col" style="width: 5ch;"><?php _e('Group size', 'ojabooking'); ?></th>
                            <th id="detail" class="manage-column column-detail" scope="col"><?php _e('Detail', 'ojabooking'); ?></th>
                            <th id="event" class="manage-column column-event" scope="col"><?php _e('Event', 'ojabooking'); ?></th>
                            <th id="created" class="manage-column column-created" scope="col" style="width: 15ch;"><?php _e('Created', 'ojabooking'); ?></th>
                        </tr>
                    </thead>

                    <tfoot>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column" scope="col">
                                <label class="screen-reader-text" for="cb-select-all-1"><?php _e("Select all", 'ojabooking'); ?></label>
                                <input id="cb-select-all-1" type="checkbox">
                            </td>
                            <th id="column-email" class="manage-column column-column-email" scope="col"><?php _e('Contact', 'ojabooking'); ?></th>
                            <th id="term" class="manage-column column-term" scope="col"><?php _e('Term', 'ojabooking'); ?></th>
                            <th id="status" class="manage-column column-status" scope="col"><?php _e('Status', 'ojabooking'); ?></th>
                            <th id="group_size" class="manage-column column-group_size num" scope="col"><?php _e('Group size', 'ojabooking'); ?></th>
                            <th id="detail" class="manage-column column-detail" scope="col"><?php _e('Detail', 'ojabooking'); ?></th>
                            <th id="event" class="manage-column column-event" scope="col"><?php _e('Event', 'ojabooking'); ?></th>
                            <th id="created" class="manage-column column-created" scope="col"><?php _e('Created', 'ojabooking'); ?></th>
                        </tr>
                    </tfoot>

                    <tbody>
                        <?php foreach ($bookings['bookings'] as $booking) :  ?>
                            
                            <?php 
                            //var_dump($booking);exit;
                            $group = json_decode($booking->group_obj);
                            $group2=array();
                            $booking_detail = array();
                            foreach ($group as $key => $value) {
                                $cat_name = ojabooking_get_object_by_property_value($price_categories, 'term_id', (int)$key)->name;
                                $booking_detail[] =  $value . "x " . $cat_name;
                                $group2[(int)$key]=$value;
                            }
                            $contact=ojabooking_is_group_private_party($group2)?  ", ".$booking->school_name_department . ", ".$booking->class_department:"";
                            ?>
                            <tr id="post-<?php echo $booking->id; ?>" class="iedit author-self level-0 post-<?php echo $booking->id; ?> type-post status-publish format-standard">
                                <th scope="row" class="check-column">
                                    <label class="screen-reader-text" for="cb-select-<?php echo $booking->id; ?>">
                                        <?php esc_html_e("Select $booking->user_email", 'ojabooking'); ?>
                                    </label>
                                    <input id="cb-select-<?php echo $booking->id; ?>" type="checkbox" name="user_email[]" value="<?php echo $booking->id; ?>">
                                </th>
                                <td class="title column-email has-row-actions column-primary" data-colname="<?php _e('User email', 'ojabooking'); ?>">
                                    <?php echo $booking->name; ?>,
                                    <strong>
                                        <a class="row-title" href="mailto:<?php echo $booking->user_email; ?>" aria-label="<?php echo $booking->user_email; ?> ">
                                            <?php echo $booking->user_email; ?>
                                        </a>
                                    </strong>, <?php echo $booking->tel . $contact; ?>
                                    <div class="row-actions">
                                        <span><a href="<?php echo add_query_arg(array('term_id' => $booking->term_id), $terms_uri); ?>"><?php _e('Show term', 'ojabooking'); ?></a> |</span>
                                        <span><a href="<?php echo get_edit_post_link($booking->event_id); ?>"><?php _e('Edit Event', 'ojabooking'); ?></a> |</span>
                                        <span><a href="<?php echo add_query_arg(array('booking' => $booking->id, 'new_status' => "canceled"), $update_status_uri); ?>"><?php _e('Cancel', 'ojabooking'); ?></a> |</span>
                                        <span><a href="<?php echo add_query_arg(array('booking' => $booking->id, 'new_status' => "accepted"), $update_status_uri); ?>"><?php _e('Accept', 'ojabooking'); ?></a> |</span>

                                    </div>
                                </td>
                                <td class="column-term" data-colname="<?php _e('Term', 'ojabooking'); ?>">
                                    <a class="row-title" href="<?php echo add_query_arg(array('term_id' => $booking->term_id), $terms_uri); ?>" aria-label="<?php esc_html_e("Show term $booking->term", 'ojabooking'); ?> ">
                                        <?php echo ojabooking_get_local_date_time($booking->term); ?>
                                    </a>
                                </td>
                                <td class="column-status" data-colname="<?php _e('Status', 'ojabooking'); ?>"><?php echo $booking->status; ?></td>
                                <td class="group_size column-group_size num" data-colname="<?php _e('Group size', 'ojabooking'); ?>"><?php echo $booking->group_size; ?></td>
                                <td class="column-detail" data-colname="<?php _e('Detail', 'ojabooking'); ?>"><?php echo implode(", ", $booking_detail); ?></td>
                                <td class="column-event" data-colname="<?php _e('Event', 'ojabooking'); ?>">
                                    <a class="row-title" href="<?php get_edit_post_link($booking->event_id); ?>" aria-label="<?php esc_html_e("Show event $booking->event_name", 'ojabooking'); ?> ">
                                        <?php echo $booking->event_name; ?>
                                    </a>
                                </td>
                                <td class="column-created" data-colname="<?php _e('Created', 'ojabooking'); ?>"><?php echo $booking->created; ?></td>

                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
                <div class="tablenav bottom">
                    <div class="tablenav-pages one-page"><span class="displaying-num"><?php esc_html_e("$bookings_count bookings", 'ojabooking'); ?></span>
                        <?php ojabooking_admin_pagination((int)$bookings['pages'], (int)$paged); ?>
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
    function ojabooking_booking_save_options()
    {
        $message = null;
        $type = null;

        $action       = 'ojabooking_update_booking_status';
        $nonce        = 'ojabooking_update_booking_status-nonce';
        $is_nonce_set   = isset($_GET[$nonce]);

        $is_valid_nonce = false;

        if ($is_nonce_set) {
            $is_valid_nonce = wp_verify_nonce($_GET[$nonce], $action);
        } else {
            return;
        }
        $booking = $_GET['booking'];
        $status = $_GET['new_status'];
        if (!$is_valid_nonce) {
            return;
            $message = __('Sorry, your data could not be saved', 'ojabooking');
            $type = 'error';
        } elseif (isset($booking) && isset($status)) {
            $updated = ojabooking_update_booking_status($booking, $status);
            if ($updated) {
                $message = __('Successfully updated', 'ojabooking');
                $type = 'updated';
            } else {
                $message = __('Something went wrong', 'ojabooking');
                $type = 'error';
            }
        } else {
            $message = __('Invalid request', 'ojabooking');
            $type = 'error';
        }
        add_settings_error(
            'ojabooking_update_booking_status',
            esc_attr('settings_updated'),
            $message,
            $type
        );
    }
}
if (current_user_can('edit_published_pages')) {
    new Ojabooking_Booking_Admin_Page;
}
