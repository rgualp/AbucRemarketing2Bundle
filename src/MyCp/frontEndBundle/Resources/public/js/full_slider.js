jQuery(document).ready(function($) {
    $('#full-width-slider').royalSlider({
        arrowsNav: true,
        loop: true,
        keyboardNavEnabled: true,
        imageScaleMode: 'fill',
        arrowsNavAutoHide: false,
        autoScaleSlider: false,
        autoScaleSliderWidth: 960,
        autoScaleSliderHeight: 350,
        controlNavigation: 'bullets',       
        startSlideId: 0,
        autoPlay: {
            enabled:true,
            pauseOnHover: true,
            delay: 5000
        },
        numImagesToPreload: 0,
        transitionType:'move',
        deeplinking: {
            enabled: true,
            change: false
        }
    });
});