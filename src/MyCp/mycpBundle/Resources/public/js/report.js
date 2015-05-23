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
    var reportUrl = $('#hdReportUrl').val();
    var parameters = $('#hdParamText').val();

    var dateParam = ($('#dateParam').val() !== undefined) ? dateToYMD($('#dateParam').val()) : "";
    parameters = parameters.replace('_date', dateParam);

    var dateRangeFrom = ($('#dateRangeFrom').val() !== undefined) ?  dateToYMD($('#dateRangeFrom').val()) : "";
    parameters = parameters.replace('_dateFrom', dateParam);

    var dateRangeTo = ($('#dateRangeTo').val() !== undefined) ?   dateToYMD($('#dateRangeTo').val()) : "";
    parameters = parameters.replace('_dateTo', dateParam);

    reportUrl = reportUrl + parameters;

    $('#reportContent').html('Cargando...');
    $("#bViewReport").attr("disabled", "disabled");
    $("#bExcel").attr("disabled", "disabled");

    $.post(
        reportUrl,
        {
            'dateParam':dateParam,
            'dateRangeFrom': dateRangeFrom,
            'dateRangeTo': dateRangeTo
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