var $ = jQuery;
$(document).ready(function () {
  event_type_selection();
  $('#add-ojabooking_repeat_times').mousedown(function (e) {
    e.preventDefault();
    e.stopPropagation();
    var container = $("<div></div>");
    var input = $("<input type=\"time\"  name=\"ojabooking_repeat_times[]\" value=\"\">");
    container.appendTo("#ojabooking_repeat_times");
    input.appendTo(container);
    $( "#ojabooking_repeat_times > div:first-child .button.remove-ojabooking_repeat_times" ).clone(true, true).appendTo( container );
  });
});

$('.button.remove-ojabooking_repeat_times').on( 'click', function() {
    if($("#ojabooking_repeat_times > div").length > 1){
        $(this).parent().remove();
      }
      else{
        $(this).parent().find("input").val("");
      }
});

$('#ojabooking_reservation_type_meta_data').on('change' , 'input', event_type_selection);

function event_type_selection(){
  if($('#ojabooking_reservation_type_meta_data input#one_day_event').is(':checked')){
    $('#ojabooking_repeat_days_meta_data').fadeOut();
    $('#ojabooking_repeat_months_meta_data').fadeOut();
    $('#ojabooking_repeat_times_meta_data').fadeOut();
    $('#ojabooking_date_span_meta_data').fadeOut();
    $('#ojabooking_the_term_meta_data').fadeIn(400);
  }
  else{
    $('#ojabooking_repeat_days_meta_data').fadeIn(400);
    $('#ojabooking_repeat_months_meta_data').fadeIn(400);
    $('#ojabooking_repeat_times_meta_data').fadeIn(400);
    $('#ojabooking_the_term_meta_data').fadeOut();
    $('#ojabooking_date_span_meta_data').fadeIn(400);
  }
}