/**
 *  DynamoForm-selectize.js - Enable the selectize.js library using html attributes.
 *
 *  Author:     David Cramblett (dcramble@mesd.k12.or.us)
 *  License:    MIT
 */

$(document).ready(function() {

    /**
     * Enable selectize on desired form elements
     *
     * Process all elements with class 'dynamo-selectize', parsing all of the
     * attribute options for each element and enabling the desired selectize
     * configuration for the specific element.
     *
     * If chaining (element dependency) is required, disable any elements where
     * appropriate.
     */
    var formElements = $('.dynamo-selectize');
    initDynamoSelectize(formElements);


    /**
     * Update selectize objects, if needed, when data changes.
     *
     * If chaining (element dependency) is in use, child elemenets may need to
     * be enabled or disabled based on the change.
     *
     */
    $(document).on('change', '.dynamo-selectize', function() {

        // Determine if chaining is in use on element.
        if ('undefined' !==  typeof $(this).attr('data-chain-child')) {

            // Parse child list
            var children = [];
            try {
                children = JSON.parse($(this).attr('data-chain-child'));
            } catch (e) {
                // The data-chain-parent attribute is not set properly.
                return false;
            }

            children.forEach(function(id) {
                processSelectizeChainedChild($('#' + id));
            });
        }
    });

});




/**
 * Initialize Dynamo-selectize elements
 *
 */
function initDynamoSelectize(formElements) {

    // Array to track element chaining
    var chainedChildren = [];

    // Enable Selectize on each element
    $.each(formElements, function() {

        // Parse the elements attribute options into the selectize format.
        var options = buildSelectizeOptionsObject($(this));

        // Enable selectize on the element with the desired option
        // configuration.
        $(this).selectize(options);

        // Determine if chaining is in use on element. If yes, track the element
        // in the chainedChildren array to be processed once all elements have
        // been initialized.
        if ('undefined' !==  typeof $(this).attr('data-chain-parent')) {
            chainedChildren.push($(this));
        }
    });

    /**
     * Proocess chained elments
     *
     * Child elments need to be diabled until all thier parents have values set.
     * Child elements often need to be rebuilt with new options for data
     * selection based on the values from the parent element.
     */
    $.each(chainedChildren, function() {
        processSelectizeChainedChild($(this));
    });


}



/**
 * Build Selectize option object
 *
 * A html data-[option] attribute exist for each Selectize option. The
 * data-[option] attributes are processed in way that options with invalid
 * setting data will be defaulted to the Selectize designated default value,
 * when possible.
 *
 */
