jQuery(document).ready(function($) {
    $('#full-width-slider').royalSlider({
        arrowsNav: false,
        loop: true,
        keyboardNavEnabled: true,
        controlsInside: false,
        imageScaleMode: 'fill',
        arrowsNavAutoHide: false,
        autoScaleSlider: true,
        autoScaleSliderWidth: 960,
        autoScaleSliderHeight: 350,
        controlNavigation: 'bullets',
        thumbsFitInViewport: false,
        navigateByClick: false,
        startSlideId: 0,
        autoPlay: true,
        transitionType:'move',
        globalCaption: false,
        deeplinking: {
            enabled: true,
            change: false
        },
        /* size of all images http://help.dimsemenov.com/kb/royalslider-jquery-plugin-faq/adding-width-and-height-properties-to-images */
        imgWidth: 1400,
        imgHeight: 488
    });
});