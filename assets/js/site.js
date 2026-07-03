/* Camping Uzbekistan — public site behaviour (layered on theme.js). */
(function ($) {
    'use strict';

    $(function () {
        // Upcoming-tours carousel
        if ($('.cu-tours-carousel').length) {
            $('.cu-tours-carousel').slick({
                dots: false,
                infinite: true,
                speed: 700,
                autoplay: true,
                autoplaySpeed: 4500,
                slidesToShow: 3,
                slidesToScroll: 1,
                prevArrow: $('.cu-tours-arrows .prev'),
                nextArrow: $('.cu-tours-arrows .next'),
                responsive: [
                    { breakpoint: 992, settings: { slidesToShow: 2 } },
                    { breakpoint: 768, settings: { slidesToShow: 1 } }
                ]
            });
        }

        // Spoilers: tap/click to reveal
        $(document).on('click', '.spoiler', function () {
            $(this).toggleClass('revealed');
        });

        // Guide modal — fill from the clicked guide's data
        var $modal = $('#cuGuideModal');
        $(document).on('click', '.cu-guide', function () {
            var d = $(this).data();
            $modal.find('.cu-guide-modal__avatar').attr('src', d.avatar || '');
            $modal.find('.cu-guide-modal__name').text(d.name || '');
            $modal.find('.cu-guide-modal__bio').html(d.bio || '');
            var $s = $modal.find('.cu-guide-modal__socials').empty();
            (d.socials || []).forEach(function (s) {
                var $a = $('<a>').attr({ href: s.url, target: '_blank', rel: 'noopener', title: s.label });
                if (s.icon) { $a.append($('<img>').attr({ src: s.icon, alt: s.label })); }
                else { $a.append($('<i>').addClass(s.faclass || 'fas fa-link')); }
                $s.append($a);
            });
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance($modal[0]).show();
            } else {
                $modal.addClass('show').css('display', 'block');
            }
        });
    });
})(jQuery);
