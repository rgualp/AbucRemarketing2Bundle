$(document).ready(function(){
    var canvas=document.getElementById("canvas-dest-prov")
    var polys_prov=Array;
    var ctx=canvas.getContext('2d');
    var array_signals = new Array();
    var array_circles = new Array();
    var array_signals_show = new Array();
    var canvas_left=$('#canvas-dest-prov').position().left;
    var canvas_top=$('#canvas-dest-prov').position().top;
    var sig_tooltip=$('#signal_tooltip');

    // polygon map signal
    var poly_signal=[8,27,1,11,0,10,0,8,0,7,0,5,1,4,2,2,4,1,7,0,9,0,12,1,14,3,15,4,15,5,16,7,16,10,15,11,8,27,8,27,];
    var circ_signal=[5,8,6,6,8,5,10,6,11,8,10,10,8,11,6,10,5,8,5,8];

    polys_prov['poly_pr']=[407,32,408,15,403,2,397,5,398,12,394,18,389,16,390,7,387,2,368,10,360,19,352,29,344,30,332,23,332,29,326,33,316,35,311,41,291,39,290,45,285,51,281,49,271,52,265,47,262,49,262,51,263,53,256,56,251,59,246,57,241,60,236,66,228,66,223,73,215,74,208,69,208,77,199,82,189,90,189,96,186,97,179,95,171,102,170,107,161,116,147,125,128,153,127,179,140,191,141,197,148,202,148,209,154,208,156,211,146,220,131,210,117,217,109,210,103,210,91,220,83,221,80,224,64,226,43,239,37,233,33,234,35,240,31,242,23,242,18,235,11,246,10,249,19,257,44,256,59,242,89,232,107,231,118,241,118,246,106,256,106,267,120,268,128,263,142,248,161,241,184,233,196,233,205,237,216,223,222,213,214,207,214,199,236,179,280,180,289,178,306,166,324,179,337,178,357,164,363,151,375,141,394,146,406,124,418,109,434,105,420,84,421,70,414,53,413,48,407,32];
    polys_prov['poly_ij']=[317,101,307,80,288,65,255,58,217,48,178,51,138,64,113,90,95,111,104,137,111,159,136,185,154,219,148,240,137,255,106,255,79,239,58,212,46,210,43,215,48,230,49,246,75,282,97,298,131,305,153,306,176,319,219,320,267,302,289,294,330,273,363,264,384,232,381,219,364,193,364,177,372,158,372,125,363,112,317,101];
    polys_prov['poly_ar']=[21,75,28,99,28,142,43,182,43,188,61,226,61,235,58,258,90,305,114,294,138,274,167,266,181,229,194,216,220,219,253,227,337,225,420,222,404,198,406,134,410,95,382,109,354,84,319,69,319,50,311,44,298,52,251,59,210,62,206,67,198,83,194,69,169,67,136,66,107,69,90,74,91,77,93,85,95,92,94,97,79,98,77,95,81,76,76,74,67,76,51,73,21,75];
    polys_prov['poly_ha']=[29,158,44,174,45,204,96,232,145,275,237,227,272,213,289,211,294,214,322,198,335,157,349,145,401,145,420,125,428,95,426,86,432,79,423,62,378,68,284,61,250,61,177,87,180,93,197,99,202,110,200,122,186,124,177,114,176,98,170,92,145,91,118,104,96,133,73,135,29,158];
    polys_prov['poly_my']=[179,55,183,69,180,78,175,94,158,107,129,109,123,128,103,142,92,141,54,154,48,254,65,283,99,285,110,286,162,299,179,295,247,285,302,300,364,333,389,324,402,298,401,290,369,264,369,253,369,233,345,185,354,164,333,131,353,115,371,69,336,65,302,71,265,68,221,56,179,55];
    polys_prov['poly_ma']=[171,48,159,39,147,39,131,71,139,85,136,97,148,122,146,138,161,150,163,160,154,174,142,178,142,194,126,210,57,217,17,218,31,231,64,245,89,251,98,267,113,272,154,272,161,274,178,268,203,275,211,293,220,295,234,288,246,290,252,284,244,255,244,247,255,255,260,290,317,305,357,303,367,300,404,304,403,297,398,294,339,282,326,257,323,225,334,208,363,202,381,207,405,208,410,198,412,170,417,149,398,108,389,121,362,104,361,93,365,83,365,76,373,71,390,75,395,69,395,57,380,51,364,56,332,62,310,62,297,54,293,50,289,50,275,68,257,68,233,51,242,37,249,35,268,33,270,29,268,27,260,27,212,47,191,54,180,64,168,67,159,66,157,62,161,55,171,48];
    polys_prov['poly_vc']=[87,175,117,163,131,171,138,179,147,166,158,164,172,170,181,178,180,198,182,218,199,234,216,267,221,308,232,330,243,340,245,328,243,321,276,318,289,320,299,294,296,279,288,268,336,280,356,285,368,283,395,258,403,258,417,248,395,223,391,202,391,195,406,181,418,175,414,171,408,173,404,179,388,192,374,183,357,170,325,122,313,123,293,112,274,109,267,96,265,99,257,100,231,78,223,74,188,70,176,79,161,81,145,77,138,71,129,71,119,72,97,60,86,60,77,45,69,38,70,48,56,60,34,56,34,64,30,78,51,97,60,86,67,86,73,95,91,132,87,175];
    polys_prov['poly_cf']=[39,129,51,189,65,205,110,219,155,226,170,235,172,250,197,252,204,246,201,240,183,222,189,211,207,213,223,231,223,240,210,254,239,272,309,330,352,344,378,302,397,300,382,282,370,253,360,190,339,148,312,124,313,106,314,97,309,91,310,62,306,58,281,49,275,54,259,78,242,59,226,51,200,63,183,69,182,90,166,105,140,105,122,103,97,94,65,100,46,113,39,129];

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
        sig_tooltip.css('left',-500);
        draw_poly(polys_prov[poly_prov],'#F4961C',0,0,1);

        focused=null;
        for(cont=0;cont<array_signals_show.length; cont++){
            focus_color='#3F3F3F';
            if(is_in_poly(get_prop_poly(array_signals[array_signals_show[cont]]),point))
            {

                temp=get_prop_poly(array_signals[array_signals_show[cont]]);
                draw_poly(temp,focus_color,0,'',0);
                focused=cont;
                sig_tooltip.html(destinations_names[array_signals_show[cont]]);
                sig_tooltip.css('left',event.pageX - 30);
                sig_tooltip.css('top',event.pageY - 35);
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