$(document).ready(function(){
    var canvas=document.getElementById("canvas-dest-prov")
    var polys_prov=Array;
    var ctx=canvas.getContext('2d');
    var array_signals = new Array();
    var array_circles = new Array();
    var array_signals_show = new Array();
    var canvas_left=$('#canvas-dest-prov').position().left;
    var canvas_top=$('#canvas-dest-prov').position().top;

    // polygon map signal
    var poly_signal=[8,27,1,11,0,10,0,8,0,7,0,5,1,4,2,2,4,1,7,0,9,0,12,1,14,3,15,4,15,5,16,7,16,10,15,11,8,27,8,27,];
    var circ_signal=[5,8,6,6,8,5,10,6,11,8,10,10,8,11,6,10,5,8,5,8];

    polys_prov['poly_pr']=[407,32,408,15,403,2,397,5,398,12,394,18,389,16,390,7,387,2,368,10,360,19,352,29,344,30,332,23,332,29,326,33,316,35,311,41,291,39,290,45,285,51,281,49,271,52,265,47,262,49,262,51,263,53,256,56,251,59,246,57,241,60,236,66,228,66,223,73,215,74,208,69,208,77,199,82,189,90,189,96,186,97,179,95,171,102,170,107,161,116,147,125,128,153,127,179,140,191,141,197,148,202,148,209,154,208,156,211,146,220,131,210,117,217,109,210,103,210,91,220,83,221,80,224,64,226,43,239,37,233,33,234,35,240,31,242,23,242,18,235,11,246,10,249,19,257,44,256,59,242,89,232,107,231,118,241,118,246,106,256,106,267,120,268,128,263,142,248,161,241,184,233,196,233,205,237,216,223,222,213,214,207,214,199,236,179,280,180,289,178,306,166,324,179,337,178,357,164,363,151,375,141,394,146,406,124,418,109,434,105,420,84,421,70,414,53,413,48,407,32];
    polys_prov['poly_ij']=[141,61,139,62,140,64,140,66,137,66,136,61,130,62,124,69,120,70,116,68,116,70,110,72,108,74,101,73,101,76,95,77,92,76,92,78,89,80,85,80,82,83,79,83,78,85,75,85,73,83,72,84,73,86,72,88,68,90,67,93,62,93,58,98,53,103,48,109,44,116,45,123,50,128,53,132,54,134,51,137,45,134,41,136,39,135,38,133,35,134,33,136,29,137,22,139,17,143,14,144,12,141,11,142,12,144,7,144,7,142,6,142,4,144,3,146,6,149,16,150,22,144,29,142,35,140,38,141,41,143,41,146,37,150,37,152,42,153,49,147,57,144,63,141,70,142,72,143,73,143,73,142,73,140,75,138,77,135,76,133,74,133,75,128,79,125,83,123,93,122,101,122,104,119,107,118,109,119,113,122,119,122,123,119,126,116,128,111,131,109,134,109,138,111,140,106,145,99,152,97,147,89,148,84,143,73,142,64,141,61];

    init_map();

    function init_map()
    {
        signal_pos.forEach(function(element){
            temp=get_prop_signal_position(element);
            temp2=get_prop_signal_circle(element);
            array_signals.push(temp);
            array_circles.push(temp2);
        });
    }

    $('#prov-links li').click(function(){
        $('#prov-links li').removeClass('focus');
        $('#prov-name').html($(this).html());
        poly_prov=$(this).attr('data');
        $(this).addClass('focus');
        clear_canvas();
        draw_poly(polys_prov[poly_prov],'#F4961C',0,0,1);
        prov=$(this).attr('data-prov');
        array_signals_show=Array();
        for(u=0; u < dest_provinces.length; u++)
        {
            if(dest_provinces[u]==prov)
            {
                draw_poly(array_signals[u],'#22519D',0,'',0);
                draw_poly(array_circles[u],'#fff',0,'',0);
                array_signals_show.push(u);
            }
        }
    });

    function clear_canvas()
    {
        ctx.clearRect(0,0,446,358);
        ctx.save();
    }

    function get_prop_signal_circle(signal_pos)
    {
        cnv=$('#canvas-dest-prov');
        new_poly=Array();
        for( item=0 ; item < circ_signal.length ; item+=2 )
        {

            new_coord_x= circ_signal[item]+signal_pos[0];
            new_coord_y= circ_signal[item + 1]+signal_pos[1];

            new_coord_x = new_coord_x * cnv.attr('width') / 446;
            new_coord_y = new_coord_y * cnv.attr('height') / 358;

            new_poly.push(new_coord_x);
            new_poly.push(new_coord_y);
        }
        return new_poly;
    }

    function get_prop_signal_position(signal_pos)
    {
        new_poly=Array();
        cvas=$('#canvas-dest-prov');
        for( item=0 ; item < poly_signal.length ; item+=2 )
        {

            new_coord_x= poly_signal[item]+signal_pos[0];
            new_coord_y= poly_signal[item + 1]+signal_pos[1];
            new_coord_x = new_coord_x * cvas.attr('width') / 446;
            new_coord_y = new_coord_y * cvas.attr('height') / 358;
            new_poly.push(new_coord_x);
            new_poly.push(new_coord_y);
        }
        return new_poly;
    }

    function get_prop_poly(poly)
    {
        new_poly=Array();
        cnv=$('#canvas-dest-prov');
        for( item=0 ; item < poly.length ; item+=2 )
        {
            new_coord_x= poly[item] * cnv.attr('width') / 446;
            new_coord_y= poly[item + 1] * cnv.attr('height') / 358;
            new_poly.push(new_coord_x);
            new_poly.push(new_coord_y);
        }

        return new_poly;

    }

    function draw_poly(poly_points,color, stroke_width, stroke_color,shadow)
    {
        ctx.fillStyle = color;
        ctx.beginPath();

        ctx.moveTo(poly_points[0], poly_points[1]);
        for( item=2 ; item < poly_points.length-1 ; item+=2 )
        {
            ctx.lineTo( poly_points[item] , poly_points[item+1] )
        }
        ctx.closePath();
        if(shadow==1)
        {
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = -1.8;
            ctx.shadowBlur = 1;
            ctx.shadowColor = "#514F4F";
        }
        else
        {
            ctx.shadowOffsetY = 0;
            ctx.shadowBlur = 0;
        }
        ctx.fill();
        if(stroke_width!=0)
        {
            ctx.lineWidth = stroke_width;
            ctx.strokeStyle = stroke_color;
            ctx.stroke();
        }
    }
    function do_mouse_move(event)
    {
        clear_canvas();
        point = [parseInt(event.pageX - canvas_left),parseInt(event.pageY -canvas_top)];

        draw_poly(polys_prov[poly_prov],'#F4961C',0,0,1);

        focused=null;
        for(cont=0;cont<array_signals_show.length; cont++){
            focus_color='#3F3F3F';
            if(is_in_poly(get_prop_poly(array_signals[array_signals_show[cont]]),point))
            {

                temp=get_prop_poly(array_signals[array_signals_show[cont]]);
                draw_poly(temp,focus_color,0,'',0);
                focused=cont;
                //sig_tooltip.html((destinations[array_signals_show[cont]].split('_').join(' ')).toUpperCase());
                //sig_tooltip.css('left',event.pageX - 30);
                //sig_tooltip.css('top',event.pageY - 35);
            }
            else
            {
                for(d=0;d < array_signals_show.length; d++)
                {
                    if(d!=focused)
                    {
                        temp=get_prop_poly(array_signals[array_signals_show[d]]);
                        draw_poly(temp,'#22519D',0,'',0);

                       temp2=get_prop_signal_circle(signal_pos[array_signals_show[d]]);
                       draw_poly(temp2,'#fff',0,'',0);

                    }
                }
            }

        }

    }

    function do_mouse_click(event)
    {
        point = [parseInt(event.pageX - canvas_left),parseInt(event.pageY -canvas_top)];
        for(cont=0;cont<array_signals_show.length; cont++){
            if(is_in_poly(get_prop_poly(array_signals[array_signals_show[cont]]),point))
            {
                name=destinations_names[array_signals_show[cont]];
                name=name.toLowerCase();
                name=name.split(' ').join('_');
                window.location=link_destination +'/'+ name;
            }

        }

    }

    function is_in_poly(point_list, p)
    {

        var counter = 0;
        var i;
        var xinterest;
        var p1 = [0,0];
        var p2 = [0,0];
        var n = point_list.length / 2;
        p1= [point_list[0],point_list[1]];

        for(i=1;i<=n;i++)
        {
            p2=[point_list[i%n *2],point_list[i%n *2+1]];
            if(p[1] > Math.min(p1[1],p2[1]))
            {
                if(p[1] <= Math.max(p1[1],p2[1]))
                {
                    if(p[0] <= Math.max(p1[0],p2[0]))
                    {
                        if(p1[1] != p2[1])
                        {
                            xinterest = (p[1]-p1[1]) * (p2[0]-p1[0]) / (p2[1]-p1[1]) + p1[0];
                            if(p1[0] == p2[0] || p[0] <= xinterest)
                            {
                                counter++;
                            }
                        }
                    }
                }
            }
            p1=p2;
        }
        if(counter % 2 == 0){
            return false;
        }
        else
        {
            return true;
        }
    }

    canvas.addEventListener('mousemove', do_mouse_move,null);
    canvas.addEventListener('click',do_mouse_click,null);
    $('#first-prov').trigger('click');
});