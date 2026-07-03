$(document).ready(function() {
    var activeFilters = [];

    // Parse URL parameter
    var urlParams = new URLSearchParams(window.location.search);
    var catParam = urlParams.get('cat');
    if (catParam) {
        var $btn = $('.cu-filter-btn[data-filter="' + catParam + '"]');
        if ($btn.length) {
            $('.cu-filter-btn[data-filter="all"]').removeClass('active');
            $btn.addClass('active');
            activeFilters.push(catParam);
        }
    }

    $('.cu-filter-btn').on('click', function(e) {
        e.preventDefault();
        var filter = $(this).data('filter');
        
        if (filter === 'all') {
            $('.cu-filter-btn').removeClass('active');
            $(this).addClass('active');
            activeFilters = [];
        } else {
            $('.cu-filter-btn[data-filter="all"]').removeClass('active');
            
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
                activeFilters = activeFilters.filter(f => f !== filter);
            } else {
                $(this).addClass('active');
                activeFilters.push(filter);
            }
            
            if (activeFilters.length === 0) {
                $('.cu-filter-btn[data-filter="all"]').addClass('active');
            }
        }

        applyFilters();
    });

    function applyFilters() {
        // Grid logic (tours.php)
        if ($('#toursGrid').length) {
            if (activeFilters.length === 0) {
                $('.tour-grid-item').fadeIn(300);
            } else {
                $('.tour-grid-item').hide().filter(function() {
                    var cats = $(this).find('.tour-filter-item').data('categories');
                    var catArray = cats ? cats.split(' ') : [];
                    // Using OR logic: if any selected filter matches the tour's categories
                    return activeFilters.some(f => catArray.includes(f));
                }).fadeIn(300);
            }
        }

        // Slick Carousel logic (index.php)
        var $carousel = $('.cu-tours-carousel');
        if ($carousel.length && $carousel.hasClass('slick-initialized')) {
            $carousel.slick('slickUnfilter');
            
            if (activeFilters.length > 0) {
                $carousel.slick('slickFilter', function() {
                    var cats = $(this).find('.tour-filter-item').data('categories');
                    var catArray = cats ? cats.split(' ') : [];
                    return activeFilters.some(f => catArray.includes(f));
                });
            }
        }
    }

    // Apply on load if we have active filters from URL
    if (activeFilters.length > 0) {
        // give slick time to initialize if we are on index.php
        setTimeout(function() { applyFilters(); }, 100);
    }
});