function buildSelectizeOptionsObject(formElement) {

    var _options = {};

    // data-diacritics: true
    if ('undefined' !==  typeof formElement.attr('data-diacritics')) {
        //If defined any way other than string 'false', use selectize default
        if ('false' === formElement.attr('data-diacritics').toLowerCase()) {
            _options.diacritics = false;
        }
    }

    // data-create: false
    if ('undefined' !==  typeof formElement.attr('data-create')) {
        //If defined any way other than string 'true', use selectize default
        if ('true' === formElement.attr('data-create').toLowerCase()) {
            _options.create = true;
        }
    }

    // data-createOnBlur: false
    if ('undefined' !==  typeof formElement.attr('data-createOnBlur')) {
        //If defined any way other than string 'true', use selectize default
        if ('true' === formElement.attr('data-createOnBlur').toLowerCase()) {
            _options.createOnBlur = true;
        }
    }

    // data-createFilter: null
    if ('undefined' !==  typeof formElement.attr('data-createFilter')) {
        //If defined any way other than string 'null', use user string
        if ('null' !== formElement.attr('data-createFilter').toLowerCase()) {
            _options.createFilter = formElement.attr('data-createFilter');
        }
    }

    // data-highlight: true
    if ('undefined' !==  typeof formElement.attr('data-highlight')) {
        //If defined any way other than string 'false', use selectize default
        if ('false' === formElement.attr('data-highlight').toLowerCase()) {
            _options.highlight = false;
        }
    }

    // data-persist: true
    if ('undefined' !==  typeof formElement.attr('data-persist')) {
        //If defined any way other than string 'false', use selectize default
        if ('false' === formElement.attr('data-persist').toLowerCase()) {
            _options.persist = false;
        }
    }

    // data-openOnFocus: true
    if ('undefined' !==  typeof formElement.attr('data-openOnFocus')) {
        //If defined any way other than string 'false', use selectize default
        if ('false' === formElement.attr('data-openOnFocus').toLowerCase()) {
            _options.openOnFocus = false;
        }
    }

    // data-maxOptions: 1000
    if ('undefined' !==  typeof formElement.attr('data-maxOptions')) {
        if (!isNaN(parseInt(formElement.attr('data-maxOptions')))) {
            _options.maxOptions = parseInt(formElement.attr('data-maxOptions'));
        }
    }

    // data-maxItems: 1
    if ('undefined' !==  typeof formElement.attr('data-maxItems')) {
        if (!isNaN(parseInt(formElement.attr('data-maxItems')))) {
            _options.maxItems = parseInt(formElement.attr('data-maxItems'));
        }
    }

    // data-hideSelected: false
    if ('undefined' !==  typeof formElement.attr('data-hideSelected')) {
        //If defined any way other than string 'true', use selectize default
        if ('true' === formElement.attr('data-hideSelected').toLowerCase()) {
            _options.hideSelected = true;
        }
    }

    // data-allowEmptyOption: false
    if ('undefined' !==  typeof formElement.attr('data-allowEmptyOption')) {
        //If defined any way other than string 'true', use selectize default
        if ('true' === formElement.attr('data-allowEmptyOption').toLowerCase()) {
            _options.allowEmptyOption = true;
        }
    }

    // data-scrollDuration: 60
    if ('undefined' !==  typeof formElement.attr('data-scrollDuration')) {
        if (!isNaN(parseInt(formElement.attr('data-scrollDuration')))) {
            _options.scrollDuration = parseInt(formElement.attr('data-scrollDuration'));
        }
    }

    // data-loadThrottle: 300
    if ('undefined' !==  typeof formElement.attr('data-loadThrottle')) {
        if (!isNaN(parseInt(formElement.attr('data-loadThrottle')))) {
            _options.loadThrottle = parseInt(formElement.attr('data-loadThrottle'));
        }
    }

    // data-loadingClass: 'loading'
    if ('undefined' !==  typeof formElement.attr('data-loadingClass')) {
        //If defined any way other than string 'loading', use user string
        if ('loading' !== formElement.attr('data-loadingClass').toLowerCase()) {
            _options.loadingClass = formElement.attr('data-loadingClass');
        }
    }

    // data-preload: false | Can be boolean or string
    if ('undefined' !==  typeof formElement.attr('data-preload')) {
        //If defined string 'true', use boolean true
        if ('true' === formElement.attr('data-preload').toLowerCase()) {
            _options.preload = true;
        }
        //If defined string 'focus', use string focus
        else if ('focus' === formElement.attr('data-preload').toLowerCase()) {
            _options.preload = 'focus';
        }
        //If defined any other way, use selectize default
    }

    // data-dropdownParent: null
    if ('undefined' !==  typeof formElement.attr('data-dropdownParent')) {
        //If defined any way other than string 'body', use selectize default
        if ('body' === formElement.attr('data-dropdownParent').toLowerCase()) {
            _options.dropdownParent = 'body';
        }
    }

   // data-addPrecedence: false
    if ('undefined' !==  typeof formElement.attr('data-addPrecedence')) {
        //If defined any way other than string 'true', use selectize default
        if ('true' === formElement.attr('data-addPrecedence').toLowerCase()) {
            _options.addPrecedence = true;
        }
    }

    // data-selectOnTab: false
    if ('undefined' !==  typeof formElement.attr('data-selectOnTab')) {
        //If defined any way other than string 'true', use selectize default
        if ('true' === formElement.attr('data-selectOnTab').toLowerCase()) {
            _options.selectOnTab = true;
        }
    }

    /** data-options: []
     *
     *  The JSON.parse method expects the string data to be in proper form
     *  to be successfully parsed. It's important that all strings, including
     *  the object key names, be surrounded in double quotes. This means you
     *  will likely need to surround the html attribute value in single quotes.
     *
     *  Example:
     *
     *    <select
     *      class="dynamo-selectize"
     *      data-options='[
     *        {
     *          "value": 1,
     *          "text": "Portland"
     *        },
     *        {
     *          "value": 2,
     *         "text": "Astoria"
     *        },
     *        {
     *         "value": 3,
     *         "text": "Vancouver"
     *        }
     *      ]'
     *    >
     */
    if ('undefined' !==  typeof formElement.attr('data-options')) {
        try {
            _options.options = JSON.parse(formElement.attr('data-options'));
        } catch (e) {}
    }

    // data-dataAttr: 'data-data'
    if ('undefined' !==  typeof formElement.attr('data-dataAttr')) {
        _options.dataAttr = formElement.attr('data-dataAttr');
    }

    // data-valueField: 'value'
    if ('undefined' !==  typeof formElement.attr('data-valueField')) {
        _options.valueField = formElement.attr('data-valueField');
    }

    // data-optgroupValueField: 'value'
    if ('undefined' !==  typeof formElement.attr('data-optgroupValueField')) {
        _options.optgroupValueField = formElement.attr('data-optgroupValueField');
    }

    // data-labelField: 'text'
    if ('undefined' !==  typeof formElement.attr('data-labelField')) {
        _options.labelField = formElement.attr('data-labelField');
    }

    // data-optgroupLabelField: 'label'
    if ('undefined' !==  typeof formElement.attr('data-optgroupLabelField')) {
        _options.optgroupLabelField = formElement.attr('data-optgroupLabelField');
    }

    // data-optgroupField: 'optgroup'
    if ('undefined' !==  typeof formElement.attr('data-optgroupField')) {
        _options.optgroupField = formElement.attr('data-optgroupField');
    }

    // data-sortField: '$order'
    if ('undefined' !==  typeof formElement.attr('data-sortField')) {
        _options.sortField = formElement.attr('data-sortField');
    }

    /**
     *  data-searchField: ['text']
     *
     *  The JSON.parse method expects the string data to be in proper form
     *  to be successfully parsed. It's important that all strings, including
     *  the array key names, be surrounded in double quotes. This means you
     *  will likely need to surround the html attribute value in single quotes.
     *
     *  Example:
     *
     *    <select
     *      class="dynamo-selectize"
     *      data-searchField='[
     *        "text",
     *        "description"
     *      ]'
     *    >
     */
    if ('undefined' !==  typeof formElement.attr('data-searchField')) {
        try {
            _options.searchField = JSON.parse(formElement.attr('data-searchField'));
        } catch (e) {}
    }

    // data-searchConjunction: 'and'
    if ('undefined' !==  typeof formElement.attr('data-preload')) {
        //If defined string 'and', use sting 'and'
        if ('and' === formElement.attr('data-preload').toLowerCase()) {
            _options.preload = 'and';
        }
        //If defined string 'or', use string 'or'
        else if ('or' === formElement.attr('data-preload').toLowerCase()) {
            _options.preload = 'or';
        }
        //If defined any other way, use selectize default
    }

    /**
     *  data-optgroupOrder: null
     *
     *  The JSON.parse method expects the string data to be in proper form
     *  to be successfully parsed. It's important that all strings, including
     *  the array key names, be surrounded in double quotes. This means you
     *  will likely need to surround the html attribute value in single quotes.
     *
     *  Example:
     *
     *    <select
     *      class="dynamo-selectize"
     *      data-optgroupOrder='[
     *        "Seahawks",
     *        "Blazers",
     *        "Timbers"
     *      ]'
     *    >
     */
    if ('undefined' !==  typeof formElement.attr('data-optgroupOrder')) {
        try {
            _options.optgroupOrder = JSON.parse(formElement.attr('data-optgroupOrder'));
        } catch (e) {}
    }

     // data-copyClassesToDropdown: true
    if ('undefined' !==  typeof formElement.attr('data-copyClassesToDropdown')) {
        //If defined any way other than string 'false', use selectize default
        if ('false' === formElement.attr('data-copyClassesToDropdown').toLowerCase()) {
            _options.copyClassesToDropdown = false;
        }
    }

    /**
     *  data-render-option: null
     *
     *  The data-render-option attribute defines the html layout for custom
     *  rendering of selectize options in the drop down list.
     *
     */
    if ('undefined' !==  typeof formElement.attr('data-render-option')) {

        // Load render html from data attribute
        var rendorHtml = formElement.attr('data-render-option');
        //console.log(rendorHtml);

        // Create list of render variables to processed
        renderVars = rendorHtml.match(/\{(\w+)\}/g);
        //console.log(renderVars);

        // Create new render object
        _options.render = {};

        // Build Render Function for Item Option
        _options.render.option =
            function(item, escape) {

                // Don't overwrite the original html on first render pass
                var localRenderHtml = rendorHtml;

                // Replace render variables with option data
                renderVars.forEach(function(value) {
                    value = value.replace(/\{(\w+)\}/, '$1');
                    localRenderHtml = localRenderHtml.replace(/\{(\w+)\}/, item[value]);
                });

                return localRenderHtml;
            };
    }

    /**
     *  data-load-url: null
     *
     *  The data-load-[*] attributes define the necessary selectize options for
     *  remote data fetching. You must define the data-load-url attribute to
     *  trigger the functionality.
     */
    if ('undefined' !==  typeof formElement.attr('data-load-url')) {
        // Determine if preload is in use
        var requestPreload = false;
        if('undefined' !==  typeof _options.preload) {
            requestPreload = true;
        }
        // Build load options
        _options.load = processSelectizeLoadOptions(formElement, requestPreload);
    }

    return _options;

} //END buildSelectizeOptionsObject(formElement)



