$(document).ready(function(){
    var array_polygons = new Array();
    var array_links_prov = new Array();
    var array_signals = new Array();
    var array_signals_show = new Array();
    var canvas=document.getElementById("map_canvas")
    var ctx=canvas.getContext('2d');
    var canvas_left=$('#map_canvas').position().left;
    var canvas_top=$('#map_canvas').position().top;

    //add polygons provinces
    var poly_ij=[150,140,151,145,155,150,158,155,158,159,156,162,151,163,147,161,145,158,143,156,142,158,143,162,147,166,154,169,161,170,170,170,177,167,182,165,187,163,191,158,188,152,188,150,189,147,188,141,183,140,177,134,170,133,167,132,157,133,152,137,150,140];
    var poly_cg=[492,173,501,186,503,199,503,205,509,213,518,219,523,226,526,226,534,234,549,234,552,225,554,221,564,219,565,213,571,214,582,203,592,210,594,204,600,204,607,190,605,187,611,176,619,176,603,162,579,150,594,163,593,167,590,167,579,157,565,156,553,143,549,148,536,133,526,140,531,144,525,153,519,150,514,157,504,164,492,173];
    var poly_lt=[553,233,564,235,582,236,589,234,592,234,597,237,603,229,616,231,630,230,634,234,641,234,638,224,640,221,639,216,645,213,653,213,660,204,663,202,668,200,669,193,657,192,656,196,653,200,648,197,652,193,647,190,644,196,637,190,639,188,644,187,633,184,622,179,620,181,618,180,619,178,613,177,608,186,610,188,610,192,604,202,600,206,596,206,595,208,596,214,592,214,588,212,584,207,583,206,573,217,568,216,566,221,558,222,555,226,553,233];
    var poly_ho=[644,234,650,234,652,236,652,241,656,241,661,241,667,246,676,245,679,248,679,253,683,253,683,249,689,246,694,246,697,251,705,255,709,253,719,253,739,243,740,253,752,254,756,250,766,250,769,251,770,251,779,253,784,254,785,249,790,243,783,242,775,237,763,238,735,233,731,230,732,234,730,236,724,233,721,237,714,236,710,230,712,225,716,224,721,224,725,226,727,226,722,223,716,223,715,218,718,218,724,217,725,211,719,208,718,204,709,204,700,207,690,207,681,200,672,196,671,202,664,204,656,214,643,216,642,225,644,234];
    var poly_gt=[743,256,737,264,737,272,740,280,738,282,738,288,744,295,745,297,750,298,757,295,759,289,765,283,768,287,763,291,763,296,773,295,777,290,781,290,784,286,794,284,807,284,828,282,829,283,831,283,831,278,837,274,837,271,832,266,826,268,819,268,812,263,808,261,806,257,793,245,789,251,787,255,780,257,774,254,767,253,757,253,753,256,743,256];
    var poly_st=[742,297,734,289,734,283,736,280,734,274,734,266,739,257,738,255,738,247,722,255,711,256,707,258,695,255,694,250,686,250,686,256,680,257,678,259,675,257,675,262,670,261,665,268,665,281,662,284,652,286,649,289,642,288,635,287,618,293,618,297,621,298,636,293,650,293,652,294,673,290,685,290,696,293,702,292,705,285,707,292,719,293,727,297,742,297];
    var poly_gr=[562,302,583,301,596,296,609,298,614,298,614,293,632,285,639,285,647,287,651,283,663,281,662,268,667,258,671,260,672,254,675,254,676,256,677,249,667,248,660,243,652,243,649,237,632,237,629,233,617,233,604,232,600,237,601,246,608,249,613,246,616,248,615,251,611,252,611,259,608,266,600,269,599,271,590,274,583,280,576,285,562,299,562,302];
    var poly_ca=[455,162,464,162,466,162,467,167,484,164,489,169,484,170,489,171,490,172,510,158,512,156,513,152,515,150,519,149,524,151,528,147,526,144,522,143,533,132,518,126,513,123,512,120,502,117,487,109,485,115,482,114,479,107,469,108,469,114,461,123,461,128,468,136,468,139,462,141,458,150,455,162];
    var poly_ss=[381,150,384,152,386,156,395,155,398,159,407,159,419,162,426,165,437,169,450,170,459,168,465,167,465,165,459,165,451,165,454,156,456,148,458,140,461,138,466,137,459,129,458,124,466,113,466,109,449,109,443,109,433,103,434,107,440,115,434,120,424,128,419,127,407,124,407,130,404,137,397,137,392,137,391,141,388,144,385,144,381,150];
    var poly_vc=[389,139,390,135,402,135,405,129,404,124,402,121,412,122,420,125,424,125,432,117,438,115,431,107,431,101,431,99,438,94,437,93,429,99,420,92,412,80,401,76,397,76,397,73,393,73,385,67,374,65,371,68,365,68,359,65,355,66,346,62,342,57,341,60,339,62,333,62,331,67,336,72,339,69,341,70,347,81,346,94,355,91,360,95,365,90,372,94,373,106,379,113,383,125,389,139];
    var poly_cf=[378,149,383,141,387,141,380,133,380,123,378,117,370,109,370,96,365,93,361,98,355,94,346,96,345,103,342,104,334,103,329,102,321,104,319,109,319,114,321,120,326,124,331,124,337,125,342,126,345,131,349,131,345,126,346,123,350,122,353,125,355,129,354,133,364,142,378,149 ];
    var poly_mt=[341,131,330,131,318,132,304,130,297,116,291,115,292,126,282,129,279,123,273,121,266,122,260,121,249,122,245,119,243,116,237,114,221,106,238,105,253,104,259,99,261,93,266,88,266,83,261,80,262,73,258,68,259,64,256,59,261,50,268,52,264,56,268,59,281,52,291,47,296,46,299,46,299,47,296,48,292,48,288,52,294,58,301,58,305,53,312,57,321,56,333,53,338,55,338,59,336,60,329,60,328,68,337,75,339,72,345,82,342,101,335,101,327,100,320,102,315,108,317,118,322,126,338,129,341,131];
    var poly_mb=[258,49,251,48,244,49,236,46,229,47,227,53,222,55,219,60,209,61,208,77,210,82,219,83,225,85,232,83,241,83,248,85,256,90,261,89,263,85,258,80,259,74,255,68,255,64,253,60,257,54,258,49];
    var poly_lh=[193,53,195,57,202,62,212,57,216,58,218,53,223,52,226,49,226,46,213,46,208,47,206,48,208,51,209,52,204,53,202,49,198,51,193,53];
    var poly_ar=[146,60,147,64,147,70,152,83,151,87,156,95,164,90,168,88,170,84,171,81,177,82,178,83,206,82,204,79,205,63,201,65,191,60,191,56,184,57,175,58,173,62,170,63,170,58,158,59,158,63,153,64,153,60,146,60];
    var poly_pr=[141,61,139,62,140,64,140,66,137,66,136,61,130,62,124,69,120,70,116,68,116,70,110,72,108,74,101,73,101,76,95,77,92,76,92,78,89,80,85,80,82,83,79,83,78,85,75,85,73,83,72,84,73,86,72,88,68,90,67,93,62,93,58,98,53,103,48,109,44,116,45,123,50,128,53,132,54,134,51,137,45,134,41,136,39,135,38,133,35,134,33,136,29,137,22,139,17,143,14,144,12,141,11,142,12,144,7,144,7,142,6,142,4,144,3,146,6,149,16,150,22,144,29,142,35,140,38,141,41,143,41,146,37,150,37,152,42,153,49,147,57,144,63,141,70,142,72,143,73,143,73,142,73,140,75,138,77,135,76,133,74,133,75,128,79,125,83,123,93,122,101,122,104,119,107,118,109,119,113,122,119,122,123,119,126,116,128,111,131,109,134,109,138,111,140,106,145,99,152,97,147,89,148,84,143,73,142,64,141,61];

    // polygon map signal
    var poly_signal=[8,27,1,11,0,10,0,8,0,7,0,5,1,4,2,2,4,1,7,0,9,0,12,1,14,3,15,4,15,5,16,7,16,10,15,11,8,27,8,27,];
    var circ_signal=[5,8,6,6,8,5,10,6,11,8,10,10,8,11,6,10,5,8,5,8];

    sig_tooltip=$('#signal_tooltip');

    array_polygons=[poly_ij,poly_cg,poly_lt,poly_ho,poly_gt,poly_st,poly_gr,poly_ca,poly_ss,poly_vc,poly_cf,poly_mt,poly_mb,
        poly_lh,poly_ar,poly_pr]

    array_links_prov=['isla_de_la_juventud','camagüey','las_tunas','holguín','guantánamo','santiago_de_cuba','granma','ciego_de_ávila','sancti_spiritus',
        'villa_clara','cienfuegos','matanzas','mayabeque','la_habana','artemisa','pinar_del_río'];

    array_names_prov_pos=[106,185,420,212,610,150,709,194,762,225,625,318,530,254,489,99,348,185,363,50,281,153,
        208,139,232,34,177,15,122,38,15,58];

    poly_signal_positions.forEach(function(element){
        temp=get_prop_signal_position(element);
        array_signals.push(temp);
    });

    init_canvas();
    init_map();

    $(window).bind('resize', function () {
        canvas_left=$('#map_canvas').position().left;
        canvas_top=$('#map_canvas').position().top;
        clear_canvas();
        init_canvas();
        init_map();
    });

    function init_canvas()
    {
        jq_canvas=$('#map_canvas');
        win=$(window);
        if( win.width() < 850)
        {
            canvas_w=win.width();
            jq_canvas.attr('width',canvas_w);
            jq_canvas.attr('height',((win.width()) * 323 / 849));
        }

    }
    function init_map()
    {
        array_polygons.forEach(function(element){
            draw_poly(get_prop_poly(element),'#8C8B8B',0,'#616060',1);
        });

        draw_prov_names();

        array_signals_show.forEach(function(element){
            temp=get_prop_signal_position(poly_signal_positions[element]);
            draw_poly(temp,'#22519D',0,'',0);
            temp2=get_prop_signal_circle(poly_signal_positions[element]);
            draw_poly(temp2,'#fff',0,'',0);
        });



    }

    function draw_prov_names()
    {
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
        ctx.shadowBlur = 0;
        ctx.shadowColor='';
        ctx.fillStyle="#bdbcbc";
        ctx.font="14px Arial";
        c_prov=0;
        for(j=0;j < array_names_prov_pos.length ; j+=2)
        {
           coord_x= array_names_prov_pos[j] * $('#map_canvas').attr('width') / 849 ;
           coord_y= array_names_prov_pos[j + 1] * $('#map_canvas').attr('height') /323;
           name=array_links_prov[c_prov];
           name=name.split('_').join(' ');
           ctx.fillText(name,coord_x,coord_y);
            c_prov++;
        }
    }

    function get_prop_signal_position(signal_pos)
    {
        new_poly=Array();
        cvas=$('#map_canvas');
        for( item=0 ; item < poly_signal.length ; item+=2 )
        {

            new_coord_x= poly_signal[item]+signal_pos[0];
            new_coord_y= poly_signal[item + 1]+signal_pos[1];
            new_coord_x = new_coord_x * cvas.attr('width') / 849;
            new_coord_y = new_coord_y * cvas.attr('height') / 323;
            new_poly.push(new_coord_x);
            new_poly.push(new_coord_y);
        }
        return new_poly;
    }

    function get_prop_signal_circle(signal_pos)
    {
        new_poly=Array();
        for( item=0 ; item < circ_signal.length ; item+=2 )
        {

            new_coord_x= circ_signal[item]+signal_pos[0];
            new_coord_y= circ_signal[item + 1]+signal_pos[1];

            new_coord_x = new_coord_x * $('#map_canvas').attr('width') / 849;
            new_coord_y = new_coord_y * $('#map_canvas').attr('height') / 323;

            new_poly.push(new_coord_x);
            new_poly.push(new_coord_y);
        }
        return new_poly;
    }

    function get_prop_poly(poly)
    {
        new_poly=Array();
        for( item=0 ; item < poly.length ; item+=2 )
        {
            new_coord_x= poly[item] * $('#map_canvas').attr('width') / 849;
            new_coord_y= poly[item + 1] * $('#map_canvas').attr('height') / 323;
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

    function remove_from_array(array,value)
    {
        ret_array=Array();
        for(r=0;r<array.length; r++)
        {
            if(array[r]!=value)
            {
                ret_array.push(array[r]);
            }
        }
        return ret_array;
    }

    function do_mouse_move(event)
    {
        clear_canvas();

        point = [parseInt(event.pageX - canvas_left),parseInt(event.pageY -canvas_top)];


        for(cont=0;cont<array_polygons.length; cont++){

            if(is_in_poly(get_prop_poly(array_polygons[cont]),point))
            {
                draw_poly(get_prop_poly(array_polygons[cont]),'#F4961C',0,'',1);
            }
            else
            {
                draw_poly(get_prop_poly(array_polygons[cont]),'#8C8B8B',0,'',1);
            }

        }
        draw_prov_names();
        focused=null;
        for(cont=0;cont<array_signals_show.length; cont++){
            focus_color='#3F3F3F';

            if(is_in_poly(get_prop_poly(array_signals[array_signals_show[cont]]),point))
            {

                temp=get_prop_poly(array_signals[array_signals_show[cont]]);
                draw_poly(temp,focus_color,0,'',0);
                focused=cont;
                sig_tooltip.html((destinations[cont].split('_').join(' ')).toUpperCase());
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

                        temp2=get_prop_signal_circle(poly_signal_positions[array_signals_show[d]]);
                        draw_poly(temp2,'#fff',0,'',0);

                    }
                }
                sig_tooltip.css('left',-500);

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

    function do_mouse_click(event)
    {

        point = [parseInt(event.pageX - canvas_left),parseInt(event.pageY -canvas_top)];
        for(cont=0;cont<array_signals_show.length; cont++){
            if(is_in_poly(get_prop_poly(array_signals[array_signals_show[cont]]),point))
            {
                window.location=link_destination +'/'+ destinations[cont];
            }

        }
        for(cont=0;cont<array_polygons.length; cont++){

            if(is_in_poly(get_prop_poly(array_polygons[cont]),point))
            {
                window.location=accommodations_url+'/'+array_links_prov[cont];
                break;
            }
        }


    }

    function clear_canvas()
    {
        ctx.clearRect(0,0,849,323);
        ctx.save();
    }

    function clear_signals(category)
    {
        clear_canvas();
        init_map();
    }

    function item_in_array(array,item)
    {

        for(a=0;a < array.length ; a++)
        {
            if(array[a] == item)
                return true;
        }
        return false;
    }

    $('.icon-destination').click(function(){
        btn_cat=$(this);
        category=btn_cat.attr('data');
        if(btn_cat.attr('focus')==1)
        {

            for(a=0; a < poly_signal_category.length;a++)
            {
                if(poly_signal_category[a] == category)
                {
                    array_signals_show=remove_from_array(array_signals_show,a);
                }
            }

            clear_signals('aa');
            btn_cat.attr('focus',0);
        }
        else
        {

            for(y=0; y < poly_signal_category.length;y++)
            {
                if(poly_signal_category[y] == category)
                {

                    if(item_in_array(array_signals_show,y)==false)
                    {
                        array_signals_show.push(y);
                    }
                    temp=get_prop_signal_position(poly_signal_positions[y]);
                    draw_poly(temp,'#22519D',0,'',0);
                    for(d=0;d < array_signals_show.length; d++)
                    {
                        temp2=get_prop_signal_circle(poly_signal_positions[array_signals_show[d]]);
                        draw_poly(temp2,'#fff',0,'',0);
                    }

                }
            }

            btn_cat.attr('focus',1);
        }

        bg=btn_cat.css('backgroundPosition');

        bg_array=bg.split(' ');
        for(a=0;a < bg_array.length; a++)
        {
            bg_array[a]=parseInt(bg_array[a].replace('px',''));
        }
        class_element=btn_cat.attr('class');
        class_element_array=class_element.split(' ');

        if(class_element_array[1])
        {
            bg_array[0]=bg_array[0]+28 + 'px';
            btn_cat.removeClass('focus');
        }
        else
        {
            bg_array[0]=bg_array[0]-28 + 'px';
            btn_cat.addClass('focus');
        }
        bg_array[1]=bg_array[1] + 'px';
        btn_cat.css('backgroundPosition',bg_array[0]+' '+bg_array[1]);



    });
    canvas.addEventListener('click',do_mouse_click,null);
    canvas.addEventListener('mousemove', do_mouse_move,null);
    $('#first_atraction').trigger('click');

});