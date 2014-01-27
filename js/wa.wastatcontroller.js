(function($) {
    $.wa.wastatcontroller = {
        debug: false,
        init: function(options) {
            var self = this;
            if (typeof ($.History) != "undefined") {

                $.History.bind(function(hash) {
                    self.dispatch(hash);
                });
            }

        },
        parseParams: function(params) {
            if (!params) {
                return {};
            }
            var p = params.split('&');
            var result = {};
            for (var i = 0; i < p.length; i++) {
                var t = p[i].split('=');
                result[t[0]] = t.length > 1 ? t[1] : '';
            }
            return result;
        },
        parsePath: function(path) {
            path = path.replace(/^.*#\//, '').replace(/(^\/|\/$)/, '');

            var matches = path.split('/');
            var tail = matches.pop();
            var params = {};

            if (tail.match(/^[\w_\-]+=/)) {
                params = this.parseParams(tail);
            } else {
                matches.push(tail);
            }
            return {
                id: matches[0],
                params: params,
                tail: tail
            };
        },
        dispatch: function(path) {
            if (typeof (path) == 'string') {
                path = this.parsePath(path);
            }
            if (path.id) {

                //alert(actionName);
                //return;
                this.execute(path.id, path.tail);
            } else {
                this.defaultAction();
            }
            /*
             if (hash) {
             hash = hash.replace(/^.*#/, '');
             hash = hash.split('/');
             if (hash[0]) {
             var actionName = "";
             var attrMarker = hash.length;
             for (var i in hash) {
             var h = hash[i];
             if (i < 2) {
             if (i == 0) {
             actionName = h;
             } else if (h.match(/[a-z]+/i)) {
             actionName += h.substr(0, 1).toUpperCase() + h.substr(1);
             } else {
             attrMarker = i;
             break;
             }
             } else {
             attrMarker = i;
             break;
             }
             }
             var attr = hash.slice(attrMarker);
             this.execute(actionName, attr);
             } else {
             this.defaultAction();
             }
             } else {
             this.defaultAction();
             }*/
        },
        execute: function(actionName, attr) {
            this.trace('execute', [actionName, attr]);
            if (this[actionName + 'Action']) {
                this.currentAction = actionName;
                this.currentActionAttr = attr;
                this[actionName + 'Action'](attr);
            } else {
                this.log('Invalid action name:', actionName + 'Action');
                $.wa.setHash('#');
                this.dispatch('#');
            }
        },
        defaultAction: function() {
            return this.sendRequest(
                    url,
                    function(data) {
                        $('#main_content').html(data.data.content);
                    }
            );
        },
        downloadAction: function() {
            this.load('?action=download');
        },
        productsAction: function() {
            this.load('?action=products');
        },
        productStatisticAction: function(params) {
            url = '?action=productStatistic';
            if (params) {
                url = url + '&' + params;
            }
            var self = this;
            this.load(url, function() {
                self.initFilterForm();
            });
        },
        orderHistoryAction: function() {
            this.load('?action=orderHistory');
        },
        settingsAction: function() {
            var self = this;
            this.load('?action=settings', function() {
                self.initSaveAuth();
            });
        },
        initFilterForm: function() {
            $('#filter-form').submit(function() {
                form = $(this);
                location.href = form.attr('action') + form.serialize();
                
                return false;
            });

        },
        initSaveAuth: function() {
            $('#save_auth').submit(function() {
                form = $(this);
                form.find('.response').html('<i class="icon16 loading"></i>Загруза');
                $.post(form.attr('action'), form.serialize(),
                        function(response) {
                            if (response.status == 'ok') {
                                form.find('.response').html('<i class="icon16 yes"></i>Сохранено');
                            } else if (response.status == 'fail') {
                                form.find('.response').html('<i class="icon16 no"></i>' + response.errors.join('<br>'));
                            }
                            setTimeout(function() {
                                form.find('.response').html('');
                            }, 3000);
                        }, 'json');
                return false;
            });
        },
        load: function(url, options, fn) {
            if (typeof options === 'function') {
                fn = options;
                options = {};
            } else {
                options = options || {};
            }
            var r = Math.random();
            this.random = r;
            var self = this;
            (options.content || $("#wastat-content")).html('<i class="icon16 loading"></i>Загрузка...');
            return  $.get(url, function(result) {

                (options.content || $("#wastat-content")).html(result);
                if (typeof fn === 'function') {
                    fn.call(this);
                }
                $('html, body').animate({scrollTop: 0}, 200);
                $('.level2').show();
                $('#s-sidebar').width(200).show();
            });
        },
        sendRequest: function(url, request_data, success_handler, error_handler) {
            var self = this;
            return $.ajax({
                'url': url,
                'data': request_data || {},
                'type': 'POST',
                'success': function(data, textStatus, XMLHttpRequest) {
                    try {
                        data = $.parseJSON(data);
                    } catch (e) {
                        self.log('Invalid server JSON responce', e);
                        if (typeof (error_handler) == 'function') {
                            error_handler();
                        }
                        self.error('Invalid server responce'.translate() + '<br>' + e, 'error');
                    }
                    if (data) {
                        switch (data.status) {
                            case 'fail':
                                {
                                    self.error(data.errors.error || data.errors, 'error');
                                    if (typeof (error_handler) == 'function') {
                                        error_handler(data);
                                    }
                                    break;
                                }
                            case 'ok':
                                {
                                    if (typeof (success_handler) == 'function') {
                                        success_handler(data.data);
                                    }
                                    break;
                                }
                            default:
                                {
                                    self.log('unknown status responce', data.status);
                                    if (typeof (error_handler) == 'function') {
                                        error_handler(data);
                                    }
                                    break;
                                }
                        }
                    } else {
                        self.log('empty responce', textStatus);
                        if (typeof (error_handler) == 'function') {
                            error_handler();
                        }
                        self.error('Empty server responce'.translate(), 'warning');
                    }

                },
                'error': function(XMLHttpRequest, textStatus, errorThrown) {
                    self.log('AJAX request error', textStatus);
                    if (typeof (error_handler) == 'function') {
                        error_handler();
                    }
                    self.error('AJAX request error'.translate(), 'warning');
                }
            });
        },
        error: function(message, type) {
            var container = $('#wa-system-notice');
            if (container) {
                //TODO use correct message box
                var delay = 1500;
                switch (type) {
                    case 'error':
                        {
                            delay = 6000;
                            message = '<i class="icon16 bug"></i>' + message;
                            break;
                        }
                    case 'warning':
                        {
                            message = '<i class="icon16 no"></i>' + message;
                            break;
                        }
                }
                container.html(message);
                container.slideDown().delay(delay).slideUp();
            } else {
                alert(message);
            }

        },
        log: function(message, params) {
            if (console) {
                console.log(message, params);
            }
        },
        trace: function(message, params) {
            if (console && this.debug) {
                console.log(message, params);
            }
        }
    };
})(jQuery);
