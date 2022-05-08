var $ = jQuery;
$(document).ready(function () {
  $('#add-holiday').mousedown(function (e) {
    e.preventDefault();
    e.stopPropagation();
    
    var container = $("<div></div>");
    var input = $("<input type=\"date\"  name=\"ojabooking_bank_holidays[]\" value=\"\">");
    container.appendTo("#ojabooking_bank_holidays");
    input.appendTo(container);
    $( "#ojabooking_bank_holidays > div:first-child button.button.remove-holiday" ).clone(true, true).appendTo( container );
  })
 
  $('.button.remove-holiday').mousedown(function (e) {
    e.preventDefault();
    e.stopPropagation();
    if($("#ojabooking_bank_holidays > div").length > 1){
      $(this).parent().remove();
    }
    else{
      $(this).parent().find("input").val("");

    }
  })
});