/* //////////////////////////////////////////////////////////////////////////////// */
/* /////////////////////////////////////////////////////////////////// DOCUMENT /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ---------------------------------------------------------------------- READY --- */
$(document).on('ready', init);

/* ----------------------------------------------------------------------- INIT --- */
function init() {
    facebook_init();
    form_init();
    chosen_init();
    object_init();
    modal_init();

    user_init();
    league_init();
    game_init();
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* ///////////////////////////////////////////////////////////////////// HELPER /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------- IS DEFINED --- */
function is_defined(value) {
    return typeof value !== 'undefined';
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* /////////////////////////////////////////////////////////////////// FACEBOOK /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- INIT --- */
function facebook_init() {
    facebook_hash();
}

/* ----------------------------------------------------------------------- HASH --- */
function facebook_hash() {
    if (window.location.hash == '#_=_') {
        window.location.hash = '';
        history.pushState('', document.title, window.location.pathname);
        event.preventDefault();
    }
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* /////////////////////////////////////////////////////////////////////// FORM /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- INIT --- */
function form_init() {
    form_validate();
    form_prevent();
    form_message();
}

/* ------------------------------------------------------------------- VALIDATE --- */
function form_validate() {
    $.validator.setDefaults({ ignore: ':hidden:not(.chosen-container)' });
    
    $('form:not(.ignore)').each(function() {
        $(this).validate({
            errorPlacement: function(error, element) {
                if ($.inArray(element.attr('type'), ['checkbox', 'radio']) !== -1) {
                    error.appendTo(element.closest('.item'));
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                form_submit(form);
            }
        });
    });
}

/* -------------------------------------------------------------------- PREVENT --- */
function form_prevent() {
    $(document).on('submit', 'form:not(.ignore)', event.preventDefault());
}

/* --------------------------------------------------------------------- SUBMIT --- */
function form_submit(form) {
    var $form;
    var action,
        data;

    $form = $(form);
    action = $form.attr('action');
    data = new FormData();

    if (!is_defined(action)) return false;

    $form.find('input[type="file"]').each(function(_, input) {
        var $input;

        $input = $(input)[0];

        for(var i = 0; i <= $input.files.length - 1; i++){
            data.append($(this).prop('name') + 
                ($input.files.length > 1 ? '_' + i : ''), $input.files[i]);
        }
    });

    $.each($form.serializeArray(), function(_, input){
        data.append(input.name, input.value);
    });

    $.ajax({
        url: action,
        type: 'post',
        cache: false,
        data: data,
        contentType: false,
        processData: false,
        success: function(res) {
            form_response(res, form);
        }
    });
}

/* ------------------------------------------------------------------- RESPONSE --- */
function form_response(res, form) {
    var $form;

    console.log(res);

    $form = $(form);
    res = $.parseJSON(res);

    console.log(res);

    if (typeof res.redirect !== 'undefined') {
        window.location.href = res.redirect;
    } else {
        if (typeof res.function !== 'undefined') {
            window[res.function]();
        }
        if (typeof res.text !== 'undefined') {
            var code;

            code = '' + 
                '<div class="message ' + res.type + '">' + 
                '<div class="text">' + res.text + '<a></a>' + '</div>' + 
                '</div>';

            $('body .message').remove();
            $('body').append(code);
        }
    }
}

/* -------------------------------------------------------------------- MESSAGE --- */
function form_message() {
    $(document).on('click', '.message a', function() {
        var $message;

        $message = $(this).closest('.message');

        $message.addClass('remove');

        setTimeout(function() {
            $message.remove();
        }, 350);
    });
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* ///////////////////////////////////////////////////////////////////// CHOSEN /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- INIT --- */
function chosen_init() {
    $('.item.chosen').each(function(_, item) {
        var $item;

        $item = $(item);
        $select = $item.find('select');

        $select.chosen();
        $select[0].setAttribute('style','display:visible; position:absolute; clip:rect(0,0,0,0)');
    });
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* ///////////////////////////////////////////////////////////////////// OBJECT /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- INIT --- */
function object_init() {
    object_delete();
}

/* -------------------------------------------------------------------------  --- */
function object_delete() {
    $('[data-action="delete"]').on('click', function(event) {
        event.preventDefault();

        var href,
            object;

        href = $(this).attr('href');
        object = $('body').attr('class').split(' ')[0];

        dialog_show({
            'title' : 'Delete ' + object,
            'message' : 'Are you sure you want to delete this ' + object + '?',
            'button' : [
                {
                    'text': 'Yes',
                    'style': 'negative',
                    'action': href
                }
            ]
        });
    });
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* ///////////////////////////////////////////////////////////////////// DIALOG /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- SHOW --- */
function dialog_show(data) {
    var dialog;

    if ($('.dialog_overlay').length) dialog_remove();

    dialog = '';
    dialog += '<div class="dialog_overlay display">';
    dialog += '<div class="dialog_box">';

    dialog += '<div class="dialog_header">';
    dialog += '<div class="dialog_title">' + data.title + '</div>';
    dialog += '</div>';

    dialog += '<div class="dialog_content">';
    dialog += '<p>' + data.message + '</p>';
    dialog += '</div>';

    dialog += '<div class="dialog_footer">';

    dialog += '<a class="button secondary" onclick="dialog_remove();">No</a>';

    $.each(data.button, function(_, button) {
        dialog += '<a class="button ' + button.style + '" ';
        if (typeof button.action !== 'undefined') {
            dialog += 'href="' + button.action + '"';
        } else if (typeof button.onclick !== 'undefined') {
            dialog += 'onclick="' + button.onclick + '"';
        }

        dialog += '>' + button.text + '</a>';
    });

    dialog += '</div>';

    dialog += '</div>';
    dialog += '</div>';

    $('body').prepend(dialog);
}

/* --------------------------------------------------------------------- REMOVE --- */
function dialog_remove() {
    $('.dialog_overlay').remove();
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* ////////////////////////////////////////////////////////////////////// MODAL /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- INIT --- */
function modal_init() {
    modal_show();
    modal_pop();
    modal_hash();
}

/* ----------------------------------------------------------------------- SHOW --- */
function modal_show() {
    $(document).on('click', '[data-modal]', function() {
        var modal_id = $(this).data('modal');

        history.replaceState({}, {}, window.location.href + '#' + modal_id);

        modal_hash();

        form_validate();
    });
}

/* ------------------------------------------------------------------------ POP --- */
function modal_pop() {
    $(window).on('popstate', function() {
        modal_hash();
    });
}

/* ----------------------------------------------------------------------- HASH --- */
function modal_hash() {
    var $div;
    var modal_id;

    history.replaceState({}, {}, window.location.href);

    modal_id = window.location.hash.replace('#', '');

    if (modal_id !== '') {
        $('body').addClass('overlay');

        $('.modal_overlay').removeClass('display');
        $('#' + modal_id).addClass('display');
    } else {
        $('.modal_overlay').removeClass('display');
        $('body').removeClass('overlay');
    }
}

/* ---------------------------------------------------------------------- CLOSE --- */
function modal_close(reload) {
    var url;

    if (typeof reload === 'undefined') {
        reload = true;
    }

    $('.modal_overlay').removeClass('display');
    $('body').removeClass('overlay');

    url = window.location.href.split('#')[0];

    if (reload === true) {
        window.location.href = url;
    } else {
        history.replaceState({}, {}, url);
    }
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* /////////////////////////////////////////////////////////////////////// USER /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- INIT --- */
function user_init() {
    user_toggle();
}

/* --------------------------------------------------------------------- TOGGLE --- */
function user_toggle() {
    $('header a.user .name').on('click', function() {
        $(this).closest('.user').toggleClass('active');
    });

    $(document).on('click', function(event) {
        var $target;

        $target = $(event.target);

        if (!$target.hasClass('name') && !$target.parent().hasClass('user')) {
            $('header a.user').removeClass('active');
        }
    });
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* ///////////////////////////////////////////////////////////////////// LEAGUE /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- INIT --- */
function league_init() {
    league_stats_toggle();
    league_code_refresh();
    league_image_upload();
    league_user_remove();
}

/* --------------------------------------------------------------- STATS TOGGLE --- */
function league_stats_toggle() {
    $('.league table.league_table td.stats a.toggle').on('click', function() {
        var $toggle,
            $table,
            $row;

        $toggle = $(this);
        $row = $toggle.closest('tr');
        $table = $toggle.closest('table');

        if ($row.hasClass('active')) {
            $row.removeClass('active');
        } else {
            $table.find('tr').removeClass('active');
            $row.addClass('active');

            if ($row.next('.stats').find('canvas').length <= 0) {
                league_graph_render($row.next('.stats'));
            }

            setTimeout(function() {
                $('html, body').animate({
                    scrollTop: $row.offset().top - 50 + 'px'
                }, 300);
            }, 300);
        }
    });
}

/* --------------------------------------------------------------- GRAPH RENDER --- */
function league_graph_render(row) {
    var $row, 
        $graph,
        $canvas;
    var data,
        months,
        context,
        graph;

    $row = $(row);
    $graph = $row.find('.graph');
    $canvas = $('<canvas/>').width($graph.width()).height(($graph.height() - 35));

    $graph.append($canvas);

    data = JSON.parse($graph.attr('data-graph'));
    label = JSON.parse($graph.attr('data-graph-label'));
    context = $canvas.get(0).getContext('2d');

    graph = new Chart(context).LineWithLine({
        labels: label,
        datasets: [
            {
                fillColor: 'rgba(131,180,117,.1)',
                strokeColor: '#23cf5f',
                pointColor: '#23cf5f',
                pointStrokeColor: '#fff',
                pointHighlightFill: '#23cf5f',
                data: data
            }
        ]
    },
    {
        scaleOverride: true,
        scaleSteps: 6,
        scaleStepWidth: 50,
        scaleStartValue: 850,
        scaleFontFamily: 'apercu',
        scaleFontSize: 12,
        scaleFontColor: '#717A86',
        scaleLineColor: '#D7DADD',
        tooltipFontFamily: 'apercu',
        tooltipFontSize: 12,
        tooltipFillColor: 'rgba(48,61,77,.9)',
        tooltipCornerRadius: 2,
        tooltipTemplate: '<%if (value){%><%= value %><%} else {%> No data <%}%>',
        tooltipCaretSize: 4,
        datasetFill: true,
        pointDotRadius: 4,
        pointDotStrokeWidth: 1,
        datasetStrokeWidth: 1
    });
}

/* --------------------------------------------------------------- IMAGE UPLOAD --- */
function league_image_upload() {
    var $modal,
        $input,
        $area;

    $modal = $('.league #league_setting');
    $input = $modal.find('#poster');
    $area = $modal.find('.file_area');

    $input.file({
        area: $area,
        limit_max: 1,
        size_max: ((2 * 1024) * 1000),
        accept: ['image/jpeg', 'image/png'],
        directory: {
            script: BASE_URL + '/src/plugin/file',
            upload: BASE_PATH + DIR_TMP + '/image'
        },
        added: function(index, file) {
            $area.addClass('active');
            $area.append('<div class="progress"/>');
        },
        progress: function(index, file) {
            $area.find('.progress').css('width', file.progress * 100 + '%');
        },
        complete: function(index, file) {
            $area.find('.progress').css('width', '100%');

            setTimeout(function() {
                var $image;
                var image_path;

                $image = $(new Image());
                image_path = BASE_URL + DIR_TMP + '/image/' + file.name.temporary;

                $input.data('file').count(0);

                $image.on('load', function() {
                    $area.removeClass('active');
                    $area.find('.progress').remove();

                    $area.css('background-image', 'url(' + image_path + ')');
                    $area.prev('#poster_tmp').val(image_path);
                });

                $image.attr('src', image_path);

            }, 300);
        },
        error: function(index, file, err) {
            var res;

            res = {};

            res.type = 'negative';
            if (err.name == 'ERR_NOT_ACCEPTED') {
                res.text = 'Only JPG & PNG images are valid';
            } else if (err.name == 'ERR_MAX_SIZE') {
                res.text = 'File size must be less than 2MB';
            }

            form_response(JSON.stringify(res));
        }
    });
}

/* --------------------------------------------------------------- CODE REFRESH --- */
function league_code_refresh() {
    $('#league_setting [data-action="code_refresh"]').on('click', function() {
        var $input;

        $input = $(this).closest('.item').find('#code');

        $.ajax({
            url: BASE_URL + '/src/include/data.php?f=league_code_ajax',
            type: 'get',
            cache: false,
            success: function(code) {
                $input.val(code);
            }
        });
    });
}

/* -------------------------------------------------------------- PLAYER REMOVE --- */
function league_user_remove(user_id) {
    user_id = user_id || null;

    if (user_id === null) {
        $('.league #league_setting .toggle').on('click', function(event) {
            user_id = $(this).find('[type="checkbox"]').val();

            dialog_show({
                'title' : 'Remove player',
                'message' : 'Are you sure you want to remove this player?',
                'button' : [
                    {
                        'text': 'Yes',
                        'style': 'negative',
                        'onclick': 'league_user_remove(' + user_id + ')'
                    }
                ]
            });
        });
    } else {
        var $checkbox;

        $checkbox = $('.league #league_setting #user_' + user_id);

        dialog_remove();
        
        $checkbox.prop('checked', true);
        $checkbox.closest('.item').addClass('delete');
    }
}


/* //////////////////////////////////////////////////////////////////////////////// */
/* /////////////////////////////////////////////////////////////////////// GAME /// */
/* //////////////////////////////////////////////////////////////////////////////// */

/* ----------------------------------------------------------------------- INIT --- */
function game_init() {
    game_player_disable();
}

/* ------------------------------------------------------------- PLAYER DISABLE --- */
function game_player_disable() {
    $('#result_insert select[id^="player_"]').on('change', function() {
        var $select;
        var user_id;

        $select = $(this);
        user_id = $select.val();

        if ($select.attr('id') == 'player_1') {
            $select = $('#result_insert #player_2');
        } else {
            $select = $('#result_insert #player_1');
        }

        $select.find('option').prop('disabled', false);
        
        $select.find('option[value="' + user_id + '"]').prop('disabled', true);
        $select.trigger("chosen:updated");
    });
}
