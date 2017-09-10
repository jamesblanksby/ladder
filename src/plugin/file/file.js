;(function($) {

    $.file = function(element, options) {

        var BASE_PATHNAME,
            BASE_URL;

        var $document,
            $input,
            $area;

        var plugin,
            defaults;

        var file_array,
            file_count;

        BASE_PATHNAME = window.location.pathname;
        BASE_URL = BASE_PATHNAME.substring(0, BASE_PATHNAME.lastIndexOf('/'));

        $input = $(element);

        plugin = this;
        plugin.setting = {};

        defaults = {
            area: null,
            accept: [],
            limit_max: null,
            size_max: null,
            chunk_size: 102400,
            directory: {
                script: BASE_URL + '',
                upload: BASE_URL + '/lib'
            },
            init: function() {},
            added: function() {},
            start: function() {},
            progress: function() {},
            complete: function() {},
            cancel: function() {}
        };

        file_array = [];
        file_count = 0;


        var post_max_size = function(callback) {
            var url;

            url = plugin.setting.directory.script 
                + '/file.php?action=post_max_size';

            $.ajax({
                url: url,
                cache: false,
                success: callback
            });
        };

        var slice_method = function(data) {
            if ('mozSlice' in data) {
                return 'mozSlice';
            } else if ('webkitSlice' in data) {
                return 'webkitSlice';
            } else {
                return 'slice';
            }
        };


        plugin.init = function() {
            // merge settings
            plugin.setting = $.extend({}, defaults, options);

            // set form enctype
            $input
                .closest('form')
                .attr('enctype', 'multipart/form-data');

            // hide input from view
            $input.hide();

            // cache upload area
            $area = $(plugin.setting.area);

            // create file upload if required
            if ($area.length <= 0) {
                var area_id;

                if (typeof $input.attr('id') !== 'undefined') {
                    area_id = $input.attr('id') + '_area';
                } else if (typeof $input.attr('name') !== 'undefined') {
                    area_id = $input.attr('name') + '_area';
                } else {
                    area_id = 'file_' + Math.random().toString(36).substr(2, 5);
                }

                $area = $('<div id="' + area_id + '" class="file_area"/>');
                $input.after($area);
            }

            // check file limit
            if (plugin.setting.limit_max === null || plugin.setting.limit_max > 1) {
                // set input to allow multiple files
                $input.prop('multiple', true);
            }

            // check for file drop support
            if (window.FileReader) {
                // turn off drag/drop on document
                $(document).on('drop dragover', function(event) {
                    event.preventDefault();
                });
                // create file drop listener
                $area.on('drop', plugin.file_data);
            }

            // create proxy input click
            $area.on('click', function() {
                $input.trigger('click');
            });

            // listen for file upload
            $input.on('change', plugin.file_data);

            // check if init function exists
            if ($.isFunction(plugin.setting.init)) {
                // trigger init callback
                plugin.setting.init.call(this);
            }

            // check maximum php post size
            post_max_size(function(data) {
                var bytes;

                // convert php post size to bytes
                bytes = parseInt(data) * 1000 * 1000;

                // check if max chunk size is greater than post max size
                if (plugin.setting.chunk_size > bytes) {
                    // lower max chunk size to post max size
                    plugin.setting.chunk_size = bytes;
                }
            });
        };

        plugin.file_data = function(event) {
            var data;

            // get file data
            if (event.originalEvent.dataTransfer) {
                data = event.originalEvent.dataTransfer.files;
            } else {
                data = $input[0].files;
            }

            // check if data actually exists
            if (data.length > 0) {
                // go through each data item
                $.each(data, function(index, tmp) {
                    var file;

                    // check max file uploads allowed
                    if (plugin.setting.limit_max !== null) {
                        if (file_count >= plugin.setting.limit_max) {
                            // call error function
                            plugin.error(index, 1);

                            return;
                        }
                    }

                    // check max file size
                    if (plugin.setting.size_max !== null) {
                        if (tmp.size > plugin.setting.size_max) {
                            // call error function
                            
                            plugin.error(index, 5);

                            return;
                        }
                    }

                    // set up file object
                    file = {
                        status: 'pending',
                        data: tmp,
                        name: {
                            original: tmp.name
                        },
                        extension: tmp.name.substr((tmp.name.lastIndexOf('.') + 1)),
                        mime: tmp.type,
                        size: {
                            total: tmp.size,
                            uploaded: 0
                        },
                        chunk: {
                            count: Math.ceil(tmp.size / plugin.setting.chunk_size),
                            start: 0,
                            end: plugin.setting.chunk_size
                        },
                        time: {
                            start: 0,
                            current: 0,
                            elapsed: 0
                        },
                        progress: 0,
                        request: new XMLHttpRequest(),
                        xhr: null,
                        slice_method: slice_method(tmp),
                    };

                    // set temporary name
                    file.name.temporary = Math.random().toString(36).substring(7) + '.' + file.extension;

                    // check if in accepted file type list
                    if (plugin.setting.accept.length > 0) {
                        if ($.inArray(file.mime, plugin.setting.accept) === -1 
                            && $.inArray(file.extension, plugin.setting.accept) === -1) {
                            // call error function
                            plugin.error(index, 4);

                            return;
                        }
                    }

                    // push file object to file array
                    file_array.push(file);

                    // increment active file count
                    file_count++;

                    // check if added function exists
                    if ($.isFunction(plugin.setting.added)) {
                        // trigger added callback
                        plugin.setting.added.call(this, file_array.length - 1, file);
                    }
                });

                // trigger file queue
                plugin.file_queue();
            }
        };

        plugin.file_queue = function() {
            // go through each file in queue
            $.each(file_array, function(index, file) {

                // check file status
                if (file.status === 'pending') {
                    // create request load handler
                    file.request.onload = function() {
                        plugin.chunk_complete(index);
                    };

                    // check if start function exists
                    if ($.isFunction(plugin.setting.start)) {
                        // trigger start callback
                        plugin.setting.start.call(this, index, file);
                    }

                    file.status = 'uploading';
                    file.time.start = new Date();

                    plugin.chunk_upload(index);
                }
            });
        };

        plugin.chunk_upload = function(index) {
            var file,
                chunk,
                path,
                params;

            file = file_array[index];

            
            // make sure chunk end isnt greater than file total size
            if (file.chunk.end > file.size.total) {
                file.chunk.end = file.size.total;
            }

            // slice chunk
            chunk = file.data[file.slice_method](file.chunk.start, file.chunk.end);

            // build upload uri
            path = plugin.setting.directory.script + '/file.php'
            params = $.param({
                action: 'upload',
                name: file.name.temporary,
                uri: plugin.setting.directory.upload
            });

            // prepare request
            file.request.open('put', path + '?' + params);
            file.request.overrideMimeType('application/octet-stream');

            if (file.chunk.start > 0) {
                var range;

                range = 'bytes ' + file.chunk.start 
                    + '-' + file.chunk.end 
                    + '/' + file.size.total;
                file.request.setRequestHeader('Content-Range', range);
            }

            // process chunk
            file.request.send(chunk);
        };

        plugin.chunk_complete = function(index) {
            var file;

            file = file_array[index];

            // check if upload complete
            if (file.chunk.end === file.size.total) {
                // call complete function
                plugin.upload_complete(index);

                return;
            }

            // update file progress
            file.size.uploaded = (file.chunk.start / plugin.setting.chunk_size);
            file.progress = (file.size.uploaded / file.chunk.count);
            file.time.current = new Date();
            file.time.elapsed = (file.time.current - file.time.start);

            // shift chunk pointer up one
            file.chunk.start = file.chunk.end;
            file.chunk.end = (file.chunk.start + plugin.setting.chunk_size);

            // check if progress function exists
            if ($.isFunction(plugin.setting.progress)) {
                // trigger progress callback
                plugin.setting.progress.call(this, index, file);
            }

            // upload next chunk
            plugin.chunk_upload(index);
        };

        plugin.upload_complete = function(index) {
            var file,
                response;

            file = file_array[index];

            // parse response body
            response = JSON.parse(file.request.response);
            // check for any errors
            if (response === true) {
                // update file status
                file.status = 'complete';

                // sanitize progress value
                if (file.progress > 1 || file.progress < 1) file.progress = 1;

                // check if complete function exists
                if ($.isFunction(plugin.setting.complete)) {
                    // trigger complete callback
                    plugin.setting.complete.call(this, index, file);
                }
            }
            // if there is an error
            else {
                // call error function
                plugin.error(index, response.code);
            }
        };

        plugin.count = function(count) {
            if (typeof count !== 'undefined') {
                file_count = count;
            }
            
            return file_count;
        };

        plugin.cancel = function(index) {
            var file,   
                url;

            file = file_array[index];

            // check upload status before aborting
            if (file.status == 'pending' || file.status == 'uploading') {
                file.request.abort();

                url = plugin.setting.directory.script 
                    + '/file.php?action=delete';

                $.ajax({
                    url: url,
                    cache: false,
                    data: {
                        name: file.name.temporary,
                        uri: plugin.setting.directory.upload
                    },
                    success: function(res) {
                        // update file status
                        file.status = 'cancelled';

                        // decrement active file count
                        file_count--;

                        // check if cancel function exists
                        if ($.isFunction(plugin.setting.cancel)) {
                            // trigger cancel callback
                            plugin.setting.cancel.call(this, index, file);
                        }
                    }
                });
            }
        };

        plugin.delete = function(index) {
            var file,   
                url;

            file = file_array[index];

            // check file upload is complete before deleting
            if (file.status == 'complete') {
                url = plugin.setting.directory.script 
                    + '/file.php?action=delete';

                $.ajax({
                    url: url,
                    cache: false,
                    data: {
                        name: file.name.temporary,
                        uri: plugin.setting.directory.upload
                    },
                    success: function(res) {
                        // remove from file array
                        file_array.splice(index, 1);

                        // decrement active file count
                        file_count--;

                        // check if delete function exists
                        if ($.isFunction(plugin.setting.delete)) {
                            // trigger delete callback
                            plugin.setting.delete.call(this, index, file);
                        }
                    }
                });
            }
        };

        plugin.error = function(index, code) {
            var file,
                error;

            file = file_array[index];

            // find correct error message
            switch (code) {
                case 1 :
                    error = {
                        name: 'ERR_MAX_FILES',
                        text: 'Number of uploaded is greater than limit_max.'
                    };
                    break;
                case 2 :
                    error = {
                        name: 'ERR_DIRECTORY',
                        text: 'The specified upload directory does not exist.'
                    };
                    break;
                case 3 :
                    error = {
                        name: 'ERR_WRITABLE',
                        text: 'The specified upload directory does not have write permissions.'
                    };
                    break;
                case 4 :
                    error = {
                        name: 'ERR_NOT_ACCEPTED',
                        text: 'The uploaded file is not in the accepted list of file types.'
                    };
                    break;
                case 5 :
                    error = {
                        name: 'ERR_MAX_SIZE',
                        text: 'The uploaded file size is greater that max_size.'
                    };
                    break;
                default :
                    error = {
                        name: 'ERR_UNKNOWN',
                        text: 'An error occurred.'
                    };
                    break;
            }

            // check if error function exists
            if ($.isFunction(plugin.setting.error)) {
                // trigger error callback
                plugin.setting.error.call(this, index, file, error);
            }
        };

        plugin.init();
    };

    $.fn.file = function(options) {

        return this.each(function() {
            if (typeof $(this).data('file') === 'undefined') {
                var plugin = new $.file(this, options);
                $(this).data('file', plugin);
            }
        });

    };

}(jQuery));
