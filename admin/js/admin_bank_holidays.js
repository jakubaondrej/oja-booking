var $ = jQuery;
$(document).ready(function () {
  $('#add-holiday').mousedown(function (e) {
    e.preventDefault();
    e.stopPropagation();
    
    var container = $("<div></div>");
    var input = $("<input type=\"date\"  name=\"oja_bank_holidays[]\" value=\"\">");
    container.appendTo("#oja_bank_holidays");
    input.appendTo(container);
    $( "#oja_repeat_times > div:first-child button.button.remove-holiday" ).clone(true, true).appendTo( container );
  })
 
  $('.button.remove-holiday').mousedown(function (e) {
    e.preventDefault();
    e.stopPropagation();
    if($("#oja_repeat_times > div").length > 1){
      $(this).parent().remove();
    }
    else{
      $(this).parent().find("input").val("");

    }
  })
});