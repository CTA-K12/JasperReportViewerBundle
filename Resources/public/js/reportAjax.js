var processIndicatorCount = 0;
var processIndicatorMax = 5;
var processingInterval;

$(document).ready(function() {
    $('#report-form').children('form').on('submit', function(e) {
        //Prevent the normal submission
        e.preventDefault();

        //Set the form variable
        var form = $(this);

        //Serialize the form (NOTE: do this before disabling the form, else it returns an empty string)
        var formData = {};
        $.each(form.serializeArray(), function(index, field) {
            formData[field.name] = field.value;
        });

        //Turn off the form inputs
        disableForm(form);

        //Alert the user that the report is running
        processingInterval = setInterval(showProcessingMessage, 1000);

        //Setup the post request
        $.ajax({
            type: form.attr('method'),
            url : form.attr('action'),
            data: formData })
        .done(function(data) { handleSuccess(data, form); })
        .fail(function(data) { handleFailure(data, form); })
        .always(function() { enableForm(form); });
    });
});

function disableForm(form) {
    form.find(':input:not(:disabled)').prop('disabled',true);
}

function enableForm(form) {
    form.find(':input:disabled').prop('disabled',false);
}

function handleSuccess(data, form) {
    clearInterval(processingInterval);
    if (data['success']) {
        //Alert that the report is complete
        $('#report-msg').html('<div class="alert alert-success"><b>Report Ready</b>:  <a class="btn btn-mini" href="' + data['output'] + '">View Report</a><a href="#" class="close" data-dismiss="alert">&times;</a></div>');

        //If the history table is present, redraw it show the new entry
        if (undefined !== historyTable) {
            historyTable.fnDraw();
        }
    } else {

    }
}

function handleFailure(data, form) {
    clearInterval(processingInterval);
    $('#report-msg').html('<div class="alert alert-danger">An error occured trying to run the report<a href="#" class="close" data-dismiss="alert">&times;</a></div>');
}

function showProcessingMessage() {
    var indicatorString = '';
    for(var i = 0; i < processIndicatorCount; i++) {
        indicatorString = indicatorString + ' . ';
    }
    $('#report-msg').html('<div class="alert alert-info">Running Report<b>' + indicatorString +
        '</b><a href="#" class="close" onClick="stopProcessingMessage();" data-dismiss="alert">&times;</a></div>');
    processIndicatorCount = (processIndicatorCount + 1) % processIndicatorMax;
}

function stopProcessingMessage() {
    clearInterval(processingInterval);
    $('#report-msg').html('');
}