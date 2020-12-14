$(function () {
    $('.delete_it').on('click', function(event) {
        $(this).parents('tbody').remove();
        console.log('12')
    });
});

jQuery(document).ready(function() {
    $(".grow1").animate({
        width: "30%"
    }, 1200);

    sliders.init();

    $('.slider-nav').on('click', '.slick-items', function () {
        $('.slick-items').removeClass('this-active');
        $(this).addClass('this-active');
    });

    $('.slider-nav').on('click', '.slick-slide', function (event) {
        event.preventDefault();
        var goToSingleSlide = $(this).data('slick-index');

        $('.slider-single').slick('slickGoTo', goToSingleSlide);
    });
});

var $TABLE = $('#table-row');

$('.table-add').click(function() {
    var $clone = $TABLE.find('tr.hide').clone(true).removeClass('hide table-line');
    $TABLE.find('table').find('thead').first().after($clone);
});

$('.table-remove').click(function() {
    $(this).parents('#table-row tr').detach();
});

$('.table-up').click(function() {
    var $row = $(this).parents('#table-row tr');
    if ($row.index() === 1) return; // Don't go above the header
    $row.prev().before($row.get(0));
});

$('.table-down').click(function() {
    var $row = $(this).parents('#table-row tr');
    $row.next().after($row.get(0));
});

$('.tbtn').click(() => $('.toggler').toggle());

// Popup Open
function popupOpen(){
    document.getElementById("popupfordelete").style.display="block";
    document.getElementById("overlay").style.display="block";
}
// Popup Close
function popupClose(){
    document.getElementById("popupfordelete").style.display="none";
    document.getElementById("overlay").style.display="none";
}

// Re-init sliders
var sliders = {
    init: function() {
        $('.owl-carousel').owlCarousel({
            dots: false,
            loop: false,
            autoHeight: true,
            nav: true,
            navText: [
                '<i class="fa fa-chevron-left" aria-hidden="true"></i>',
                '<i class="fa fa-chevron-right" aria-hidden="true"></i>'
            ],
            responsive: {
                375: {
                    items: 2
                },

                600: {
                    items: 3
                },
                768: {
                    items: 4
                },

                1024: {
                    items: 6
                },

                1366: {
                    items: 7
                },
                1500: {
                    items: 8
                },
                1900: {
                    items: 9
                },
                2000: {
                    items: 12
                }
            }
        });

        $('.slider-nav').not('.slick-initialized').slick({
            slidesToShow: 8,
            slidesToScroll: 8,
            dots: false,
            focusOnSelect: false,
            infinite: false,
            responsive: [
                {
                    breakpoint: 1300,
                    settings: {
                        slidesToShow: 6,
                        slidesToScroll: 6,
                    }
                }, {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 5,
                        slidesToScroll: 5,
                    }
                }, {
                    breakpoint: 640,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                    }
                }, {
                    breakpoint: 420,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                    }
                }
            ]
        });
    },

    removeSlickSlides: function () {
        $('.slider-nav').removeClass('slick-initialized slick-slider').children().remove();
    }
}

