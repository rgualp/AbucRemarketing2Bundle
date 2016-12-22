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

    var filter_accommodation_modality = ($('#filter_accommodation_modality').val() !== undefined) ?   $('#filter_accommodation_modality').val() : "";
    if(filter_accommodation_modality!='') {
        location += "/" + filter_accommodation_modality;
        parameters = parameters.replace('_accommodationModality', filter_accommodation_modality);
    }

    reportUrl = reportUrl + parameters;

    var exportParameters = $('#hdParamText').val();
    exportParameters = exportParameters.replace('_location', location);
    exportParameters = exportParameters.replace('_dateFrom', dateRangeFrom);
    exportParameters = exportParameters.replace('_dateTo', dateRangeTo);
    exportParameters = exportParameters.replace('_date', dateParam);
    exportParameters = exportParameters.replace('_accommodationModality', filter_accommodation_modality);
    var report = $('#ddlReport').val();
    exportUrl = exportUrl + '/' + report  + exportParameters  ;


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
            'filter_destination':filter_destination,
            'accommodationModality':filter_accommodation_modality
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