(function($) {
    
    /*****************************************************************
     * Select
     *****************************************************************/
    function Select(el, options) {
        this.$el = $(el);
        this.id = Math.random();
        this.options = options;
        this.multiple = this.$el.prop('multiple');
        this.activeOpt = null;
        this.widthSet = false;

        this.generate();
    }
    
    Select.prototype = {
        generate: function() {
            if (!this.$select) {
                var _self = this;

                this.$select = $('<div class="wSelect"><div class="wSelect-arrow"></div></div>');
                this.$optionsHolder = $('<div class="wSelect-options-holder"></div>');
                this.$options = $('<div class="wSelect-options"></div>');

                // ie 7 fix to get proper zIndex on select dropdowns
                if(!$.support.placeholder) {
                    this.$select.css('zIndex', 100 - this.$el.index());
                }

                var click = function(e) {
                    e.stopPropagation();

                    $('select').each(function() {
                        var wSelect = $(this).data('wSelect');

                        if (wSelect && wSelect.id !== _self.id) {
                            if (!wSelect.multiple) { wSelect.$optionsHolder.hide(); }
                            wSelect.onBlur();
                        }
                    });

                    if (!_self.multiple) { _self.onClick(e); }
                    _self.$el.focus();
                };

                if (this.multiple) {
                    this.$select.addClass('wSelect-multiple');
                    this.$optionsHolder.click(click);
                }
                else {
                    this.$selected = $('<div class="wSelect-selected"></div>');
                    this.$select.append(this.$selected);
                    this.$select.click(click);
                    this.$optionsHolder.click(function(e) {
                        e.stopPropagation();
                        _self.$el.focus();
                    });
                }

                this.$select.hover(
                    function(){ _self.onFocus('hover'); },
                    function(){ _self.onBlur('hover'); }
                );

                this.$el.addClass('wSelect-el')
                .change(function() { _self.change(); })
                .focus(function() { _self.onFocus(); })
                .keydown(function(e) { _self.keydown(e); })
                .keyup(function(e) { _self.keyup(e); });
                
                $(document).click(function() {
                    if (!_self.multiple) { _self.$optionsHolder.hide(); }
                    _self.onBlur();
                });

                this.widthSet = this.$select.width() > 0;
                this.setTheme(this.options.theme);
                this.setSize(this.options.size);
                
                this.reset();
                this.$optionsHolder.append(this.$options);
                this.$select.append(this.$optionsHolder);
                this.$el.after(this.$select);//.hide();
            }

            return this.$select;
        },

        reset: function() {
            var _self = this;
            
            this.$options.children().remove();
            this.$el.children().each(function() {
                var option = new Option(this, _self);
                $.data(this, 'wSelect-option', option);

                _self.$options.append(option.generate());
            });

            this.$options.children().removeClass('wSelect-option-last').last().addClass('wSelect-option-last');
            this.setSize(this.options.size);
        },

        change: function() {
            this.$options.children().removeClass('wSelect-option-selected wSelect-option-active');

            this.$el.children(':selected').each(function() {
                $(this).data('wSelect-option').select();
            });
        },

        keydown: function(e) {
            // tab
            if (e.keyCode === 9) {
                this.$optionsHolder.hide();
                this.onBlur();
            }
        },

        keyup: function(e) {
            // enter
            if (e.keyCode === 13) {
                this.$optionsHolder.hide();
            }
            // left, up, right, down
            else if (e.keyCode >= 37 && e.keyCode <= 40) {
                this.change();

                var $option = this.$options.find('.wSelect-option-selected:last'),
                    scrollTop = this.$options.scrollTop(),
                    top = $option.position().top + scrollTop,
                    optionsHeight = this.$options.height(),
                    optionHeight = $option.outerHeight(true);

                if (top - scrollTop < 0) {
                    this.$options.scrollTop(top);
                }
                else if (top + optionHeight - scrollTop > optionsHeight) {
                    this.$options.scrollTop(top - optionsHeight + optionHeight);
                }
            }
        },

        onClick: function(e) {
            // find best fit for dropdowns (top or bottom)
            if (!this.$optionsHolder.is(':visible')) {
                var top = this.$select.offset().top - $(window).scrollTop(),
                    optionsHeight = this.$optionsHolder.outerHeight(),
                    topDiff = top - optionsHeight,
                    botDiff = $(window).height() - (top + this.$select.outerHeight() + optionsHeight + 5), // 5 is just for some bottom screen padding
                    newTop = (botDiff > 0 || botDiff > topDiff) ? this.$select.height() : -optionsHeight;
                
                this.$optionsHolder.css('top', newTop);
            }

            this.$optionsHolder.toggle();
        },

        onFocus: function(className) {
            className = className || 'active';

            if (this.options.highlight) {
                this.$select.addClass('wSelect-' + className);
            }
        },

        onBlur: function(className) {
            className = className || 'active';

            if (this.options.highlight) {
                this.$select.removeClass('wSelect-' + className);
            }
        },

        setTheme: function(theme) {
            this.$select.attr('class', this.$select.attr('class').replace(/wSelect-theme-.+\s|wSelect-theme-.+$/, ''));
            this.$select.addClass('wSelect-theme-' + theme);
        },

        setSize: function(size) {
            var $option = this.$options.children(':first').clone().css({position:'absolute', left:-10000}),
                numOptions = this.$el.children().length,
                height;
            
            $('body').append($option);
            height = $option.outerHeight(true);
            $option.remove();

            if (!this.multiple && size > numOptions) {
                size = numOptions;
            }

            this.$options.height(height * size - 1);
        }
    };

    /*****************************************************************
     * Option
     *****************************************************************/
    function Option(el, wSelect) {
        this.$el = $(el);
        this.wSelect = wSelect;
    }

    Option.prototype = {
        generate: function() {
            var _self = this;
            if (!this.$option) {
                var icon = this.$el.attr('data-icon');

                this.$option = $('<div class="wSelect-option"></div>');
                this.$value = $('<div class="wSelect-option-value"></div>');
                this.$option.append(this.$value);

                if (typeof icon === 'string') {
                    this.$value.addClass('wSelect-option-icon');
                    this.$value.css('backgroundImage', 'url(' + icon + ')');
                }
            }

            if (this.$el.prop('selected')) {
                this.select();
            }

            if (this.$el.prop('disabled')) {
                this.$option.addClass('wSelect-option-disabled');
            }
            else {
                this.$option.removeClass('wSelect-option-disabled');
                this.$option.unbind('click').click(function(e){ _self.onClick(e); });
            }
            
            this.$value.html(this.$el.html()); // in case html has changed we always set it here
            this.setWidth();

            return this.$option;
        },

        select: function() {
            if (!this.wSelect.activeOpt) {
                this.wSelect.activeOpt = this;
            }

            if (!this.wSelect.multiple) {
                var icon = this.$el.attr('data-icon');

                if (typeof icon === 'string') {
                    this.wSelect.$selected.addClass('wSelect-option-icon');
                    this.wSelect.$selected.css('backgroundImage', 'url(' + icon + ')');
                }
                else {
                    this.wSelect.$selected.removeClass('wSelect-option-icon');
                    this.wSelect.$selected.css('backgroundImage', '');
                }

                //if(!this.wSelect.focus) { this.wSelect.$optionsHolder.hide(); }
                this.wSelect.$selected.html(this.$el.html());
            }

            this.$option.addClass('wSelect-option-selected');
        },

        onClick: function(e) {
            var selVal = null;

            if (this.wSelect.multiple && (e.ctrlKey || e.shiftKey) ) {
                if (e.ctrlKey || !this.wSelect.activeOpt) {
                    selVal = this.wSelect.$el.val() || [];

                    var optVal = this.$el.val(),
                        arrayPos = $.inArray(optVal, selVal);

                    if (arrayPos === -1) {
                        selVal.push(this.$el.val());
                        this.wSelect.activeOpt = this; // only set active when "selecting"
                    }
                    else {
                        selVal.splice(arrayPos, 1);
                    }
                }
                // don't set active here as the shift+click only highlights from active option
                else if (e.shiftKey) {
                    var indexActive = this.wSelect.activeOpt.$el.index(),
                        indexCurrent = this.$el.index(),
                        indexStart = 0,
                        indexEnd = 0,
                        $option = null;

                    if (indexCurrent > indexActive) {
                        indexStart = indexActive;
                        indexEnd = indexCurrent;
                    } else {
                        indexStart = indexCurrent;
                        indexEnd = indexActive;
                    }

                    selVal = [];

                    for (var i=indexStart; i<=indexEnd; i++) {
                        $option = this.wSelect.$el.children(':eq(' + i + ')');
                        if ($option.is(':not(:disabled)')) {
                            selVal.push($option.val());
                        }
                    }
                }
            }
            else {
                selVal =  this.$el.val();
                this.wSelect.$optionsHolder.hide();
                this.wSelect.activeOpt = this;
            }

            this.wSelect.$el.val(selVal).change();
        },

        // help us set the proper widths based on given values (this way so we can add options on the fly one at a time)
        setWidth: function() {
            if (this.wSelect.multiple || this.wSelect.widthSet) { return true; }

            this.$option.hide().appendTo('body');
            var optionWidth = this.$option.width();

            if (optionWidth > this.wSelect.$select.width()) {
                this.wSelect.$select.width(optionWidth);
            }
            
            this.$option.detach().show();
        }
    };

    /*****************************************************************
     * fn.wSelect
     *****************************************************************/
    $.support.placeholder = 'placeholder' in document.createElement('input');

    $.fn.wSelect = function(options, value) {
        if (typeof options === 'string') {
            var values = [];
            var elements = this.each(function() {
                var wSelect = $(this).data('wSelect');

                if (wSelect) {
                    var func = (value ? 'set' : 'get') + options.charAt(0).toUpperCase() + options.substring(1).toLowerCase();

                    if (wSelect[options]) {
                        wSelect[options].apply(wSelect, [value]);
                    } else if (value) {
                        if (wSelect[func]) { wSelect[func].apply(wSelect, [value]); }
                        if (wSelect.options[options]) { wSelect.options[options] = value; }
                    } else {
                        if(wSelect[func]) { values.push(wSelect[func].apply(wSelect, [value])); }
                        else if (wSelect.options[options]) { values.push(wSelect.options[options]); }
                        else { values.push(null); }
                    }
                }
            });

            if (values.length === 1) { return values[0]; }
            else if (values.length > 0) { return values; }
            else { return elements; }
        }

        options = $.extend({}, $.fn.wSelect.defaults, options);

        function get(el) {
            var wSelect = $.data(el, 'wSelect');
            if (!wSelect) {
                var _options = jQuery.extend(true, {}, options);
                _options.size = $(el).prop('size') || _options.size;

                wSelect = new Select(el, _options);
                $.data(el, 'wSelect', wSelect);
            }

            return wSelect;
        }

        return this.each(function() { get(this); });
    };
    
    $.fn.wSelect.defaults = {
        theme: 'classic',         // theme
        size: '4',                // default number of options to display (overwrite with `size` attr on `select` element)
        highlight: true           // highlight fields when selected
    };
    
})(jQuery);
