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
});