/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */
/**
 * Description of scripts
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
$(document).foundation();

$(document).ready(function() {

    /* Delete I/O */
    $(document).on('click', '.delete-io', function(elem) {
        elem.preventDefault();
        var button = $(this);
        if (confirm("Are you sure you want to delete '"+button.data('name')+"'? ")) {
            $.ajax({
                url: button.attr('href'),
                method: 'DELETE',
                complete: function() {
                    window.location.reload();
                }
            })
        }
    });

    /* Select OAuth type */
    $(document).on('change', '#oauth-selector', function(elem) {
        if ($(this).val() === '1') {
            $('.input-meta#oauth2').hide();
            $('.input-meta#oauth1').show();
        } else if ($(this).val() === '2') {
            $('.input-meta#oauth1').hide();
            $('.input-meta#oauth2').show();
        } else {
            $('.input-meta#oauth1').hide();
            $('.input-meta#oauth2').hide();
        }
    });

    /* Fetch endpoint data */
    $(document).on('click', '#fetch-data-button', function(elem) {
        elem.preventDefault();
        var button = $(this);
        button.attr('disabled', true);
        $('#fetch-error').removeClass('is-visible');
        $('#endpoint-url').removeClass('is-invalid-input');
        $('#endpoint-data').hide();
        $('#endpoint-data').html('');
        $.ajax({
            url: button.attr('href'),
            method: 'POST',
            data: {url: $('#endpoint-url').val()},
            dataType: 'json',
            success: function(data) {
                button.attr('disabled', false);
                if (data.status === 'error') {
                    $('#fetch-error').html(data.message);
                    $('#fetch-error').addClass('is-visible');
                    $('#endpoint-url').addClass('is-invalid-input');
                    $('#endpoint-data').hide();
                } else {
                    $('#fetch-error').removeClass('is-visible');
                    $('#endpoint-url').removeClass('is-invalid-input');
                    $('#endpoint-data').html(JSON.stringify(data.message, null, 2));
                    $('#endpoint-data').show();
                }
            }
        })
    });

    /* Add mapping */
    $(document).on('click', '#add-mapping', function(elem) {
        elem.preventDefault();
        var prototype = $(this).data('prototype');

        var number = $('.map-input-box').length + 1;
        var newForm = prototype.replace(/__number__/g, number);

        $(this).after(newForm);

    });
});