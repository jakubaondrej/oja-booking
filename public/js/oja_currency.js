var $ = jQuery;
var lang = navigator.language;
var currency = Oja_Currency.current_currency;

function get_price_text(number) {
  return number.toLocaleString(lang, { style: 'currency', currency: currency });
}

function set_price_element(element) {
  var number = price.attr("amount");
  var element_text = get_price_text(number);
  element.text(element_text);
}

$(document).ready(function () {
  var currencyElements = document.getElementsByClassName('currency');
  for (let item of currencyElements) {
    var curVal = parseInt(item.getAttribute('amount'));
    var curStr = curVal.toLocaleString(lang, { style: 'currency', currency: currency });
    item.textContent = curStr;
  }
});

$(document).ready(function () {
  
  update_prices();

  $("#create-event-reservation table .group-count").change(function () {
    update_prices();
  });
})

function update_price_element(element){
  var price = $(element).attr("price");
  var count = $(element).val();
  var sum_price = price * count;
  var sum_price_text = get_price_text(sum_price);
  var cat_row = $(element).parent().parent("tr");
  $(cat_row).children(".price-category").html(sum_price_text);
  return sum_price;
}

function update_prices(){
  var sum_price = 0;
  $("#create-event-reservation table .group-count").each(function(){
    sum_price+=update_price_element($(this));
  })
  var sum_price_text = get_price_text(sum_price);
  $("#create-event-reservation table tfoot .price-category").html(sum_price_text);
}