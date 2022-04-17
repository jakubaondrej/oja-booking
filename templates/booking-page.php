<?php /*Template Name: Booking page*/
include plugin_dir_path(__FILE__) . 'booking-parts.php';
oja_get_booking_style();
oja_enqueue_scripts();
get_header();
?>
<?php oja_get_template_part_booking_list() ?>

<div class="container p-1 my-2 p-sm-3 p-md-5 my-2 bg-content shadow rounded-3">
    <h1><?php the_title(); ?></h1>
    <?php
    oja_get_alert_placeholder();
    oja_get_template_part_booking_filter();
    ?>

    <div id="booking-list">
        <h3 class="date mt-4"></h3>
        <div id="loading-more" class="loading-animation content-center">
            <div class="m-3 content-center preloader-animation outside-circle">
                <div class="content-center preloader-animation main-circle">
                    <div class="preloader-animation inside-circle"></div>
                </div>
            </div>
            <div class="h1"><?php _e('Loading', 'oja'); ?></div>
        </div>
        <div class="terms">
        </div>
    </div>
</div>
<?php get_footer(); ?>