$(document).ready(function () {

    const btnToTop = $('.btn-to-top');

    if (btnToTop) {
        btnToTop.click(function () {
            $('body, html').animate({scrollTop: '0px'}, 500);
        });

        toggleToTop();

        $(window).scroll(function () {
            toggleToTop();
        });

        function toggleToTop() {
            if ($(this).scrollTop() > 100) {
                btnToTop.fadeIn(300);
            } else {
                btnToTop.fadeOut(300);
                btnToTop.blur();
            }
        }
    }

    let preloader = $('.preloader');

    if (preloader) {
        preloader.remove();
        // document.querySelector('#content').removeAttribute('style');
        $('#content').removeClass('d-none');
    }
});

$('a[data-method="post"]').on('click', function (e) {
    e.preventDefault();

    const url = $(this).attr('href');
    const confirmMessage = $(this).data('confirm');

    if (confirmMessage && !confirm(confirmMessage)) {
        return;
    }

    const form = $('<form>', {
        method: 'POST',
        action: url
    });

    // form.append($('<input>', {
    //     type: 'hidden',
    //     name: '_csrf',
    //     value: 'your-csrf-token-here' // Optional: Add CSRF token input if needed
    // }));

    $('body').append(form);
    form.submit();
});

$(document).on("click", "#selectedRole", function (event) {
    event.preventDefault();

    $.ajax({
        type: 'post',
        url: $(this).attr('href')
    }).done(function () {
        window.location.reload();
    });

    return true;
});

$(document).on('click', "#btn-change-theme", function (event) {
    event.preventDefault();

    let button = $(this);

    button.addClass('disabled');

    $.ajax({
        url: $(this).data('url'),
        dataType: 'json',
        success: function() {
            window.location.reload();
        },
        error: function(jqXHR, textStatus) {
            nerror(textStatus);
        },
        complete: function () {
            button.removeClass('disabled');
        }
    });

    return false;
});