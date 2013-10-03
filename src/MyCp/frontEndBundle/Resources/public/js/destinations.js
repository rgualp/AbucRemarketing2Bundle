$(document).ready(start);

function start() {
    $('#destinations-slider').royalSlider({
        arrowsNav: true,
        loop: true,
        keyboardNavEnabled: true,
        imageScaleMode: 'fill',
        arrowsNavAutoHide: false,
        autoScaleSlider: true,
        autoScaleSliderWidth: 960,
        autoScaleSliderHeight: 350,
        controlNavigation: 'bullets',
        numImagesToPreload: 0,
        startSlideId: 0,
        autoPlay: true,
        transitionType: 'move',
        deeplinking: {
            enabled: true,
            change: false
        }
    });
}

function show_loading()
{
    $('#loading').removeClass('hidden');
}

function hide_loading()
{
    $('#loading').addClass('hidden');

}
