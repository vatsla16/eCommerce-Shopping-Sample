(function ($) {
    'use strict';
    var in_scroll = false;
    function triggerEvent(a, b) {
        var c;
        document.createEvent ? (c = document.createEvent("HTMLEvents"), c.initEvent(b, !0, !0)) : document.createEventObject && (c = document.createEventObject(), c.eventType = b), c.eventName = b, a.dispatchEvent ? a.dispatchEvent(c) : a.fireEvent && htmlEvents["on" + b] ? a.fireEvent("on" + c.eventType, c) : a[b] ? a[b]() : a["on" + b] && a["on" + b]()
    }
    var InitSlider = function () {
        if ($.fn.slider && $('.wpf_slider').length > 0) {
            $('.wpf_slider').each(function () {
                var $wrap = $(this).closest('.wpf_item'),
                        $min = $wrap.find('.wpf_price_from'),
                        $max = $wrap.find('.wpf_price_to'),
                        $min_val = parseInt($(this).data('min')),
                        $max_val = parseInt($(this).data('max')),
                        $price_format = $wrap.find('.wpf_price_format').html(),
                        $form = $wrap.closest('form'),
                        $v1 = parseInt($min.val()),
                        $v2 = parseInt($max.val());
                // check for valid numbers in data-min and data-max
                if ($min_val === parseInt($min_val, 10) && $max_val === parseInt($max_val, 10)) {
                    $v1 = $v1 ? $v1 : $min_val;
                    $v2 = $v2 ? $v2 : $max_val;
                    $(this).slider({
                        range: true,
                        min: $min_val,
                        step: 1,
                        max: $max_val,
                        values: [$v1, $v2],
                        slide: function (event, ui) {
                            $(ui.handle).find('.wpf_tooltip_amount').html(ui.value);
                        },
                        stop: function (event, ui) {
                            $min.val(ui.values[ 0 ]);
                            $max.val(ui.values[ 1 ]);
                            if ($form.hasClass('wpf_submit_on_change')) {
                                $form.submit();
                            }
                        },
                        create: function (event, ui) {
                            $min.val($v1);
                            $max.val($v2);
                            var tooltip = '<span class="wpf-slider-tooltip"><span class="wpf-slider-tooltip-inner">' + $price_format.replace('0', '<span class="wpf_tooltip_amount">' + $v1 + '</span>') + '</span><span class="wpf-slider-tooltip-arrow"></span></span>',
                                    $slider = $(this).children('.ui-slider-handle');
                            $slider.first().html(tooltip);
                            $slider.last().html(tooltip.replace($v1, $v2));
                        }
                    });
                }
            });
        }
    };

    var InitGroupToggle = function () {
        $('body').on( 'click','.wpf_items_grouped:not(.wpf_layout_horizontal) .wpf_item_name', function (e) {

            var $wrap = $(this).next('.wpf_items_group'),
                    $this = $(this);
            if ($this.closest('.wpf_item_onsale,.wpf_item_instock').length > 0) {
                return true;
            }
            e.preventDefault();
            if ($wrap.is(':visible')) {
                $wrap.slideUp(function () {
                    $this.addClass('wpf_grouped_close');
                });
            }
            else {
                $wrap.slideDown(function () {
                    $this.removeClass('wpf_grouped_close');
                });
            }
        });
		$('.wpf_items_grouped:not(.wpf_layout_horizontal) .wpf_item_name').trigger('click');
    };

    var InitSubmit = function () {
        var masonryData, isMasonry;

        $('body').on( 'submit','.wpf_form', function (e) {
            e.preventDefault();
            var $form = $(this),
                    $container = $('.wpf-search-container').first(),
                    data = $form.serializeArray(),
                    result = {};
            if ($container.length === 0) {
                $container = $('.post').first();
            }
            for (var i in data) {
                if ($.trim(data[i].value)) {
                    var name = data[i].name.replace('[]', '');
					
                    if (!result[name]) {
                        result[name] = data[i].value;
                    }
                    else {
                        result[name] += ',' + data[i].value;
                    }
                }
            }
            if (in_scroll) {
               result['append'] = 1;
            }
            $form.find('input[name="wpf_page"]').val('');
            if (!$form.hasClass('wpf_form_ajax')) {
                for (var $name in result) {
                    var input = $form.find('input[name="' + $name + '[]"]');
                    if (input.length > 0) {
                        input.prop({'disabled': true, 'name': ''}).filter(':checked').prop({'disabled': false, 'name': $name}).val(result[$name]);
                    }
                }
                $('body').off('submit','.wpf_form');
                $form.submit();
                return false;
            }

            // Save isotope data if masonry is enabled
            masonryData = masonryData || $( '.products', $container ).data( 'isotope' );
            isMasonry = isMasonry || typeof masonryData === 'object' && 'options' in masonryData;

            $.ajax({
                url: $form.prop('action'),
                type: 'POST',
                data: $.param(result),
                beforeSend: function () {
                    $form.addClass('wpf-search-submit');
                    $container.addClass('wpf-container-wait');
                },
                complete: function () {
                    $form.removeClass('wpf-search-submit');
                    $container.removeClass('wpf-container-wait');
                },
                success: function (resp) {
                    if (resp) {
                        var scrollTo = $container,
                            products=null;
                        $container.data('slug', $form.data('slug'));
						$.event.trigger('wpf_ajax_before_replace');
                        if (in_scroll) {
                            resp = $(resp);
                            products = resp.find('.product');
                            products.addClass('wpf_transient_product');
                            $('.products', $container).first().append(products);
                            var scroll = resp.find('.wpf_infinity a');

                            if (scroll.length > 0) {
                                $('.wpf_infinity a', $container).data({current: scroll.data('current'), max: scroll.data('max')});
                            }

                            $container.removeClass('wpf-infnitiy-scroll');
                            scrollTo = products.first();
                            delete result['append'];
                            setTimeout(function () {
                                in_scroll = false;
                            }, 200);
                        }
                        else {
                            $container.html(resp);
                            InitPagination();
                        }
                        
                        if( isMasonry && $.fn.isotope) {
                                var productsContainer = $( '.products', $container );

                                if( ! productsContainer.find( '.grid-sizer, .gutter-sizer' ).length ) {
                                        productsContainer.prepend('<div class="grid-sizer"></div><div class="gutter-sizer"></div>');
                                }
                                productsContainer.imagesLoaded().always(function(instance ){console.log(instance.elements[0]);
                                        var p = $(instance.elements[0]);
                                        p.addClass('masonry-done');
                                        if(products!==null){
                                                p.isotope( 'destroy' );
                                        }
                                        if(products!==null){
                                                products.addClass('wpf_transient_end_product');
                                        }
                                        if ($form.hasClass('wpf_form_scroll')) {
                                                ToScroll(scrollTo);
                                        }
                                        p.isotope( masonryData.options );

                                });
                        }
                        else if ($form.hasClass('wpf_form_scroll')) {
                            ToScroll(scrollTo);
                            if(products!==null){
                                    products.addClass('wpf_transient_end_product');
                            }
							
                        }
                        history.replaceState({}, null, '?' + decodeURIComponent($.param(result)));
                        if (typeof window.wp.mediaelement != 'undefined') {
                            window.wp.mediaelement.initialize();
                        }
                        $.event.trigger('wpf_ajax_success');
                        triggerEvent(window, 'resize');
                    }
                }
            });
        });
    };

    var ToScroll = function ($container) {
        if ($container.length > 0) {
            $('html,body').animate({
                scrollTop: $container.offset().top - $('#wpadminbar').outerHeight(true) - 10
            }, 1000);
        }
    };

    var infinity = function (e, click) {
        if (!in_scroll && (click || ($(this).scrollTop() + $(this).height() + 100) > $(document).height())) {
            var container = $('.wpf-search-container'),
                    scroll = $('.wpf_infinity a', container),
                    $form = $('.wpf_form_' + container.data('slug'));
            if ($form.length > 0) {
                var current = scroll.data('current');
                if (current <= scroll.data('max')) {
                    $form.find('input[name="wpf_page"]').val(current);
                    in_scroll = true;
                    if (!click) {
                        container.addClass('wpf-infnitiy-scroll');
                    }
                    $form.submit();
                    if (((current + 1) > scroll.data('max'))) {
                        $('.wpf_infinity').remove();
                        if (!click) {
                            $(this).off('scroll', infinity);
                        }
                    }
                }
            }
        }
    };


    var InitPagination = function () {
        function find_page_number(element) {
            var $page = parseInt(element.text());
            if ( ! $page ) {
                $page = parseInt(element.closest('.woocommerce-pagination,.pagenav').find('.current').text());
                if (element.hasClass('next')) {
                    ++$page;
                } else {
                    --$page;
                }
                var pattern = new RegExp( '(?<=paged=)[^\b\s\=]+' );
                if( ! $page && pattern.test( element.attr( 'href' ) ) ) {
                        $page = element.attr( 'href' ).match( pattern )[0];
                }
            }

            return $page;
        }
        if ($('.wpf_infinity_auto').length > 0) {
            $('#load-more').remove();
            $(window).off('scroll', infinity).on('scroll', infinity);
        }
        else if ($('.wpf_infinity').length > 0) {
            $('.wpf_infinity').closest('.wpf-hide-pagination').removeClass('wpf-hide-pagination');
            $('#load-more').remove();
            $('.wpf-search-container').off('click').on('click', '.wpf_infinity a', function (e) {
                e.preventDefault();
                e.stopPropagation();
                infinity(e, 1);
            });
        }
        else {
            $('.wpf-search-container').off('click').on('click', '.wpf-pagination a', function (e) {
                var $slug = $(this).closest('.wpf-search-container').data('slug'),
					$form = $('.wpf_form_' + $slug);
                if ($form.length > 0 && $form.find('input[name="wpf_page"]').length > 0) {
                    e.preventDefault();
                    $form.find('input[name="wpf_page"]').val(find_page_number($(this)));
                    $form.submit();
                }
            });
        }
    };

    var InitAutoComplete = function () {
        var cache = [];
        $('.wpf_autocomplete input').each(function () {
            var $this = $(this),
                    $key = $this.closest('.wpf_item_sku').length > 0 ? 'sku' : 'title',
                    $spinner = $this.next('.wpf-search-wait'),
                    $form = $this.closest('form'),
                    $submit = $form.hasClass('wpf_submit_on_change');
            cache[$key] = [];
            $(this).autocomplete({
                minLength: 0,
                classes: {
                    "ui-autocomplete": "highlight"
                },
                source: function (request, response) {
                    var term = $.trim(request.term);
                    if ($submit && term.length === 0 && request.term.length === 0) {
                        $form.submit();
                    }
                    if (term.length < 1) {
                        return;
                    }
                    request.term = term;
                    term = term.toLowerCase();
                    if (term in cache[$key]) {
                        response(cache[$key][ term ]);
                        return;
                    }
                    $spinner.show();
                    request.key = $key;
                    request.action = 'wpf_autocomplete';
                    $.post(
                            wpf.ajaxurl,
                            request,
                            function (data, status, xhr) {
                                $spinner.hide();
                                cache[$key][ term ] = data;
                                response(data);
                        },'json');

                },
                select: function (event, ui) {
                    $this.val(ui.item.value);
                    if ($submit) {
                        $form.submit();
                    }
                    return false;
                }
            })
            .focus(function () {
                if ($.trim($this.val()).length > 0) {
                    $(this).autocomplete("search");
                }

            })
            .autocomplete("widget").addClass("wpf_ui_autocomplete");
            ;
        });
    };

    var InitOrder = function () {
        function Order(val, obj) {
            var $slug = obj.closest('.wpf-search-container').data('slug'),
                    $form = $('.wpf_form_' + $slug);
            if ($form.length > 0 && $form.find('input[name="orderby"]').length > 0) {
                $form.find('input[name="orderby"]').val(val);
                $form.submit();
            }
        }
        $('.wpf-search-container').delegate('form.woocommerce-ordering', 'submit', function (e) {
            e.preventDefault();
            Order($(this).find('select').val(), $(this));

        }).delegate('select.orderby', 'change', function (e) {
            Order($(this).val(), $(this));
        });
        if (!$('.wpf-search-container').data('slug')) {
            $('.wpf-search-container').data('slug', $('.wpf_form').last().data('slug'));
        }
    };

    var InitChange = function () {
        if ($('.wpf_submit_on_change').length > 0) {
            $('.wpf_submit_on_change').each(function () {
                var $form = $(this);
                $form.find('input[type!="text"],select').change(function (e) {
                    if( $(this).attr("name") == 'price' && $(this).is(":checked") ) {
                        $(".wpf_price_range label").removeClass("active");
						$(this).next("label").addClass("active");
                    }
                    $form.submit();
                });

                $form.find('.wpf_pa_link').click(function (e) {
                    e.preventDefault();
                    $(this).find('input').prop('checked', true).trigger('change');
                });
            });
        }
    };

    var InitTabs = function () {
        var $horizontal = $('.wpf_layout_horizontal');
        if ($horizontal.length > 0) {
            InitTabsWidth($horizontal);
            $horizontal.find('.wpf_item:not(.wpf_item_onsale):not(.wpf_item_instock)').hover(
                    function () {
                        $(this).children('.wpf_items_group').stop().fadeIn();
                    },
                    function () {
                        if ($(this).closest('.wpf-search-submit').length === 0) {
                            var hover = true;
                            $('.wpf_ui_autocomplete').each(function () {
                                if ($(this).is(':hover')) {
                                    hover = false;
                                    return false;
                                }
                            });
                            if (hover) {
                                $(this).children('.wpf_items_group').stop().fadeOut();
                            }
                        }
                    }
            );
            var interval;
            $(window).resize(function (e) {
                if (!e.isTrigger) {
                    clearTimeout(interval);
                    interval = setTimeout(function () {
                        InitTabsWidth($horizontal);
                    }, 500);
                }

            });
        }
    };

    var InitTabsWidth = function ($groups) {
        $groups.each(function () {
            var $items = $(this).find('.wpf_items_group'),
                    $middle = Math.ceil($items.length / 2),
                    last = $items.last().closest('.wpf_item'),
                    max = last.offset().left;
            $items.each(function () {
                var p = $(this).closest('.wpf_item');
                if (max < p.offset().left) {
                    last = p;
                    max = p.offset().left;
                }
            });
            var $firstPos = $items.first().closest('.wpf_item').offset().left - 2,
                    $lastPos = max + last.outerWidth(true);
            last = null;
            max = null;
            $items.each(function (i) {
                var parent_item = $(this).closest('.wpf_item'),
                        left = parent_item.offset().left;
                if (i + 1 >= $middle) {
                    $(this).removeClass('wpf_left_tab').addClass('wpf_right_tab').outerWidth(Math.round(left + parent_item.width() - $firstPos));
                }
                else {
                    $(this).removeClass('wpf_right_tab').addClass('wpf_left_tab').outerWidth(Math.round($lastPos - left));
                }
            });

        });
    };
    
    var initSelect = function(){
       
        function clear(el,selected){
            var text = el.find('[value="'+selected+'"]').text();
            el.next('.select2').find('[title="'+text+'"]').addClass('wpf_disabled');
        }
        $('.wpf_form').find('select').each(function(){
            var el = $(this),
                is_multi = el.prop('multiple'),
                selected =  is_multi?el.data('selected'):false;
            el.select2({
                dir: wpf.rtl ? 'rtl' : 'ltr',
                minimumResultsForSearch: 10,
                dropdownCssClass: 'wpf_selectbox',
                allowClear: !selected && is_multi,
                placeholder:is_multi?'':false
            });

            if(selected && is_multi){
                clear(el,selected);
                el.on('change',function(e){
                    clear(el,selected);
                });
            }
        });
               
             
    };

    $(document).ready(function () {
        if ($('.wpf_form').length > 0) {
            $('body').addClass('woocommerce woocommerce-page');
            InitTabs();
            InitGroupToggle();
            
            if ($.fn.select2) {
                initSelect();
            }
            InitSlider();
            InitPagination();
            InitOrder();
            InitChange();
            InitAutoComplete();
            InitSubmit();
        }
    });


}(jQuery));