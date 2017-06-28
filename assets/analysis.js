yii.gii = (function ($) {
    var ajaxRequest = null;

    var fillModal = function ($link, data) {
        var $modal     = $('#preview-modal'),
            $modalBody = $modal.find('.modal-body');
        if (!$link.hasClass('modal-refresh')) {
            var filesSelector = 'a.' + $modal.data('action') + ':visible';
            var $files        = $(filesSelector);
            var index         = $files.filter('[href="' + $link.attr('href') + '"]').index(filesSelector);
            var $prev         = $files.eq(index - 1);
            var $next         = $files.eq((index + 1 == $files.length ? 0 : index + 1));
            $modal.data('current', $files.eq(index));
            $modal.find('.modal-previous').attr('href', $prev.attr('href')).data('title', $prev.data('title'));
            $modal.find('.modal-next').attr('href', $next.attr('href')).data('title', $next.data('title'));
        }
        $modalBody.html(data);
        valueToCopy = $("<div/>").html(data.replace(/(<(br[^>]*)>)/ig, '\n').replace(/&nbsp;/ig, ' ')).text().trim() + '\n';
        $modal.find('.content').css('max-height', ($(window).height() - 200) + 'px');
        $modal.find('.modal-dialog').css('width', ($(window).width() - 200) + 'px');
    };

    $('.ajax-view').on('click', function () {
        if (ajaxRequest !== null) {
            if ($.isFunction(ajaxRequest.abort)) {
                ajaxRequest.abort();
            }
        }
        var that   = this;
        var $modal = $('#preview-modal');
        var $link  = $(this);
        $modal.find('.modal-refresh').attr('href', $link.attr('href'));
        if ($link.hasClass('preview-code') || $link.hasClass('diff-code')) {
            $modal.data('action', ($link.hasClass('preview-code') ? 'preview-code' : 'diff-code'))
        }
        $modal.find('.modal-title').text($link.data('title'));
        $modal.find('.modal-body').html('Loading ...');
        $modal.modal('show');
        var checkbox = $('a.' + $modal.data('action') + '[href="' + $link.attr('href') + '"]').closest('tr').find('input').get(0);
        var checked  = false;
        if (checkbox) {
            checked = checkbox.checked;
            $modal.find('.modal-checkbox').removeClass('disabled');
        } else {
            $modal.find('.modal-checkbox').addClass('disabled');
        }
        $modal.find('.modal-checkbox span').toggleClass('glyphicon-check', checked).toggleClass('glyphicon-unchecked', !checked);

        ajaxRequest = $.ajax({
            type   : 'POST',
            cache  : false,
            url    : $link.prop('href'),
            data   : $('.default-view form').serializeArray(),
            success: function (data) {
                fillModal($(that), data);
            },
            error  : function (XMLHttpRequest, textStatus, errorThrown) {
              $modal.find('.modal-body').html('<div class="error">' + XMLHttpRequest.responseText + '</div>');
            }
        });
        return false;
    });

})(jQuery);