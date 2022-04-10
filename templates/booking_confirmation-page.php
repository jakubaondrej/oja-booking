<?php 
$key = filter_input(INPUT_GET, 'key');
$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
$action = filter_input(INPUT_GET, 'action');
$language = filter_input(INPUT_GET, 'language');
$code = isset($booking_id)?oja_get_booking_code($booking_id):"";
//test
$event_term = oja_get_event_term_by_datetime(158, "2022-04-13 02:02:00");
if (is_null($event_term)) {
var_dump($event_term);
$aaa=oja_can_be_create_event_term(158, "2022-04-13 02:02:00");
var_dump($aaa);

$term_id = oja_create_event_term(158, "2022-04-13 02:02:00");
var_dump($term_id);
}
exit;
//test end
if (empty($code) || $code != $key) {
    oja_show_404();
}

get_header(); ?>

<div class="container p-1 my-2 p-sm-3 p-md-5 my-2 bg-content shadow rounded-3">
    <h1><?php the_title(); ?></h1>
    <p><?php echo oja_confirm_booking($booking_id, $key, $action, $language); ?></p>
</div>


<?php get_footer(); ?>