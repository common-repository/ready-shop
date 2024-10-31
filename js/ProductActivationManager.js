/**
 * @author Daniel P. Baddeley JR <dan@bluewatermedia.tv>
 * @date 9/15/17.
 */
var ProductActivationManager = function($){
    var processing;
    var BrandSelectionDropdown;

    /**
     * This will allow Users To Fuzzy Search Through Products Based On Title & Description & Brand
     * @todo allow searching via title
     * @todo allow searching via description
     * @todo allow searching via brand
     *
     * --- Build Search Algorithm To Focus on Exact Spelling *possibly* number of occurrences of a string
     * --- add priority to order of keywords
     *
     * ---> might be faster to send the request to the server instead of trying to find it inside the client...
     * especially since NEW products will most likely need to be loaded instead of just displaying a shorter list
     * of results for the currently available.
     * @todo Build Server-Side Endpoint To Return Products That Match The Keywords Entered
     * @private
     */
    var _initFuzzySearch = function() {
        var productListingsSearch = $('#product-listings-search');
        var timer;

        //use existing characters to find matching content
        productListingsSearch.on('keyup', function() {
            var searchString = $(this).val();


            clearInterval(timer);  //clear any interval on key up
            timer = setTimeout(function() { //then give it a second to see if the user is finished

                if(searchString !== '' || searchString === '') { //redundant but additional conditions can be implemented --- The Addition of the "" <-- Was added Due To Erasing The String And Allowing Full Range Of Results To Be Re-Populated.
                    //sanitize search string
                    searchString = searchString.replace(/[^a-zA-Z ]/g, "");
                    var keywords = searchString.split(' ');
                    var productListings = $('.product-listing');


                    /**
                     * Make Ajax Request To The Server With The Search Parameters & Have It Return The New Result Set To The Client
                     */
                    var brand = localStorage.getItem('rs_product_brand_filter');
                    var category = localStorage.getItem('rs_product_category_filter');
                    localStorage.setItem('rs_scroll_offset',0);
                    _initDefaultLoadingAnimation('override');
                    _initProductFeed(parseInt(localStorage.getItem('rs_scroll_offset')),100,brand,category,'filter',searchString);



                    /**
                     * Below Code Used For Client Side Searching - This May Be Useful In The Future
                     * @deprecated
                     * @date 8/17/18
                     */

                    // //iterate through product listings & get total number of occurrences for keywords & sum for total | products to be sorted in [Descending Order of Most Occurrences]
                    // productListings.each(function() {
                    //     var brand = $(this).attr('data-brand');
                    //     var title = $(this).attr('data-title');
                    //     var description = $(this).attr('data-description');
                    //     var exactSearchStringMatch = false;
                    //     var count = 0, exactCount = 0;
                    //
                    //     var regEx = new RegExp(searchString,'igm');
                    //
                    //     exactCount += (brand.match(regEx) || []).length;
                    //     exactCount += (title.match(regEx) || []).length;
                    //     exactCount += (description.match(regEx) || []).length;
                    //
                    //     if(exactCount > 0) {
                    //         exactSearchStringMatch = true;
                    //     }
                    //
                    //
                    //     for(var i = 0; i < keywords.length; i++) {
                    //         regEx = new RegExp(keywords[i],'igm');
                    //         count += (brand.match(regEx) || []).length;
                    //         count += (title.match(regEx) || []).length;
                    //         count += (description.match(regEx) || []).length;
                    //     }
                    //
                    //     if(exactSearchStringMatch) {
                    //         count += 1000000;
                    //     }
                    //
                    //
                    //     $(this).attr('data-search-result-value',count);
                    //
                    //     SortElements.sort('#product-listing-container','#product-listing-container .product-listing','data-search-result-value','desc');
                    // });

                }
            }, 500);
        });
    };

    var _initProductActivationButton = function() {
        $('.product-activation-button').on({
            'click': function(e) {
                e.preventDefault();
                var id = $(this).attr('data-id'), option = '';

                if($(this).hasClass('btn-default')) {
                    option = 'add';
                    $(this).html('Remove');
                    $(this).addClass('btn-danger').removeClass('btn-default');
                    //$('#collapse-trigger-'+id).click();
                    $('div[data-product-id="'+id+'"]').removeClass('.removed-product').addClass('.added-product');
                } else {
                    option = 'remove';
                    $(this).html('Add To Store');
                    $(this).addClass('btn-default').removeClass('btn-danger');
                    $('div[data-product-id="'+id+'"]').removeClass('.added-product').addClass('.removed-product');
                }

                var data = {
                    'action': 'readyshop_product_activation_management',
                    'productId': id,     // We pass php values differently!
                    'option': option
                };


                $.ajax({
                    url: ajaxurl,
                    data: data,
                    method: 'POST',
                    success: function(response) {
                        console.log(response);
                        console.log(id);
                        toastr.success('Successfully Updated Your Product Listings!');

                    }, error: function() {
                        toastr.error('Product Was Not Able To Be Modified To Your Store.');
                    }
                });

            }
        });
    };

    var _initMenuNavigation = function() {
        localStorage.setItem('rs_product_brand_filter','all'); //reset brand filter
        localStorage.setItem('rs_product_category_filter','all'); //reset brand filter

        $('.dropdown-item').on({
            'click': function(e) {
                e.preventDefault();
                console.log('Item Clicked');
                var selector = $(this).attr('data-selection');
                var productListings = $('.product-listing');

                console.log($(this).attr('title'));
                console.log('Brand: '+$(this).attr('data-brand'));

                if($(this).attr('data-category') === 'true') {
                    $('.product-selection-dropdown').html($(this).attr('title'));
                    localStorage.setItem('rs_product_category_filter',selector);
                } else if($(this).attr('data-brand') === 'true') {
                    console.log('Brand Selected');
                    $('.brand-selection-dropdown').html($(this).attr('title'));
                    localStorage.setItem('rs_product_brand_filter',selector);
                    console.log('Brand Choosen: ' + $(this).attr('title'));
                }





                //server-side content filtering below client side check is just there for brevity
                var brand = localStorage.getItem('rs_product_brand_filter');
                var category = localStorage.getItem('rs_product_category_filter');
                var search = $('#product-listings-search').val();
                localStorage.setItem('rs_scroll_offset',0);
                _initProductFeed(parseInt(localStorage.getItem('rs_scroll_offset')),100,brand,category,'none',search);










                //todo create better category & filtering for product navigation
                //todo Allow Filter Adjustments
                //todo allow filter by brand
                //todo allow filter by category
                //allow category adjustment to coincide with filter adjustment
                //allow filter adjustment to coincide with category adjustment
                //allow brand adjustment to work with filter adjustment -> re-display categories based on brand | on brand selection change reset category filter to [all]


                if (selector === '.decending-commission') {
                    SortElements.sort('#product-listing-container', '#product-listing-container .product-listing', 'data-commission', 'desc');
                } else if(selector === '.ascending-comission') {
                    SortElements.sort('#product-listing-container', '#product-listing-container .product-listing', 'data-commission', 'asc');
                } else {
                    var options = selector.split(' ');
                    productListings.each(function() {








                        if(selector !== 'all') {
                            if(options.length > 1) {
                                if($(this).hasClass(options[0]) || $(this).hasClass(options[1])) {
                                    $(this).show();
                                } else {
                                    $(this).hide();
                                }
                            } else {
                                if($(this).hasClass(selector)) {
                                    $(this).show();
                                } else {
                                    $(this).hide();
                                }
                            }

                        } else if(selector === 'all') {
                            $(this).show();
                        }
                    });
                }




            }
        })
    };

    /**
     * @todo Add Checks To See If Default Product || Chain Hav
     * @private
     */
    var _initDefaultColorOption = function() {
        $('.product-default-image-select').on('change',function(e) {
            //add lazy loader or block UI on current element

            e.preventDefault();
            var value = $(this).val();
            var defaultId = $(this).attr('data-default-id');

            //change the viewable shortcode to newly selected color option ID
            $('#product-id-' + defaultId).html(value);

            //change product activation option
            $('#product-activation-default-' + defaultId).attr('data-id',value);

            //ajax request to fetch new product information
            $.ajax({
                url: 'https://api.readyshop.com/v1.0/products/content/'+value+'/json',
                success: function(response) {
                    var newImage = 'https://api.readyshop.com/assets/img/products/ ' + response.localGallery[0];
                    $('#product-default-image-'+defaultId).attr('src',newImage);
                }
            })

        })
    };


    /**
     * This will be a server side call to Add/Remove All products Based on The [Brand|Category|{SearchString}] Criteria That has been selected.
     * @todo attempt to utilize the [getProductsByFilters] Function & Use The Result Set Returned To Add/Remove Products to The Store
     * @todo Look Into Performing JUST a "UPDATE" query using the same "SELECT" operation to prevent having multiple transactions against the database and faster response time to the client.
     * @see ProductRepository:getProductsByFilters()
     * @private
     */
    var _initAddAllDisplayedProductsButton = function() {
        $('.product-selection-add-all-displayed').on({
            'click': function(e) {
                e.preventDefault();
                var productArray = [], i = 0;

                $('.product-listing').each(function(index) {
                    if($(this).css('display') != 'none' && $(this).hasClass('.removed-product')) {
                        //@todo gather array of product ID's To Send In A Batch To Endpoint
                        // $('#product-activation-default-'+$(this).attr('data-product-id')).click();
                        productArray[i] = $(this).attr('data-product-id');
                        i++;


                        $('#product-activation-default-'+$(this).attr('data-product-id')).html('Remove');
                        $('#product-activation-default-'+$(this).attr('data-product-id')).addClass('btn-danger').removeClass('btn-default');
                        $('div[data-product-id="'+$(this).attr('data-product-id')+'"]').removeClass('.removed-product').addClass('.added-product');
                    }
                });
                localStorage.setItem('batch_group_product_list',JSON.stringify(productArray));


                var data = {
                    'action': 'readyshop_batch_product_activation_management',
                    'option': 'add',
                    'productArray': productArray     // We pass php values differently!
                };



                $.ajax({
                    url: ajaxurl,
                    data: data,
                    method: 'POST',
                    success: function(response) {
                        toastr.success('Successfully Added All Visible Product Listings!');

                    }, error: function() {
                        toastr.error('Product Was Not Able To Be Modified To Your Store.');
                    }
                });
            }
        })
    };

    var _initRemoveAllDisplayedProductsButton = function() {
        $('.product-selection-remove-all-displayed').on({
            'click': function(e) {
                e.preventDefault();
                var productArray = [];

                $('.product-listing').each(function(index) {
                    if($(this).css('display') != 'none' && $(this).hasClass('.added-product')) {
                        //@todo gather array of product ID's To Send In A Batch To Endpoint
                        // $('#product-activation-default-'+$(this).attr('data-product-id')).click();
                        productArray.push($(this).attr('data-product-id'));

                        $('#product-activation-default-'+$(this).attr('data-product-id')).html('Add To Store');
                        $('#product-activation-default-'+$(this).attr('data-product-id')).addClass('btn-default').removeClass('btn-danger');
                        $('div[data-product-id="'+$(this).attr('data-product-id')+'"]').removeClass('.added-product').addClass('.removed-product');
                    }
                });

                localStorage.setItem('batch_group_product_list',JSON.stringify(productArray));


                var data = {
                    'action': 'readyshop_batch_product_activation_management',
                    'option': 'remove',
                    'productArray': productArray     // We pass php values differently!
                };


                $.ajax({
                    url: ajaxurl,
                    data: data,
                    method: 'POST',
                    success: function(response) {
                        toastr.success('Successfully Removed All Visible Product Listings!');

                    }, error: function() {
                        toastr.error('Product Was Not Able To Be Modified To Your Store.');
                    }
                });

            }
        })
    };

    /**
     * This endpoint fetches the list of products to be displayed on the client
     * @param offset
     * @param limit
     * @param brand
     * @param category
     * @param filter
     * @param search
     * @private
     */
    var _initProductFeed = function(offset, limit, brand, category, filter, search){
        //initialize scroll event
        $(window).scroll(function() {
            _detectScrollPosition();
        }); //this is so that when actions are performed to generate a new list of results, scrolling can be enabled again, if previously disabled

        //set defaults
        //@todo may change this to just check the values of the drop-downs directly && check the offset from a localStorage Value
        offset = typeof offset == 'undefined' ? 0 : offset;
        limit = typeof limit == 'undefined' ? 100 : limit;
        brand = typeof brand == 'undefined' ? 'all' : brand;
        category = typeof category == 'undefined' ? 'all' : category;
        filter = typeof filter =='undefined'? 'none' : filter;
        search = typeof search =='undefined' || search === '' ? 'no-search-string' : search;


        //fetch the list of products
        $.ajax({
            url: 'https://api.readyshop.com/wp/manage/products/'+localStorage.getItem('application_token')+'/'+offset+'/'+limit+'/'+brand+'/'+category+'/'+filter+'/'+search,
            success: function(response) {
                $('.default-product-loading-animation').hide();
                if(offset === 0) {
                    $('#product-listing-container').html(response);
                } else if(offset >= 100) {
                    $('#product-listing-container').append(response);
                }
                _initProductActivationButton();
                processing = false;
                localStorage.setItem('rs_scroll_offset', offset + 100);
                _initDefaultLoadingAnimation('append');
            },
            error: function(xhr) {
                console.log('This was a 404 Error');
                $(window).unbind('scroll');
                $('.default-product-loading-animation').html('<img src="'+localStorage.getItem('rs_404_animation')+'" style="margin-left: -25%;"/>');
            }
        });
    };

    var _initDefaultLoadingAnimation = function(option) {
        var loadingAnimation = '<br><div class="default-product-loading-animation" style="margin-left: 40%;"><img src="'+localStorage.getItem('rs_loading_animation')+'"/><p>Loading List Of Products, Please Wait...</p></div>';
        if(option === 'override') {
            $('#product-listing-container').html(loadingAnimation);
        } else if(option === 'append') {
            $('#product-listing-container').append(loadingAnimation);
        }
    };

    var _detectScrollPosition = function() {
        if (processing)
            return false;

        if ($(window).scrollTop() >= ($(document).height() - $(window).height())*0.9){
            processing = true; //sets a processing AJAX request flag

            //server-side content filtering below client side check is just there for brevity
            var brand = localStorage.getItem('rs_product_brand_filter');
            var category = localStorage.getItem('rs_product_category_filter');
            var search = $('#product-listings-search').val();

            _initProductFeed(parseInt(localStorage.getItem('rs_scroll_offset')),100,brand,category,'none',search);
        }
    };


    /**
     *
     * @constructor
     */
    var Main = function (){
        _initMenuNavigation();
        _initFuzzySearch();
        // _initDefaultColorOption();
        _initAddAllDisplayedProductsButton();
        _initRemoveAllDisplayedProductsButton();

        //initialize the product feed
        _initProductFeed();

        //initialize scroll event
        $(window).scroll(function() {
            _detectScrollPosition();
        });
    };

    return {
        init: function(  ){
            Main();
        },
        initProductActivation: function() {
            _initProductActivationButton();
        },
        initProductFeed: function() {
            _initProductFeed();
        },
        initLoadingAnimation: function(option) {
            _initDefaultLoadingAnimation(option);
        }
    }
}(jQuery);

ProductActivationManager.init();