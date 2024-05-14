$(document).ready(function () {
    $('.accordion .accordion-header').click(function () {
        const id = $(this).attr('data-accordion-header-id'),
            el = $('.accordion[data-accordion="' + id + '"]');
        if(el.hasClass('open')) {
            el.removeClass('open')
        } else el.addClass('open');
    });
});
