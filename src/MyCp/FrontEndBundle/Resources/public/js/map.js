$(document).ready(function(){
    if(document.getElementById("map_canvas") != null)
     destination_map();
    
    if(document.getElementById("canvas-dest-prov") != null)
     map_destination_by_province();
});

function destination_map()
{
    var array_polygons = new Array();
    var array_links_prov = new Array();
    var array_signals = new Array();
    var array_signals_show = new Array();
    var canvas=document.getElementById("map_canvas");
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

    array_links_prov=['isla_de_la_juventud','camaguey','las_tunas','Holguin','guantanamo','santiago_de_cuba','granma','ciego_de_avila','sancti_spiritus',
        'villa_clara','cienfuegos','matanzas','mayabeque','la_habana','artemisa','pinar_del_rio'];

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
        else
        {
            jq_canvas.attr('width','849');
            jq_canvas.attr('height','323');
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
        sig_tooltip.css('left',-500);
        for(cont=0;cont<array_signals_show.length; cont++){
            focus_color='#3F3F3F';

            if(is_in_poly(get_prop_poly(array_signals[array_signals_show[cont]]),point))
            {

                temp=get_prop_poly(array_signals[array_signals_show[cont]]);
                draw_poly(temp,focus_color,0,'',0);
                focused=cont;
                sig_tooltip.html((destinations[array_signals_show[cont]].split('_').join(' ')).toUpperCase());
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
                window.location=link_destination +'/'+ destinations[array_signals_show[cont]];
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
        category=btn_cat.attr('data-catid');
        if(btn_cat.attr('data-focus')==1)
        {

            for(a=0; a < poly_signal_category.length;a++)
            {
                if(poly_signal_category[a] == category)
                {
                    array_signals_show=remove_from_array(array_signals_show,a);
                }
            }

            clear_signals('aa');
            btn_cat.attr('data-focus',0);
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

            btn_cat.attr('data-focus',1);
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
}

function map_destination_by_province()
{
    var canvas = document.getElementById("canvas-dest-prov");
    var polys_prov = Array;
    var ctx = canvas.getContext('2d');
    var array_signals = new Array();
    var array_circles = new Array();
    var array_signals_show = new Array();
    var canvas_left = $('#canvas-dest-prov').position().left;
    var canvas_top = $('#canvas-dest-prov').position().top;
    var sig_tooltip = $('#signal_tooltip');

    // polygon map signal
    var poly_signal = [8, 27, 1, 11, 0, 10, 0, 8, 0, 7, 0, 5, 1, 4, 2, 2, 4, 1, 7, 0, 9, 0, 12, 1, 14, 3, 15, 4, 15, 5, 16, 7, 16, 10, 15, 11, 8, 27, 8, 27, ];
    var circ_signal = [5, 8, 6, 6, 8, 5, 10, 6, 11, 8, 10, 10, 8, 11, 6, 10, 5, 8, 5, 8];

    polys_prov['poly_pr'] = [407, 32, 408, 15, 403, 2, 397, 5, 398, 12, 394, 18, 389, 16, 390, 7, 387, 2, 368, 10, 360, 19, 352, 29, 344, 30, 332, 23, 332, 29, 326, 33, 316, 35, 311, 41, 291, 39, 290, 45, 285, 51, 281, 49, 271, 52, 265, 47, 262, 49, 262, 51, 263, 53, 256, 56, 251, 59, 246, 57, 241, 60, 236, 66, 228, 66, 223, 73, 215, 74, 208, 69, 208, 77, 199, 82, 189, 90, 189, 96, 186, 97, 179, 95, 171, 102, 170, 107, 161, 116, 147, 125, 128, 153, 127, 179, 140, 191, 141, 197, 148, 202, 148, 209, 154, 208, 156, 211, 146, 220, 131, 210, 117, 217, 109, 210, 103, 210, 91, 220, 83, 221, 80, 224, 64, 226, 43, 239, 37, 233, 33, 234, 35, 240, 31, 242, 23, 242, 18, 235, 11, 246, 10, 249, 19, 257, 44, 256, 59, 242, 89, 232, 107, 231, 118, 241, 118, 246, 106, 256, 106, 267, 120, 268, 128, 263, 142, 248, 161, 241, 184, 233, 196, 233, 205, 237, 216, 223, 222, 213, 214, 207, 214, 199, 236, 179, 280, 180, 289, 178, 306, 166, 324, 179, 337, 178, 357, 164, 363, 151, 375, 141, 394, 146, 406, 124, 418, 109, 434, 105, 420, 84, 421, 70, 414, 53, 413, 48, 407, 32];
    polys_prov['poly_ij'] = [317, 101, 307, 80, 288, 65, 255, 58, 217, 48, 178, 51, 138, 64, 113, 90, 95, 111, 104, 137, 111, 159, 136, 185, 154, 219, 148, 240, 137, 255, 106, 255, 79, 239, 58, 212, 46, 210, 43, 215, 48, 230, 49, 246, 75, 282, 97, 298, 131, 305, 153, 306, 176, 319, 219, 320, 267, 302, 289, 294, 330, 273, 363, 264, 384, 232, 381, 219, 364, 193, 364, 177, 372, 158, 372, 125, 363, 112, 317, 101];
    polys_prov['poly_ar'] = [21, 75, 28, 99, 28, 142, 43, 182, 43, 188, 61, 226, 61, 235, 58, 258, 90, 305, 114, 294, 138, 274, 167, 266, 181, 229, 194, 216, 220, 219, 253, 227, 337, 225, 420, 222, 404, 198, 406, 134, 410, 95, 382, 109, 354, 84, 319, 69, 319, 50, 311, 44, 298, 52, 251, 59, 210, 62, 206, 67, 198, 83, 194, 69, 169, 67, 136, 66, 107, 69, 90, 74, 91, 77, 93, 85, 95, 92, 94, 97, 79, 98, 77, 95, 81, 76, 76, 74, 67, 76, 51, 73, 21, 75];
    polys_prov['poly_ha'] = [29, 158, 44, 174, 45, 204, 96, 232, 145, 275, 237, 227, 272, 213, 289, 211, 294, 214, 322, 198, 335, 157, 349, 145, 401, 145, 420, 125, 428, 95, 426, 86, 432, 79, 423, 62, 378, 68, 284, 61, 250, 61, 177, 87, 180, 93, 197, 99, 202, 110, 200, 122, 186, 124, 177, 114, 176, 98, 170, 92, 145, 91, 118, 104, 96, 133, 73, 135, 29, 158];
    polys_prov['poly_my'] = [179, 55, 183, 69, 180, 78, 175, 94, 158, 107, 129, 109, 123, 128, 103, 142, 92, 141, 54, 154, 48, 254, 65, 283, 99, 285, 110, 286, 162, 299, 179, 295, 247, 285, 302, 300, 364, 333, 389, 324, 402, 298, 401, 290, 369, 264, 369, 253, 369, 233, 345, 185, 354, 164, 333, 131, 353, 115, 371, 69, 336, 65, 302, 71, 265, 68, 221, 56, 179, 55];
    polys_prov['poly_ma'] = [171, 48, 159, 39, 147, 39, 131, 71, 139, 85, 136, 97, 148, 122, 146, 138, 161, 150, 163, 160, 154, 174, 142, 178, 142, 194, 126, 210, 57, 217, 17, 218, 31, 231, 64, 245, 89, 251, 98, 267, 113, 272, 154, 272, 161, 274, 178, 268, 203, 275, 211, 293, 220, 295, 234, 288, 246, 290, 252, 284, 244, 255, 244, 247, 255, 255, 260, 290, 317, 305, 357, 303, 367, 300, 404, 304, 403, 297, 398, 294, 339, 282, 326, 257, 323, 225, 334, 208, 363, 202, 381, 207, 405, 208, 410, 198, 412, 170, 417, 149, 398, 108, 389, 121, 362, 104, 361, 93, 365, 83, 365, 76, 373, 71, 390, 75, 395, 69, 395, 57, 380, 51, 364, 56, 332, 62, 310, 62, 297, 54, 293, 50, 289, 50, 275, 68, 257, 68, 233, 51, 242, 37, 249, 35, 268, 33, 270, 29, 268, 27, 260, 27, 212, 47, 191, 54, 180, 64, 168, 67, 159, 66, 157, 62, 161, 55, 171, 48];
    polys_prov['poly_vc'] = [87, 175, 117, 163, 131, 171, 138, 179, 147, 166, 158, 164, 172, 170, 181, 178, 180, 198, 182, 218, 199, 234, 216, 267, 221, 308, 232, 330, 243, 340, 245, 328, 243, 321, 276, 318, 289, 320, 299, 294, 296, 279, 288, 268, 336, 280, 356, 285, 368, 283, 395, 258, 403, 258, 417, 248, 395, 223, 391, 202, 391, 195, 406, 181, 418, 175, 414, 171, 408, 173, 404, 179, 388, 192, 374, 183, 357, 170, 325, 122, 313, 123, 293, 112, 274, 109, 267, 96, 265, 99, 257, 100, 231, 78, 223, 74, 188, 70, 176, 79, 161, 81, 145, 77, 138, 71, 129, 71, 119, 72, 97, 60, 86, 60, 77, 45, 69, 38, 70, 48, 56, 60, 34, 56, 34, 64, 30, 78, 51, 97, 60, 86, 67, 86, 73, 95, 91, 132, 87, 175];
    polys_prov['poly_cf'] = [39, 129, 51, 189, 65, 205, 110, 219, 155, 226, 170, 235, 172, 250, 197, 252, 204, 246, 201, 240, 183, 222, 189, 211, 207, 213, 223, 231, 223, 240, 210, 254, 239, 272, 309, 330, 352, 344, 378, 302, 397, 300, 382, 282, 370, 253, 360, 190, 339, 148, 312, 124, 313, 106, 314, 97, 309, 91, 310, 62, 306, 58, 281, 49, 275, 54, 259, 78, 242, 59, 226, 51, 200, 63, 183, 69, 182, 90, 166, 105, 140, 105, 122, 103, 97, 94, 65, 100, 46, 113, 39, 129];
    polys_prov['poly_ca'] = [298, 96, 285, 92, 248, 84, 229, 71, 217, 68, 192, 52, 191, 69, 184, 77, 178, 75, 167, 59, 164, 47, 121, 49, 122, 64, 108, 90, 88, 112, 86, 123, 112, 154, 118, 166, 109, 173, 90, 176, 80, 215, 75, 239, 63, 264, 77, 264, 91, 264, 107, 261, 113, 267, 114, 282, 184, 269, 201, 289, 182, 296, 201, 299, 206, 306, 225, 285, 264, 262, 296, 236, 302, 220, 314, 212, 328, 214, 340, 223, 351, 212, 358, 199, 352, 190, 332, 189, 357, 161, 361, 161, 381, 144, 345, 124, 322, 119, 306, 115, 298, 107, 298, 96];
    polys_prov['poly_ss'] = [272, 89, 249, 107, 239, 109, 214, 133, 200, 138, 171, 132, 135, 122, 138, 136, 124, 176, 78, 173, 77, 186, 69, 203, 48, 203, 34, 230, 52, 235, 58, 250, 81, 247, 94, 247, 100, 256, 104, 265, 117, 266, 133, 263, 197, 280, 221, 291, 264, 301, 304, 306, 331, 305, 345, 299, 366, 298, 370, 289, 367, 281, 359, 282, 346, 285, 333, 283, 313, 286, 331, 245, 341, 193, 349, 183, 372, 177, 340, 137, 342, 123, 357, 108, 371, 88, 376, 77, 374, 62, 350, 63, 343, 64, 324, 64, 311, 68, 295, 66, 281, 66, 267, 57, 248, 48, 238, 39, 239, 53, 249, 69, 272, 89];
    polys_prov['poly_cm'] = [190, 47, 176, 58, 173, 59, 164, 68, 176, 74, 174, 86, 159, 101, 151, 94, 144, 93, 137, 99, 137, 105, 116, 122, 89, 139, 75, 153, 92, 178, 101, 209, 108, 245, 124, 260, 148, 273, 156, 286, 170, 291, 171, 298, 193, 312, 226, 310, 232, 285, 243, 273, 262, 272, 267, 252, 281, 259, 303, 237, 308, 229, 313, 229, 324, 245, 336, 249, 337, 237, 342, 231, 352, 231, 363, 221, 375, 195, 367, 185, 373, 179, 379, 166, 389, 158, 406, 159, 391, 146, 380, 132, 365, 122, 355, 121, 346, 113, 313, 97, 305, 92, 292, 93, 305, 97, 320, 113, 328, 119, 339, 127, 340, 136, 335, 136, 320, 130, 314, 126, 303, 108, 269, 105, 258, 102, 248, 89, 233, 74, 225, 85, 220, 84, 212, 70, 202, 61, 190, 47];
    polys_prov['poly_lt'] = [220, 108, 211, 127, 198, 151, 181, 161, 172, 160, 169, 165, 170, 185, 143, 177, 131, 158, 125, 165, 93, 196, 78, 191, 73, 212, 50, 213, 38, 218, 29, 251, 86, 261, 121, 262, 157, 251, 181, 265, 188, 246, 199, 240, 213, 243, 221, 242, 252, 242, 274, 241, 288, 243, 298, 255, 322, 255, 316, 232, 316, 222, 319, 215, 317, 194, 326, 188, 361, 185, 379, 167, 385, 154, 399, 147, 415, 143, 414, 121, 406, 116, 375, 116, 371, 120, 373, 129, 364, 141, 351, 138, 346, 131, 357, 118, 339, 109, 337, 118, 331, 127, 315, 117, 311, 106, 317, 104, 325, 107, 333, 99, 325, 98, 308, 91, 290, 88, 260, 70, 251, 77, 247, 74, 250, 68, 231, 67, 221, 75, 218, 85, 212, 95, 220, 108];
    polys_prov['poly_ho'] = [101, 77, 101, 96, 91, 100, 78, 104, 74, 108, 69, 119, 55, 136, 32, 134, 20, 138, 21, 155, 18, 161, 25, 185, 25, 189, 34, 188, 43, 187, 47, 191, 49, 203, 54, 206, 67, 203, 75, 205, 90, 220, 97, 220, 118, 220, 124, 224, 125, 234, 123, 242, 136, 242, 137, 229, 141, 224, 164, 220, 172, 236, 199, 246, 210, 240, 236, 239, 252, 228, 286, 212, 291, 215, 290, 240, 323, 242, 337, 232, 365, 232, 372, 239, 374, 230, 398, 241, 407, 244, 417, 238, 421, 225, 432, 212, 413, 210, 400, 200, 388, 195, 347, 195, 316, 190, 278, 184, 264, 175, 264, 179, 272, 185, 268, 190, 255, 189, 246, 182, 243, 190, 233, 195, 219, 191, 213, 179, 212, 166, 216, 160, 229, 159, 241, 159, 254, 168, 257, 167, 245, 156, 241, 155, 231, 157, 225, 152, 224, 144, 228, 142, 239, 146, 250, 140, 255, 127, 245, 115, 239, 116, 234, 114, 232, 105, 208, 103, 187, 111, 159, 112, 143, 101, 101, 77];
    polys_prov['poly_gr'] = [149, 177, 149, 186, 115, 197, 98, 211, 90, 212, 86, 219, 70, 229, 64, 237, 52, 249, 46, 260, 29, 276, 27, 283, 32, 287, 102, 281, 115, 281, 141, 267, 154, 266, 177, 271, 201, 273, 200, 254, 261, 230, 276, 230, 305, 238, 324, 224, 328, 227, 358, 215, 353, 185, 358, 166, 371, 143, 388, 149, 387, 135, 392, 130, 402, 135, 405, 122, 406, 113, 400, 110, 385, 109, 373, 111, 354, 97, 347, 92, 331, 93, 323, 95, 317, 91, 315, 71, 302, 72, 296, 74, 258, 72, 248, 59, 225, 59, 216, 61, 200, 60, 193, 59, 185, 59, 180, 61, 170, 59, 164, 57, 159, 60, 151, 78, 156, 82, 157, 100, 155, 104, 178, 112, 198, 103, 203, 106, 206, 116, 205, 115, 186, 124, 192, 137, 185, 156, 165, 175, 156, 179, 149, 177];
    polys_prov['poly_sc'] = [211, 139, 189, 130, 177, 154, 175, 178, 179, 195, 171, 204, 143, 213, 137, 212, 123, 224, 115, 224, 83, 216, 72, 217, 19, 238, 19, 252, 24, 255, 40, 250, 86, 239, 125, 237, 134, 242, 174, 234, 194, 234, 216, 228, 249, 230, 273, 237, 302, 233, 315, 213, 317, 235, 357, 238, 381, 250, 408, 256, 435, 253, 435, 249, 414, 231, 409, 216, 410, 202, 415, 195, 408, 148, 427, 117, 420, 109, 422, 81, 390, 96, 363, 112, 332, 113, 315, 122, 295, 115, 280, 106, 273, 90, 247, 94, 249, 115, 226, 116, 219, 127, 210, 115, 211, 139];
    polys_prov['poly_gu'] = [254, 66, 241, 82, 236, 100, 223, 113, 196, 112, 172, 101, 171, 117, 150, 99, 116, 98, 96, 113, 53, 112, 43, 134, 31, 153, 30, 177, 40, 208, 31, 220, 32, 239, 48, 255, 61, 268, 62, 277, 76, 280, 116, 266, 121, 243, 135, 226, 148, 226, 154, 238, 131, 253, 132, 274, 145, 272, 171, 271, 182, 260, 187, 249, 206, 247, 214, 237, 224, 233, 255, 225, 281, 230, 370, 217, 393, 217, 394, 223, 402, 218, 404, 204, 422, 196, 434, 176, 409, 152, 393, 158, 377, 162, 354, 155, 341, 143, 314, 132, 300, 109, 256, 76, 254, 66];

    init_map();

    function init_map()
    {
        signal_pos.forEach(function(element) {
            temp = get_prop_signal_position(element);
            temp2 = get_prop_signal_circle(element);
            array_signals.push(temp);
            array_circles.push(temp2);
        });
    }

    $('#prov-links li').click(function() {
        $('#prov-links li').removeClass('focus');
        $('#prov-name').html($(this).html());
        poly_prov = $(this).attr('data');
        $(this).addClass('focus');
        clear_canvas();
        draw_poly(polys_prov[poly_prov], '#F4961C', 0, 0, 1);
        prov = $(this).attr('data-prov');
        array_signals_show = Array();
        for (u = 0; u < dest_provinces.length; u++)
        {
            if (dest_provinces[u] == prov)
            {
                draw_poly(array_signals[u], '#22519D', 0, '', 0);
                draw_poly(array_circles[u], '#fff', 0, '', 0);
                if (destinations_icons[u] != 'null')
                    draw_image(signal_pos[u][0] - 6, signal_pos[u][1] + 35, dir + destinations_icons[u]);
                array_signals_show.push(u);
                coord_x = signal_pos[u][0] * $('#canvas-dest-prov').attr('width') / 446;
                coord_y = signal_pos[u][1] * $('#canvas-dest-prov').attr('height') / 358;

            }
        }


        var province_name = $(this).html();
        var url = $('#housesList').attr('data-url');
        var result = $('#housesList');
        $.post(url, {
            'province_name': province_name
        }, function(data) {
            result.html(data);
            /*$('img.lazy').jail();*/
        });

        // $('#housesList').html();
    });

    function clear_canvas()
    {
        ctx.clearRect(0, 0, 446, 358);
        ctx.save();
    }

    function get_prop_signal_circle(signal_pos)
    {
        cnv = $('#canvas-dest-prov');
        new_poly = Array();
        for (item = 0; item < circ_signal.length; item += 2)
        {

            new_coord_x = circ_signal[item] + signal_pos[0];
            new_coord_y = circ_signal[item + 1] + signal_pos[1];

            new_coord_x = new_coord_x * cnv.attr('width') / 446;
            new_coord_y = new_coord_y * cnv.attr('height') / 358;

            new_poly.push(new_coord_x);
            new_poly.push(new_coord_y);
        }
        return new_poly;
    }

    function get_prop_signal_position(signal_pos)
    {
        new_poly = Array();
        cvas = $('#canvas-dest-prov');
        for (item = 0; item < poly_signal.length; item += 2)
        {

            new_coord_x = poly_signal[item] + signal_pos[0];
            new_coord_y = poly_signal[item + 1] + signal_pos[1];
            new_coord_x = new_coord_x * cvas.attr('width') / 446;
            new_coord_y = new_coord_y * cvas.attr('height') / 358;
            new_poly.push(new_coord_x);
            new_poly.push(new_coord_y);
        }
        return new_poly;
    }

    function get_prop_poly(poly)
    {
        new_poly = Array();
        cnv = $('#canvas-dest-prov');
        for (item = 0; item < poly.length; item += 2)
        {
            new_coord_x = poly[item] * cnv.attr('width') / 446;
            new_coord_y = poly[item + 1] * cnv.attr('height') / 358;
            new_poly.push(new_coord_x);
            new_poly.push(new_coord_y);
        }

        return new_poly;

    }


    function draw_image(pos_x, pos_y, dir_icon)
    {
        imageObj = new Image();
        imageObj.src = dir_icon;
        ctx.shadowOffsetY = 0;
        ctx.shadowBlur = 0;
        ctx.drawImage(imageObj, pos_x, pos_y);
    }

    function draw_poly(poly_points, color, stroke_width, stroke_color, shadow)
    {
        ctx.fillStyle = color;
        ctx.beginPath();

        ctx.moveTo(poly_points[0], poly_points[1]);
        for (item = 2; item < poly_points.length - 1; item += 2)
        {
            ctx.lineTo(poly_points[item], poly_points[item + 1])
        }
        ctx.closePath();
        if (shadow == 1)
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
        if (stroke_width != 0)
        {
            ctx.lineWidth = stroke_width;
            ctx.strokeStyle = stroke_color;
            ctx.stroke();
        }
    }
    function do_mouse_move(event)
    {
        clear_canvas();
        point = [parseInt(event.pageX - canvas_left), parseInt(event.pageY - canvas_top)];
        sig_tooltip.css('left', -500);
        draw_poly(polys_prov[poly_prov], '#F4961C', 0, 0, 1);

        focused = null;
        for (cont = 0; cont < array_signals_show.length; cont++) {

            if (destinations_icons[array_signals_show[cont]] != 'null')
                draw_image(signal_pos[array_signals_show[cont]][0] - 6, signal_pos[array_signals_show[cont]][1] + 35, dir + destinations_icons[array_signals_show[cont]]);

            focus_color = '#3F3F3F';
            if (is_in_poly(get_prop_poly(array_signals[array_signals_show[cont]]), point))
            {

                temp = get_prop_poly(array_signals[array_signals_show[cont]]);
                draw_poly(temp, focus_color, 0, '', 0);
                focused = cont;
                sig_tooltip.html(destinations_names[array_signals_show[cont]]);
                sig_tooltip.css('left', event.pageX - 30);
                sig_tooltip.css('top', event.pageY - 35);

            }
            else
            {
                for (d = 0; d < array_signals_show.length; d++)
                {
                    if (d != focused)
                    {
                        temp = get_prop_poly(array_signals[array_signals_show[d]]);
                        draw_poly(temp, '#22519D', 0, '', 0);

                        temp2 = get_prop_signal_circle(signal_pos[array_signals_show[d]]);
                        draw_poly(temp2, '#fff', 0, '', 0);

                    }
                }
            }

        }

    }

    function do_mouse_click(event)
    {
        point = [parseInt(event.pageX - canvas_left), parseInt(event.pageY - canvas_top)];
        for (cont = 0; cont < array_signals_show.length; cont++) {
            if (is_in_poly(get_prop_poly(array_signals[array_signals_show[cont]]), point))
            {
                name = destinations_names[array_signals_show[cont]];
                name = name.toLowerCase();
                name = name.split(' ').join('-');
                window.location = link_destination + '/' + name;
            }

        }

    }

    function is_in_poly(point_list, p)
    {

        var counter = 0;
        var i;
        var xinterest;
        var p1 = [0, 0];
        var p2 = [0, 0];
        var n = point_list.length / 2;
        p1 = [point_list[0], point_list[1]];

        for (i = 1; i <= n; i++)
        {
            p2 = [point_list[i % n * 2], point_list[i % n * 2 + 1]];
            if (p[1] > Math.min(p1[1], p2[1]))
            {
                if (p[1] <= Math.max(p1[1], p2[1]))
                {
                    if (p[0] <= Math.max(p1[0], p2[0]))
                    {
                        if (p1[1] != p2[1])
                        {
                            xinterest = (p[1] - p1[1]) * (p2[0] - p1[0]) / (p2[1] - p1[1]) + p1[0];
                            if (p1[0] == p2[0] || p[0] <= xinterest)
                            {
                                counter++;
                            }
                        }
                    }
                }
            }
            p1 = p2;
        }
        if (counter % 2 == 0) {
            return false;
        }
        else
        {
            return true;
        }
    }

    canvas.addEventListener('mousemove', do_mouse_move, null);
    canvas.addEventListener('click', do_mouse_click, null);
    $('#first-prov').trigger('click');
}