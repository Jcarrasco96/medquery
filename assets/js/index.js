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
        $('#content').removeClass('d-none');
    }

    $(document).ajaxSend(function () {
        $('#loading-indicator').show();
    });
    $(document).ajaxComplete(function () {
        $('#loading-indicator').hide();
    });
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