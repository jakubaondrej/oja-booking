var $ = jQuery;
const supportsTemplate = function () {
  return 'content' in document.createElement('template');
}
$('#add-booking').on('click', function (e) {
  e.preventDefault();
  e.stopPropagation();
  if (supportsTemplate) {
    let temp = document.getElementById("ojabooking_booking_category_template");
    let cont = temp.content;
    document.getElementById("ojabooking_booking_categories").appendChild(cont.cloneNode(true));
  }
  var container = $("<div></div>");
  var input = $("<input type=\"date\"  name=\"ojabooking_bank_holidays[]\" value=\"\">");
  container.appendTo("#ojabooking_bank_holidays");
  input.appendTo(container);
  $("#ojabooking_booking_categories > div:first-child button.button.remove-booking").clone(true, true).appendTo(container);
});

$("#ojabooking_booking_categories").on('click','.button.remove-booking',function (e) {
  e.stopPropagation();
  e.preventDefault();
  if ($("#ojabooking_booking_categories > div").length > 1) {
    $(this).parent().remove();
  }
  else {
    $(this).parent().find("input").val("");

  }
});

$("#ojabooking_booking_categories").on('change', ".booking_category_name", function () {
  var newName = "ojabooking_booking_categories[";
  newName = newName.concat($(this).val(), "]");
  $(this).parent().find("input.booking_category_price").attr('name', newName);
});

$('#add-booking_language').on('click', function (e) {
  e.preventDefault();
  e.stopPropagation();
  if (supportsTemplate) {
    let temp = document.getElementById("ojabooking_booking_language_template");
    let cont = temp.content;
    document.getElementById("ojabooking_booking_languages").appendChild(cont.cloneNode(true));
  }
});

$("#ojabooking_booking_languages").on('click','.button.remove-booking_language',function (e) {
  e.stopPropagation();
  e.preventDefault();
  if ($("#ojabooking_booking_languages > div").length > 1) {
    $(this).parent().remove();
  }
  else {
    $(this).parent().find("input").val("");

  }
});
$("#ojabooking_booking_languages").on('change', ".booking_language_name", function () {
  var vla= $(this).val();
  $(this).parent().find("input.default-language").val(vla);
});
