$(document).ready(init);

function init()
{
    $('#ddlCategory').change(loadReportsByCategory);
    $('#ddlReport').change(loadParameters);
    $("#bViewReport").attr("disabled", "disabled");
    $("#bExcel").attr("disabled", "disabled");

    $("#bViewReport").click(viewReport);
}

function loadReportsByCategory()
{
    var category = $('#ddlCategory').val();
    if (category != '')
    {
        $('#ddlReport').html('<option value="">Cargando...</option>');

        $.post(
            loadReportsUrl,
            {'category':category},
            function(data)
            {
                $('#ddlReport').html(data);
            });
    }
}

function loadParameters()
{
    var report = $('#ddlReport').val();
    if (report != '')
    {
        $('#lblLoadingReport').html('Cargando...');

        $.post(
            loadParametersUrl,
            {'report':report},
            function(data)
            {
                $('#divParameters').html(data);
                $('#lblLoadingReport').html('&nbsp;');
                $("#bViewReport").removeAttr("disabled");
            });
    }
}

function viewReport()
{
    $('#reportContent').html('Cargando...');

    var reportUrl = $('#hdReportUrl').val();
    var exportUrl = $('#hdReportExportUrl').val();
    var parameters = $('#hdParamText').val();

    var dateRangeFrom = ($('#dateRangeFrom').val() !== undefined) ?  dateToYMD($('#dateRangeFrom').val()) : "";
    console.log($('#dateRangeFrom').val());
    parameters = parameters.replace('_dateFrom', dateRangeFrom);

    var dateRangeTo = ($('#dateRangeTo').val() !== undefined) ?   dateToYMD($('#dateRangeTo').val()) : "";
    parameters = parameters.replace('_dateTo', dateRangeTo);

    var dateParam = ($('#dateParam').val() !== undefined) ? dateToYMD($('#dateParam').val()) : "";
    parameters = parameters.replace('_date', dateParam);

    var location = "";
    var filter_province = ($('#filter_province').val() !== undefined) ?   $('#filter_province').val() : "";
    if(filter_province!=''){
        location += filter_province;
        parameters = parameters.replace('_location', filter_province);
    }
    else {
        location += "-1";
        parameters = parameters.replace('_location', 'all');
    }
    var filter_municipality = ($('#filter_municipality').val() !== undefined) ?   $('#filter_municipality').val() : "";
    if(filter_municipality!='') {
        location += "/" + filter_municipality;
        parameters = parameters.replace('_location', filter_municipality);
    }

    var filter_destination = ($('#filter_destination').val() !== undefined) ?   $('#filter_destination').val() : "";
    if(filter_destination!='') {
        location += "/" + filter_destination;
        parameters = parameters.replace('_location', filter_destination);
    }
    console.log(parameters);
    reportUrl = reportUrl + parameters;
    console.log(reportUrl);

    var pLocation = $('#hdParamText').val();
    pLocation = pLocation.replace('_location', location);
    var report = $('#ddlReport').val();
    exportUrl = exportUrl + '/' + report  + pLocation  ;
    console.log(exportUrl);


    $("#bExcel").attr('href', exportUrl);
    $("#bViewReport").attr("disabled", "disabled");
    $("#bExcel").attr("disabled", "disabled");

    $.post(
        reportUrl,
        {
            'dateParam':dateParam,
            'dateRangeFrom': dateRangeFrom,
            'dateRangeTo': dateRangeTo,
            'filter_province':filter_province,
            'filter_municipality':filter_municipality,
            'filter_destination':filter_destination
        },
        function(data)
        {
            $('#reportContent').html(data);
            $('#lblLoadingReport').html('&nbsp;');
            $("#bExcel").removeAttr("disabled");
            $("#bViewReport").removeAttr("disabled");
        });
}

function dateToYMD(dateText) {
    var date = dateText.split('/');
    if (date.length == 3)
    {
        var dateResult = parseInt(date[2], 10) + '-' + zeroBased(parseInt(date[1], 10)) + '-' + zeroBased(parseInt(date[0], 10));
        return dateResult;
    }
    return null;
}

function zeroBased(intValue)
{
    return (intValue < 10) ? "0"+ intValue : intValue;
}