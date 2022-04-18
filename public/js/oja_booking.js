var $ = jQuery;
const supportsTemplate = function () {
    return 'content' in document.createElement('template');
}

$(document).ready(function () {
    group_changed();

    var bookingGroupModal = document.getElementById('booking-group-modal')
    bookingGroupModal.addEventListener('hide.bs.modal', function (event) { group_changed(event) })
});

$("#booking-filter").on('change', 'input:not([name*=group])', function (e) {
    e.stopPropagation();
    e.preventDefault();
});
$("#booking-filter").on('click', 'button', function (e) {
    e.stopPropagation();
    e.preventDefault();
});
$("#booking-filter").on('change', 'input#date,select#booking-language', function (e) {
    oja_load_booking();
});

$("#booking-list").on('click', '.booking button.book', function (e) {
    var event_id = $(this).attr("event_id");
    var term = $(this).attr("term");
    $("#booking-event-id").val(event_id);
    $("#booking-term").val(term);
});


$("#booking-filter").on('click', '#booking-contact-modal-btn', function (e) {
    e.stopPropagation();
    e.preventDefault();
    oja_book_me();
});

$('#booking-filter').on('click','button.private-party-select', function (e){
    e.stopPropagation();
    e.preventDefault();
    $("#booking-group-modal .price_category input").val("0");
    $(this).siblings( "input" ).val("1");
    $("#private-party-contact-details").removeClass("hidden");
    $("#private-party-contact-details input").prop('required',true);
    group_changed();
});

$('#booking-filter').on('click','button#button-select-group', function (e){
    $("#booking-group-modal .private_party.price_category input").val("0");
    $("#private-party-contact-details").addClass("hidden");
    $("#private-party-contact-details input").prop('required',false);
});

function group_changed(event) {
    if(event && event.target.attributes.style.value=="display: none;") return;
    var groups = $("#booking-group-modal .price_category");
    selected_group_array = new Array();
    groups.each(function (index, value) {
        var cat_count = $(this).find("input").val();
        if (cat_count > 0) {
            selected_group_array.push(cat_count + "x " + $(this).find("label").text());
        }
    });
    var text = selected_group_array.toString();
    if(text.length<1) text = Oja_Ajax.select_group_text;
    $(".selected-group").html(text);
    oja_load_booking();
}


function oja_load_booking() {
    removeAlerts();
    var button = $('#load-more');
    var loading_spin = $("#loading-more");
    var data = $("#booking-filter").serialize() + "&action=oja_get_events&nextNonce=" + Oja_Ajax.nextNonce;

    $.ajax({
        url: Oja_Ajax.ajaxurl,
        data: data,
        type: 'POST',
        beforeSend: function (xhr) {
            button.fadeOut(100);
            loading_spin.fadeIn(200);
        },
        success: function (data) {
            document.getElementById("booking-list").getElementsByClassName("terms")[0].innerHTML = "";
            if (data.success) {
                $("#booking-list .date").text(data.data.date);
                data.data.events.forEach(event => {
                    appendTerm(event);
                });
                Oja_Ajax.current_page++;
                Oja_Ajax.max_page = data.data.max_num_pages;
                if (Oja_Ajax.current_page == Oja_Ajax.max_page)
                    button.hide(); // if last page, remove the button

                if (data.data.out['no_events']) {
                    document.getElementById("booking-list").getElementsByClassName("terms")[0].textContent = data.data.out['no_events'];
                }
            } else {
                button.hide(); // if no data, remove the button as well
                alert(data.data,'warning');

            }
        },
        error: function (request, status, error) {
            alert(request.responseText,'warning');
        },
        complete: function () {
            loading_spin.fadeOut(100);
            button.fadeIn(300);
        }
    });
}


function appendTerm(event) {
    if (supportsTemplate) {
        event.terms.forEach(term => {
            var vacancy = event.max_group_size - term.occupancy;
            var temp = document.getElementById("booking-list-template");
            var clone = temp.content.cloneNode(true);
            clone.querySelectorAll(".time")[0].textContent = term.time;

            var title_el=  clone.querySelectorAll(".title a")[0];
            title_el.textContent = event.event.post_title;
            title_el.setAttribute("href", event.event_link??"#");

            clone.querySelectorAll(".vacancies")[0].textContent = vacancy;
            clone.querySelectorAll(".price")[0].textContent = get_price_text(event.price);

            var button = clone.querySelectorAll("button.book")[0];
            button.setAttribute("term", term.term);
            button.setAttribute("event_id", event.event.ID);

            document.getElementById("booking-list").getElementsByClassName("terms")[0].appendChild(clone);
        });

    }
}

function oja_book_me() {
    event.preventDefault();
    event.stopPropagation();
    removeAlerts();
    var form = document.getElementById("booking-filter");
    form.classList.add('was-validated')
    if (!form.checkValidity()) {
        $("#booking-contact-modal").modal('toggle');
        return;
    }
    var button = $('#load-more');
    var loading_spin = $("#loading-more");
    var data = $("#booking-filter").serialize() + "&action=oja_create_booking&bookingNonce=" + Oja_Ajax.bookingNonce;

    $.ajax({
        url: Oja_Ajax.ajaxurl,
        data: data,
        type: 'POST',
        beforeSend: function (xhr) {
            button.fadeOut(100);
            loading_spin.fadeIn(200);
        },
        success: function (data) {
            if (data.success) {
                alert(data.data, 'success');
            }
            else{
                if(Array.isArray(data.data)){
                    data.data.forEach(mes =>alert(mes, 'warning'));
                }
                else{
                    alert(data.data, 'warning')
                }
                
            }
        },
        error: function (request, status, error) {
            alert(request.responseText,'warning');
        },
        complete: function () {
            loading_spin.fadeOut(100);
            button.fadeIn(300);
        }
    });
}