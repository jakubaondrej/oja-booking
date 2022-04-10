var $ = jQuery;
$(document).ready(function () {
  $("#preloader").fadeOut(1000);
  //--Navbar_Toggle_icon--//
  NavItemsStatus = '';

  $('.navbar-toggler-icon').mousedown(function (e) {
    e.preventDefault();
    e.stopPropagation();
    //---Check if the NavItems are opened, if so then close them, otherwise open them--//
    if ((NavItemsStatus == "") || (NavItemsStatus == undefined)) {
      NavItemsStatus = 'on';
      //--Do nothing here as the nav items are sliding down after clicked for the first time--// 
    } else if ((NavItemsStatus == "off")) {
      NavItemsStatus = 'on';
      $("#navbarSupportedContent").show(100); //the id which was called on data-bs-target
    } else {
      NavItemsStatus = 'off';
      $("#navbarSupportedContent").hide(100);
      //$("#navbarSupportedContent").css('height','0px');
    }
  })


});

$("input.form-control").change(function () {
  if ($(this)[0].checkValidity()) {
      $(this).addClass('is-valid');
      $(this).removeClass("is-invalid");
  }
  else {
      $(this).addClass('is-invalid');
      $(this).removeClass("is-valid");
  }
});

$("input.email_address").change(function () {
  var pattern = new RegExp(/^[+a-zA-Z0-9\._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
  if (pattern.test($(this).val())) {
      $(this).addClass('is-valid');
      $(this).removeClass("is-invalid");
  }
  else {
      $(this).addClass('is-invalid');
      $(this).removeClass("is-valid");
  }
});

function alert(message, type) {
  var wrapper = document.createElement('div');
  wrapper.className = 'alert alert-' + type + ' alert-dismissible d-flex align-items-center';
  wrapper.role = "alert";
  var icon_label = 'Success';
  var icon = 'check-circle-fill';
  if (type != 'success') {
      icon_label = 'Warning';
      icon = 'exclamation-triangle-fill';
  }
  wrapper.innerHTML = '<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="' + icon_label + ':"><use xlink:href="#' + icon + '"/></svg>'
      + '<div>' + message + '</div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';

  document.getElementById('liveAlertPlaceholder').appendChild(wrapper);
}

function removeAlerts() {
  function removeHiddenAlerts() {
    $( this ).remove();
  }
  $('#liveAlertPlaceholder .alert').fadeOut(200,removeHiddenAlerts);
}

// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })
})()