/**
 * Process load options
 *
 * Build a load function for remote data fetching based on attribute values.
 */
function processSelectizeLoadOptions(formElement, requestPreload) {

    var loadUrl = formElement.attr('data-load-url');

    // data-load-type: 'GET'
    var loadType = 'GET';
    if ('undefined' !==  typeof formElement.attr('data-load-type')) {
        loadType = formElement.attr('data-load-type');
    }

    // data-load-resultSet-limit: 10
    var loadLimit = 10;
    if ('undefined' !==  typeof formElement.attr('data-load-resultSet-limit')) {
        loadLimit = formElement.attr('data-load-resultSet-limit');
    }

    // data-load-resultSet-key: null
    var loadKey = null;
    if ('undefined' !==  typeof formElement.attr('data-load-resultSet-key')) {
        loadKey = formElement.attr('data-load-resultSet-key');
    }

    // data-load-url-vars: null
    var loadUrlVars = [];
    if ('undefined' !==  typeof formElement.attr('data-load-url-vars')) {
        try {
            loadUrlVars = JSON.parse(formElement.attr('data-load-url-vars'));
        } catch (e) {}

        //Update URL with var data
        $.each(loadUrlVars, function(k, v) {
            var re = new RegExp('\\{' + k + '\\}', 'g');
            loadUrl = loadUrl.replace(re, v);
        });
    }

    // Build load option
    var _load =
        function(query, callback) {
            // Don't search if query string is empty, unless pre-loading
            if (!requestPreload && !query.length) return callback();
            $.ajax({
                url: loadUrl + encodeURIComponent(query),
                type: loadType,
                error: function() {
                    callback();
                },
                success: function(res) {
                    if (loadKey) {
                        callback(res[loadKey].slice(0, loadLimit));
                    } else {
                        callback(res.slice(0, loadLimit));
                    }
                }
            });
        };

    return _load;
}



