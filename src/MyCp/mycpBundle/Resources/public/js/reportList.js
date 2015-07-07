$(document).ready(init);

function init()
{
    generateExportUrl();

    $('#filter_nomenclator').change(function() {
        generateExportUrl();
    });

    $('#filter_province').change(function(){
        code = $('#filter_province').val();
        if(code!='')
        {
            $('#filter_municipality').html('<option value="">Cargando...</option>');
            $.ajax({
                type:"POST",
                url:urlGetMun + '/' + code,
                success:function (msg) {

                    $('#filter_municipality').html(msg);
                }
            });

            generateExportUrl();
        }
    });
    $('#filter_municipality').change(function() {

        $('#filter_destination').html('<option value="">Cargando...</option>');
        mun = $('#filter_municipality').val();
        prov = $('#filter_province').val();
        generateExportUrl();
        $.ajax({
            type: "POST",
            url: url_destination + '/' + mun + '/' + prov,
            success: function(msg) {

                $('#filter_destination').html(msg);
            }
        });
    });
}

function generateUrl(isForExport)
{
    filter_nomenclator = $('#filter_nomenclator').val();
    if (filter_nomenclator == '')
    {
        $("#lblNomenclator").addClass("error");
        $("#filter_nomenclator").addClass("errorControl");
    }
    else {
        $("#lblNomenclator").removeClass("error");
        $("#filter_nomenclator").removeClass("errorControl");

        filter_province = $('#filter_province').val();
        if (filter_province == '')
            filter_province = '-1';
        filter_municipality = $('#filter_municipality').val();
        if (filter_municipality == '')
            filter_municipality = '-1';

        if (isForExport)
            url = urlExcel;
        else
            url = urlFilter;

        url = url.replace("_nomenclator", filter_nomenclator);
        url = url.replace("_province", filter_province);
        url = url.replace("_municipality", filter_municipality);
        return url;
    }
    return "#";
}

function submit()
{
    url_submit = generateUrl(false);

    $('#filter').attr('action', url_submit);
    $('#filter').submit();
}

function generateExportUrl()
{
    url = generateUrl(true);
    $("#bExportExcel").attr("href",url);
}