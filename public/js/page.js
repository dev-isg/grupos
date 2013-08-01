(function($) {
    // Wait for DOM to load
    $(function() {

        /**
         * @namespace Utility functions
         * @name      utils
         */
        var utils = {};

        /**
         * Execute callback and add it to resize event
         *
         * @param {Function} callback
         */
        utils.resize = function(callback) {
            callback();
            $(window).on('resize', callback);
        },

        /** @namespace Media queries */
        utils.layout = (function() {
            var $window = $(window);

            // Callbacks list
            var callbacks = [];

            // Steps on which callbacks will be called
            var steps = [320, 480, 768, 1024, 1280, 1440];

            /**
             * Round width to step
             *
             * @param {Number} width
             *
             * @returns Step
             */
            function step(width) {
                for (var n = 0; n < steps.length; n++)
                    if (width <= steps[n])
                        return steps[n];
                return Number.MAX_VALUE;
            }

            // Last step
            var last = step($window.width());

            // On each step call all callbacks
            utils.resize(function() {
                var current = step($window.width());
                if (current != last) {
                    last = current;
                    for (var n = 0; n < callbacks.length; n++)
                        callbacks[n](current);
                }
            });

            /** @scope utils.layout */
            return {

                /** Return current step */
                width: function() {
                    return last;
                },

                /**
                 * Register callback
                 *
                 * @param {Function} callback
                 */
                change: function(callback) {
                    callback(last);
                    callbacks.push(callback);
                }
            };
        })();

       
        /**
         * Toggle items by category(attribute data-category, multiple categories must separed by comma)
         *
         * @param {String|Object} items    Items to filter
         * @param {String}        category Category
         */
        utils.filter = function(items, category) {
            var $items = $(items);
            if (category == 'all')
                $items.show();
            else
                $items.each(function() {
                    var $item = $(this);
                    var categories = $item.attr('data-category').split(',');
                    $item.toggle($.inArray(category, categories) != -1);
                });
        };

        /**
         * Detect iDevice
         *
         * @returns {Boolean}
         */
        utils.ios = function() {
            return navigator.userAgent.match(/iPad/i) || navigator.userAgent.match(/iPhone/i);
        }

        /**
         * @namespace UI elements
         * @name      ui
         */
        var ui = {};

        /** Animation speed */
        ui.speed = 350;

        /** @namespace Preloader */
        ui.preloader = (function() {
            var speed = 500;
            var $images = $([]);
            var $preloader = $([]);

            /** @scope ui.preloader */
            return {

                /** Add image */
                add: function(src) {
                    if ($preloader.length)
                        $images = $images.add('<img src="' + src + '" />');
                },

                /** Show preloader */
                show: function() {
                    $preloader.show();
                },

                /**
                 * Hide preloader after all images are loaded
                 *
                 * @param {Function} callback
                 */
                hide: function(callback) {
                    if (!$preloader.length)
                        return;
                    $images
                        .add('img')
                        .loaded(function() {
                            $preloader.fadeOut(speed, callback || $.noop);
                        });
                },

                /** Initialization */
                init: function() {
                    $preloader = $('[data-ui="preloader"]');
                }
            };
        })();


        /** @namespace Flip effect */
        ui.flip = {};

        /**
         * Flip
         *
         * @param {String|Object} element Selector or element object
         * @param {Boolean}       [state] Value of data-flip attribute
         */
        ui.flip.toggle = function(element, state) {
            $element = $(element);
            state = state === undefined ? $element.attr('data-flip') != 'true' : state;
            $element.attr('data-flip', state);
        };

        /** Initialization */
        ui.flip.init = function() {
            $('[data-ui="flip"]')
                .wrapInner('<div class="content" />')
                .each(function() {
                    var $flip = $(this);
                    $flip.attr({
                        'data-flip': $flip.attr('data-flip') == 'true',
                        'data-axis': $flip.data('axis') || 'y'
                    });
                });
        };

        /** @namespace Scrollbars */
        ui.scrollbar = {};

        /** Enable custom scrollbars */
        ui.scrollbar.enabled = false;

        /**
         * Call an callback on each of selected scrollbars
         *
         * @param {String|Object} [scrollbars] Selector or element object
         * @param {Function}      [callback]   Callback function
         */
        ui.scrollbar.each = function(scrollbars, callback) {
            scrollbars = scrollbars || '[data-ui-scrollbar]';
            $(scrollbars).each(callback);
        };

        /**
         * Refresh scrollbars
         *
         * @param {String|Object} [scrollbars] Selector or element object
         */
        ui.scrollbar.refresh = function(scrollbars, force) {
            if (ui.scrollbar.enabled || force)
                ui.scrollbar.each(scrollbars, function() {
                    var api = ui.scrollbar.api(this);
                    if (api)
                        api.reinitialise();
                    else
                        ui.scrollbar.init(this);
                });
        };

        /**
         * Reset scrollbars
         *
         * @param {String|Object} [scrollbars] Selector or element object
         */
        ui.scrollbar.reset = function(scrollbars, force) {
            if (ui.scrollbar.enabled || force) {
                scrollbars = scrollbars || '[data-ui-scrollbar]';
                ui.scrollbar.destroy(scrollbars);
                ui.scrollbar.init(scrollbars);
            }
        };

        /**
         * Destroy scrollbars
         *
         * @param {String|Object} [scrollbars] Selector or element object
         */
        ui.scrollbar.destroy = function(scrollbars, force) {
            if (ui.scrollbar.enabled || force)
                ui.scrollbar.each(scrollbars, function() {
                    var api = ui.scrollbar.api(this);
                    if (api)
                        api.destroy();
                });
        };

        /**
         * Scroll to
         *
         * @param {String|Object} [scrollbars]    Selector or element object
         * @param {Number}        [x=0]           Horizontal position
         * @param {Number}        [y=0]           Vertical position
         * @param {Boolean}       [animate=false] Animate scroll
         */
        ui.scrollbar.scrollTo = function(scrollbars, x, y, animate) {
            if (ui.scrollbar.enabled || force) {
                x = x || 0;
                y = y || 0;
                animate = animate || false;
                ui.scrollbar.each(scrollbars, function() {
                    var api = ui.scrollbar.api(this);
                    if (api)
                        api.scrollTo(x, y, animate || false);
                    else {
                        $(this)
                            .scrollLeft(x)
                            .scrollTop(y);
                    }
                });
            }
        };

        /**
         * Scroll to top
         *
         * @param {String|Object} [scrollbars]    Selector or element object
         * @param {Boolean}       [animate=false] Animate scroll
         */
        ui.scrollbar.scrollTop = function(scrollbars, animate) {
            ui.scrollbar.scrollTo(scrollbars);
        };

        /**
         * Get API object
         *
         * @param   {String|Object} scrollbar Selector or element object
         *
         * @returns {Object}                  jScrollPane API object
         */
        ui.scrollbar.api = function(scrollbar) {
            return $(scrollbar).data('jsp');
        };

        /**
         * Check if scrollbar exists on certain element
         *
         * @param   {String|Object} scrollbar Selector or element object
         *
         * @returns {Boolean}
         */
        ui.scrollbar.exists = function(scrollbar) {
            return !!ui.scrollbar.api(scrollbar);
        };

        /**
         * Initialize scrollbars
         *
         * @param {String|Object} [scrollbars] Selector or element object
         */
        ui.scrollbar.init = function(scrollbars, force) {

            // Default
            if (!ui.scrollbar.enabled && !force) {
                ui.scrollbar.each(scrollbars, function() {
                    var $scrollbar = $(this);
                    var $pane =
                    $('<div class="jspPane jspContainer" />').css({
                        position: 'relative',
                        padding: $scrollbar.css('padding'),
                        paddingTop: $scrollbar.css('padding-top'),
                        paddingLeft: $scrollbar.css('padding-left'),
                        paddingRight: $scrollbar.css('padding-right'),
                        paddingBottom: $scrollbar.css('padding-bottom')
                    });
                    $scrollbar
                        .css({
                            padding: 0,
                            overflow: 'auto'
                        })
                        .wrapInner($pane)
                        .on('scroll', function(event) {
                            $(window).trigger('scroll', event);
                        });
                });
            }

            // Custom
            else
                ui.scrollbar.each(scrollbars, function() {
                    var $scrollbar = $(this);
                    if (!ui.scrollbar.exists(this)) {
                        $scrollbar.attr('data-ui-scrollbar', true);
                        var reinit = $scrollbar.data('ui-scrollbar-reinit');
                        $scrollbar.jScrollPane({
                            autoReinitialise: reinit === undefined || reinit,
                            autoReinitialiseDelay: utils.ios() ? 250 : 20,
                            animateScroll: true,
                            animateSteps: true,
                            animateDuration: 200,
                            mouseWheelSpeed: 200,
                            keyboardSpeed: 120
                        });
                    }
                });
        };



        /** UI initialization */
        ui.init = function() {
            ui.preloader.init();
            /*
            ui.flip.init();*/
            ui.scrollbar.init();

        };


        /**
         * @namespace Common functions
         * @name      common
         */
        var common = {};

        /** Browser sniffing */
        common.browser = function() {
            var version = parseInt($.browser.version);
            if ($.browser.mozilla)
                $('html').attr('data-mozilla', version);
            else if ($.browser.webkit)
                $('html').attr('data-webkit', version);
            else if ($.browser.opera)
                $('html').attr('data-opera', version);
            else if ($.browser.safari)
                $('html').attr('data-safari', version);
            else if ($.browser.msie)
                $('html').attr('data-msie', version);
        };





        /** @namespace Sidebar */
        var bar = (function() {

            // Bar toggle speed
            var speed = 250;

            // Menu animation speed
            var menuSpeed = 250;

            // Overlay edge
            var overlay = 480;

            // Bar visibility flag
            var visible = true;

            // Cached elements
            var $body = $('body');
            var $bar = $('#bar');
            var $switch = $('.switch', $bar);
            var $panel = $('#panel');

            // Bar width
            var width = $bar.outerWidth();

            // Animation step callback
            var step = $.noop;

            /** Toggle scrollbar flag class */
            function scrollbar() {
                var $items = $('.menu > li > a', $bar);
                $items.addClass('transition_none');
                $bar.toggleClass('scrollbar', $('.jspVerticalBar', $bar).is(':visible'));
                setTimeout(function() {
                    $items.removeClass('transition_none');
                }, 250);
            }

            /** @scope bar */
            return {

                /**
                 * Toggle sidebar
                 *
                 * @param {Boolean} show    Sidebar state
                 * @param {Boolean} animate Transition
                 */
                toggle: function(show, animate) {
                    show = show === undefined ? !visible : show;
                    if (show && utils.layout.width() <= 768 && info.visible())
                        info.hide();
                    visible = show;
                    animate = animate === undefined ? true : animate;

                    // Animation
                    $bar
                        .stop()
                        .animate(
                            {left: show ? 0 : -width},
                            {
                                duration: animate ? speed : 0,
                                complete: function() {
                                    step();
                                    $bar.toggleClass('hidden', !visible);
                                    width = $bar.outerWidth();
                                    ui.scrollbar.refresh('#bar');
                                    $(window).trigger('resize');
                                    scrollbar();
                                },
                                step: step
                            }
                        );

                    // Refresh
                    $bar
                        .find('.submenu.open')
                        .removeClass('open')
                        .find('> ul')
                        .slideUp(0);
                    ui.scrollbar.refresh('#bar');
                    ui.flip.toggle($switch, !visible);
                    var left = utils.layout.width() > overlay ? (show ? width : 0) : 0;
                    $body
                        .stop()
                        .animate({paddingLeft: left}, animate ? speed : 0);
                    $panel
                        .stop()
                        .animate({left: left}, animate ? speed : 0);
                },

                /**
                 * Show sidebar
                 *
                 * @param {Boolean} [animate=true] Transition
                 */
                show: function(animate) {
                    animate = animate === undefined ? true : animate;
                    bar.toggle(true, animate);
                },

                /**
                 * Hide sidebar
                 *
                 * @param {Boolean} [animate=true] Transition
                 */
                hide: function(animate) {
                    animate = animate === undefined ? true : animate;
                    bar.toggle(false, animate);
                },

                /** @returns Bar state */
                visible: function() {
                    return visible;
                },

                /** Animation step callback */
                step: function(callback) {
                    step = callback || step;
                },

                /**
                 * Initialization
                 *
                 * @param {Object}   [options]             Options
                 * @param {Number}   [options.overlay=480] Step from which bar will overlay main content
                 * @param {Function} [options.step]        Callback which will be called on each step of bar toggle animation
                 */
                init: function(options) {
                    options = options || {};
                    overlay = options.overlay || overlay;
                    step = options.step || step;
                    $panel.css('left', $bar.outerWidth());
                    var $footer = $('.footer', $bar);
                    if ($footer.length)
                        $('.scrollbar', $bar).css('padding-bottom', $footer.outerHeight());

                    // Scrollbar
                    ui.scrollbar.init('#bar');

                    // Switch
                    $('.switch', $bar).on('click', function() {
                        bar.toggle();
                    });
                    $(window).on('resize', function() {
                        width = $bar.outerWidth();
                        $bar.css('left', visible ? 0 : -width);
                    });

                    // Hide menu when switching to overlay
                    utils.layout.change(function(width) {
                        var show = width > overlay;
                        if (show != bar.visible())
                            if (ui.scrollbar.enabled)
                                ui.scrollbar.api('#bar').scrollTo(0, 0, false);
                            else
                                $bar.scrollTop(0);
                        bar.toggle(show, false);
                    });

                    // Normal menu - hover
                    $bar.on({
                        mouseenter: function() {
                            if (utils.layout.width() > 480)
                                $('ul', this).stop(true, true).show('slide', {direction: 'left'}, menuSpeed);
                        },
                        mouseleave: function() {
                            if (utils.layout.width() > 480)
                                $('ul', this).stop(true, true).hide('slide', {direction: 'left'}, menuSpeed);
                        }
                    }, '.submenu');

                    // Mobile menu - accordion
                    function toggle($submenu) {
                        if (!$submenu.length)
                            return;
                        var open = $submenu.is(':visible');
                        var $siblings = $submenu
                            .parent()
                            .siblings()
                            .filter('.submenu');
                        $submenu['slide' + (open ? 'Up' : 'Down')](menuSpeed, function() {
                            $siblings.removeClass('open');
                            $submenu
                                .parent()
                                .toggleClass('open', !open);
                        });
                        $siblings
                            .find('> ul')
                            .slideUp(menuSpeed);
                    }
                    $bar

                        // Toggle submenu on click
                        .on('click', '.submenu > a', function() {
                            if (utils.layout.width() > 480)
                                return false;
                            toggle($(this).next('ul'));
                            return false;
                        })

                        // Mark items that contains submenu
                        .find('.menu > li')
                        .each(function() {
                            $(this).toggleClass('submenu', !!$('ul', this).length);
                        })
                        .filter('.submenu')
                        .find('> a')
                        .append('<span class="ico" />');

                    // Close submenu when switching overlay
                    utils.layout.change(function(width) {
                        if (width > 480)
                            toggle($('.submenu.open > ul', $bar));
                    });

                    // Filter
                    if (options.filter)
                        $.each(options.filter, function(name, callback) {
                            $bar.on('change', 'input[name="' + name + '"]', function() {
                                callback($(this).val());
                            });
                        });
                    $('.filter', $bar)

                        // Style radiobuttons
                        .find('input')
                        .each(function(id) {
                            id = 'bar_filter' + id;
                            $(this)
                                .attr('id', id)
                                .after('<label for="' + id + '" ><span></span></label>')
                                .parent()
                                .next()
                                .find('label')
                                .attr('for', id);
                        });

                    // Safari weird :checked behavior fix
                    if ($.browser.safari)
                        $('input[checked="checked"]')
                            .removeAttr('checked')
                            .prop('checked', true);

                    // Footer
                    var $placeholder = $('<div class="placeholder" />').appendTo($('.jspPane', $bar));
                    utils.resize(function() {
                        scrollbar();
                        $placeholder.css('height', $('.footer', $bar).outerHeight());
                    });
                }
            };
        })();

        /**
         * @namespace Blog
         * @name      blog
         */
        var blog = {};

        /** Filter posts */
        blog.filter = function(category) {
            var $items = $('#content li');
            $items
                .not(':visible')
                .css({
                    left: 0,
                    top: 0
                });
            utils.filter('#content li', category);
            $('#content ul').masonry('reload');
        },

        /** Organize posts */
        blog.posts = function() {
            var $container = $('#content ul');
            var $items = $('li', $container);
            var container = $container.width();
            var columns = Math.ceil(container / 375);
            var item = Math.floor((container - (columns - 1) * 30) / columns);
            item = columns == 2 && item < 250 ? container : item;
            $items.width(item);
            $container.masonry({
                itemSelector : 'li:visible',
                columnWidth: item,
                gutterWidth: 30
            });
        };


        /** @namespace Initialization */
        var init = {

            /** Common */
            common: function() {
                common.browser();
                ui.init();
            },
            /** Blog home */
            blog: function() {
                bar.init({
                    overlay: 768,
                    step: function() {
                        setTimeout(blog.posts, 200);
                    },
                    filter: {
                        category: blog.filter
                    }
                });
                blog.posts();
                $(window).on('resize', function() {
                    setTimeout(blog.posts, 200);
                });
                ui.preloader.hide();
            }
          
        };

        // Initialization
        init.common();
        var page = $('body').attr('id');
        if (init[page])
            init[page]();
        else
            init['default']();
    });
})(jQuery);