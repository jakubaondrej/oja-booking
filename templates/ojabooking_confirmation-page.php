<?php 
$key = filter_input(INPUT_GET, 'key');
$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
$action = filter_input(INPUT_GET, 'action');
$language = filter_input(INPUT_GET, 'language');
$code = isset($booking_id)?ojabooking_get_booking_code($booking_id):"";

if (empty($code) || $code != $key) {
    ojabooking_show_404();
}

get_header(); 
ojabooking_get_booking_style();
?>

<div class="container p-1 my-2 p-sm-3 p-md-5 my-2 bg-content shadow rounded-3">
    <h1><?php the_title(); ?></h1>
    <p><?php echo ojabooking_confirm_booking($booking_id, $key, $action, $language); ?></p>
</div>


<?php get_footer(); ?>