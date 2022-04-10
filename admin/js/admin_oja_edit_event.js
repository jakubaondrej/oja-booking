var $ = jQuery;
$(document).ready(function () {
  event_type_selection();
  $('#add-oja_repeat_times').mousedown(function (e) {
    e.preventDefault();
    e.stopPropagation();
    var container = $("<div></div>");
    var input = $("<input type=\"time\"  name=\"oja_repeat_times[]\" value=\"\">");
    container.appendTo("#oja_repeat_times");
    input.appendTo(container);
    $( "#oja_repeat_times > div:first-child .button.remove-oja_repeat_times" ).clone(true, true).appendTo( container );
  });
});

$('.button.remove-oja_repeat_times').on( 'click', function() {
    if($("#oja_repeat_times > div").length > 1){
        $(this).parent().remove();
      }
      else{
        $(this).parent().find("input").val("");
      }
});

$('#oja_reservation_type_meta_data').on('change' , 'input', event_type_selection);

function event_type_selection(){
  if($('#oja_reservation_type_meta_data input#one_day_event').is(':checked')){
    $('#oja_repeat_days_meta_data').fadeOut();
    $('#oja_repeat_months_meta_data').fadeOut();
    $('#oja_repeat_times_meta_data').fadeOut();
    $('#oja_date_span_meta_data').fadeOut();
    $('#oja_the_term_meta_data').fadeIn(400);
  }
  else{
    $('#oja_repeat_days_meta_data').fadeIn(400);
    $('#oja_repeat_months_meta_data').fadeIn(400);
    $('#oja_repeat_times_meta_data').fadeIn(400);
    $('#oja_the_term_meta_data').fadeOut();
    $('#oja_date_span_meta_data').fadeIn(400);
  }
}