/**
 * Process Chained Child
 *
 * Disable and enable child form elments that are part of a chained dependency
 * between mutiple form elments. Additionaly, update the load url when needed.
 *
 */
function processSelectizeChainedChild(childElement) {

    // Parse parent list
    var parent = [];
    try {
        parent = JSON.parse(childElement.attr('data-chain-parent'));
    } catch (e) {
        // The data-chain-parent attribute is not set properly.
        return false;
    }

    // Iterate through all parents to determine if a value has been set. If
    // parent elements has a value, store it.
    var parentDependencyMet = true;
    var parentValues = {};
    parent.forEach(function(id) {
        if ($('#' + id).val()) {
            // Store parent / value combination
            parentValues[id] = $('#' + id).val();
        }
        else {
            // One of the parents has no value yet. Exit, we'll attempt to
            // enable the child again upon next parent update.
            childElement[0].selectize.setValue("");
            childElement[0].selectize.disable();
            parentDependencyMet = false;
        }
    });

    // Exit now if any dependcy is not met
    if ( false === parentDependencyMet) {
        return false;
    }

    // Ensure child is enabled
    childElement[0].selectize.enable();

    // Re-Build selectize control with new options if needed
    if ('undefined' !==  typeof childElement.attr('data-load-url')) {

        // Destroy exisiting selectize control
        childElement[0].selectize.destroy();

        // Set data-load-url-vars attribute with values from parent elements
        childElement.attr('data-load-url-vars', JSON.stringify(parentValues));

        // Build new selectize options
        var _options = buildSelectizeOptionsObject(childElement);

        // Re-create selectize control with new options
        childElement.selectize(_options);

    }

}