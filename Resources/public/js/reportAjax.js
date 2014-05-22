var processIndicatorCount = 0;
var processIndicatorMax = 5;
var processingInterval;

var requestId;

$(document).ready(function() {
    $('form').on('submit', function(e) {
        console.log('Supersaurus Rex');
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

        //Turn off the report toolbar
        disableToolbar();

        //Alert the user that the report is running
        startProcessingMessage();

        //Setup the post request
        $.ajax({
            type: form.attr('method'),
            url : form.attr('action'),
            data: formData })
        .done(function(data) { handleSuccess(data); })
        .fail(function(data) { handleFailure(data); })
        .always(function() { enableForm(form); });
    });

    //Handle the report page buttons specially
    $('.page-button').on('click', function(e) {
        e.preventDefault();

        //Load the page
        if ('#' != $(this).attr('href')) {
            $.ajax({
                type: 'GET',
                url: $(this).attr('href'),
                })
            .done(function(data) { handleSuccess(data); })
            .fail(function(data) { handleFailure(data); });
        }
    });
});

function disableForm(form) {
    form.find(':input:not(:disabled)').prop('disabled',true);
}

function enableForm(form) {
    form.find(':input:disabled').prop('disabled',false);
}

function handleSuccess(data) {
    stopProcessingMessage();
    if (data['success']) {
        //Set the request id
        requestId = data['requestId'];

        //Go ahead and set the first page
        displayPage(data['output']);

        //Setup the toolbat
        setupToolbar(data['toolbar']);
    } else {
        displayErrorMessage();
    }
}

function handleFailure(data) {
    stopProcessingMessage();
    displayErrorMessage();
}

function displayErrorMessage() {
    $('#report-output').html('<div class="report-placeholder"><h3 class="text-error">An error occured when running the report</h3></div>');
}

function showProcessingMessage() {
    var indicatorString = '';
    for(var i = 0; i < processIndicatorCount + 1; i++) {
        indicatorString = indicatorString + ' . ';
    }
    $('#report-output').html('<div class="report-placeholder"><h2>Running Report<br><b>' + indicatorString +
        '</b></h2></div>');
    processIndicatorCount = (processIndicatorCount + 1) % processIndicatorMax;
}

function startProcessingMessage() {
    showProcessingMessage();
    processingInterval = setInterval(showProcessingMessage, 1000);
}

function stopProcessingMessage() {
    clearInterval(processingInterval);
    $('#report-output').html('');
}

function displayPage(output) {
    $('#report-output').html(output);
}

function setupToolbar(toolbar) {
    //Set the hrefs
    $('#export-pdf').attr('href', toolbar['pdf']);
    $('#export-xls').attr('href', toolbar['xls']);
    $('#first-page').attr('href', toolbar['first']);
    $('#back-ten').attr('href', toolbar['back10']);
    $('#back-one').attr('href', toolbar['back']);
    $('#next-one').attr('href', toolbar['forward']);
    $('#next-ten').attr('href', toolbar['forward10']);
    $('#last-page').attr('href', toolbar['last']);
    $('#print-report').attr('href', toolbar['print']);

    //Determine whether the button should be displayed as active or not
    var selectors = ['#export-pdf', '#export-xls', '#first-page', '#back-ten', '#back-one',
                     '#next-one', '#next-ten', '#last-page', '#print-report'];

    for(var i = 0; i < selectors.length; i++) {
        if ('#' == $(selectors[i]).attr('href')) {
            $(selectors[i]).addClass('disabled');
        } else {
            $(selectors[i]).removeClass('disabled');
        }
    }
}

function disableToolbar() {
    var selectors = ['#export-pdf', '#export-xls', '#first-page', '#back-ten', '#back-one',
                     '#next-one', '#next-ten', '#last-page', '#print-report'];
    for(var i = 0; i < selectors.length; i++) {
        $(selectors[i]).attr('href', '#');
        $(selectors[i]).addClass('disabled');
    }
}