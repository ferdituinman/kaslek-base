(function($) {
    // Bij laden: check JS cookie en verberg knoppen indien al gestemd
    $(document).ready(function() {
        $('.ft-poll-btn').each(function() {
            var postId = $(this).data('post-id');
            if (document.cookie.indexOf('ft_poll_' + postId + '=') !== -1) {
                $('#ft-poll-buttons-' + postId).hide();
                $('#ft-poll-results-' + postId).show();
            }
        });
    });

    $(document).on('click', '.ft-poll-btn', function() {
        var btn    = $(this);
        var postId = btn.data('post-id');
        var vote   = btn.data('vote');
        var nonce  = btn.data('nonce');

        var buttonsWrap     = $('#ft-poll-buttons-' + postId);
        var resultContainer = $('#ft-poll-results-' + postId);

        if (buttonsWrap.hasClass('submitting')) return;
        buttonsWrap.addClass('submitting').css('opacity', '0.5');

        $.ajax({
            url: ftPollData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ft_poll_vote',
                post_id: postId,
                vote: vote,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Zet cookie client-side als fallback
                    var expires = new Date();
                    expires.setDate(expires.getDate() + 30);
                    document.cookie = 'ft_poll_' + postId + '=voted; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';
                    buttonsWrap.hide();
                    resultContainer.html(response.html || '').show();
                } else {
                    buttonsWrap.css('opacity', '1').removeClass('submitting');
                }
            },
            error: function() {
                buttonsWrap.css('opacity', '1').removeClass('submitting');
            }
        });
    });
})(jQuery);

