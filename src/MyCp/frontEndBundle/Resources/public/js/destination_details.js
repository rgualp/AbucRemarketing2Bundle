$(document).ready(start);

function start(){
    //initialize_map();

    $('.details_items_per_page').click(function()
    {
       $('.blue_nav_tools li.active').removeClass('active');
       $(this).addClass('active'); 
       var show_rows = $(this).attr('data-content-value');
       $('#items_per_page').html(show_rows);
       
       visualize_rows(show_rows);
    });
    
    $('.top_rated_tools .paginator-cont a').click(do_paginate);
}


function initialize_map()
{
    if(document.getElementById("big_map_details") != null)
    {
        var x = $("#big_map_details").attr("data-x");
        var y = $("#big_map_details").attr("data-y");
        var name = $("#big_map_details").attr("data-name");
        var description = $("#big_map_details").attr("data-description");
        var image = $("#big_map_details").attr("data-image");
        var icon = $("#big_map_details").attr("data-icon");
        
        var center_details = new google.maps.LatLng(x, y);//La Habana 23.09725, -82.37548
        var options_details = {
            zoom: 15,
            center: center_details,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var big_map_details = new google.maps.Map(document.getElementById("big_map_details"), options_details);
        
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(x,y),
            map: big_map_details,
            title: name//,
        //icon: icon
        });
        
        
    
        var boxText = document.createElement("div");
        boxText.style.cssText = "border: 1px solid #ccc; margin-top: 8px; background: #fff; padding: 5px; font-size:11px";
        boxText.innerHTML = "<table><tr><td class='map_image' style='background-image:url("+image+")'></td><td style='padding-left:4px; line-height:12px;' valign='top'>"+name+"<br/><b>" + description + "</b></td></tr></table>";
        
        var myOptions = {
                 content: boxText
                ,disableAutoPan: false
                ,maxWidth: 0
                ,pixelOffset: new google.maps.Size(-140, 0)
                ,zIndex: null
                ,boxStyle: { 
                  background: "url('tipbox.gif') no-repeat"
                  ,opacity: 0.85
                  ,width: "280px"
                 }
                ,closeBoxMargin: "10px 2px 2px 2px"
                ,closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif"
                ,infoBoxClearance: new google.maps.Size(1, 1)
                ,isHidden: false
                ,pane: "floatPane"
                ,enableEventPropagation: false
        };
        
        var ib = new InfoBox(myOptions);

        google.maps.event.addListener(marker, 'mouseover', function() {
            ib.open(big_map_details, marker);
        });
    }
}

function visualize_rows(show_rows)
{
    var url = $("#nav_blue_placeholder").attr("data-url");
    var result = $("#nav_blue_placeholder");
    
    //show_loading();
    $.post(url,{
            'show_rows':show_rows
        },function(data){
            result.html(data);
            //hide_loading();
            start();
        });
}

function do_paginate()
{
     var url = $("#nav_blue_placeholder").attr("data-url");
    var result = $("#nav_blue_placeholder");
    
    //show_loading();
    $.post(url,null,function(data){
            result.html(data);
            //hide_loading();
            start();
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

