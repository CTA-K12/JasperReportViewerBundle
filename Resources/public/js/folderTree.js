$(document).ready(function() {
    //Turn all div with the report demo folder tree class into jstrees
    $('#jstree').jstree({
        'core': {
            'data': {
                'url': function(node) {
                    return $('#jstree').data('url');
                },
                'data': function(node) {
                    return { 'id': node.id };
                },
                'dataType': 'json',
                'type': 'GET'
            }
        }
    })
    .on('changed.jstree', function(e, data) {
        if (undefined !== data.node.a_attr.href && '#' !== data.node.a_attr.href) {
            window.location.replace(data.node.a_attr.href);
        } else {
            if (data.node.state.opened) {
                $('#jstree').jstree('close_node', data.node);
                
            } else {
                $('#jstree').jstree('open_node', data.node);
                
            }
        }
    })
    .on('after_open.jstree', function(e, data) {
        //Change the folder icon to open
        $('#' + jQueryStrEscape(data.node.id) + '-icon').addClass('icon-folder-open').removeClass('icon-folder-close');
    })
    .on('after_close.jstree', function(e, data) {
        //Change the folder icon to close
        $('#' + jQueryStrEscape(data.node.id) + '-icon').addClass('icon-folder-close').removeClass('icon-folder-open');
    });
});

//Function to escape special javascript characters
function jQueryStrEscape(str)
{
    return str.replace(/([;&,\.\+\*\~':"\!\/\^#$%@\[\]\(\)=>\|])/g, '\\$1');
}