jQuery(window).on('elementor/frontend/init', function() {
    elementorFrontend.hooks.addAction('frontend/element_ready/shemvener_strip_slider.default', async function($scope) {
        console.log('shemvener_strip_slider.default')
        var $slider = $scope.find('.shemvener-swiper');
        var slidesCount = $scope.find('.js-strip-slider').data('stripslidescount') || 5;

        if ($slider.length === 0) {
            return;
        }

        const Swiper = elementorFrontend.utils.swiper;
        const swiperInstance = await new Swiper($slider, {
            loop: true,
            rtl: true,
            slidesPerView: 1,
            spaceBetween: 10,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                },
                768: {
                    slidesPerView: 4,
                },
                1024: {
                    slidesPerView: slidesCount,
                },
            }
        });
    });
});
