/**
 * This class will be used to manage the universal [footer] cart & allow for
 * additions of SingleProduct[Macro]Lines From Blog Posts To Be Added To The
 * [localStorage.getItem('dic_shopping_cart')]. This array will persist across all pages
 * of the registered domain & allow for checkout on ANY page.
 *
 * - For now checkout will be held on the [^/shop] Endpoint.
 * @todo fetch new cart ID if none exists in the client.
 * @todo allow single product line includes to be added to the cart [.onClick()]
 * @todo allow for updating of the UniversalCart even if a(n) item was added from the shop page on the owners phone or something.
 * @todo [Check Out] button "For Now" will redirect to the main [^/shop] endpoint. **NOTE** Will have modal window included In Future.
 *
 * @todo [localStorage.getItem('dic_shopping_cart')] Needs To Be Refactored To [localStorage.getItem('rs_shopping_cart')]
 * @type {{init}}
 */
var UniversalCart = function($) {
    // var $;

    /**
     * GET URL PARAMETERS
     */
    var $_GET = function(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };

    /**
     * Will Return The Stateless Token In Client
     * @todo if the stateless token does not exist create a new tmp session
     * @return array|null
     * @private
     */
    var _getToken = function() {
        if(localStorage.getItem('dic_stateless_token') === null) {
            var token = {user: {cart: ''}};
            //check for 500 on response and generate cart token & resubmit request
            $.ajax({
                async: false,
                url: 'https://api.readyshop.com/cart/temp?domain='+window.location.hostname,
                success: function(response) {
                    token = JSON.parse(atob(response.data));
                    _setToken(token);
                }
            });
            return token;
        } else {
            return JSON.parse(atob(localStorage.getItem('dic_stateless_token')));
        }
    };

    /**
     * Used To Set The Stateless Token
     * @param object
     * @private
     */
    var _setToken = function(object) {
        var token = btoa(JSON.stringify(object));
        localStorage.setItem('dic_stateless_token', token);
    };

    function json2array(json){
        var result = [];
        var keys = Object.keys(json);
        keys.forEach(function(key){
//            result.push(json[key]);
            result[key] = json[key];
        });
        return result;
    }

    /**
     * Update The Local Storage With The Latest Version Of The Shopping Cart
     * @returns {boolean}
     */
    var updateShoppingCart = function(cart) {
        localStorage.setItem('dic_shopping_cart',JSON.stringify(cart));
        return true;
    };

    /**
     * Fetch(s) The Contents Of The End Users Shopping Cart From Local Storage
     * @returns {Array}
     */
    var fetchShoppingCart = function() {
        var cart = JSON.parse(localStorage.getItem('dic_shopping_cart'));
        if(typeof cart === 'undefined' || cart === null || cart == '') {
            return [];
        } else {
            return cart;
        }
    };

    /**
     *  Explicitly Set The Cart
     */
    var setShoppingCart = function(encodedCart) {
        localStorage.setItem('dic_shopping_cart',JSON.stringify(json2array(JSON.parse(atob(encodedCart)))));
        return true;
    };

    /**
     * Checks Whither The Shopping Cart is Empty Or Not
     * @returns {boolean}
     */
    var isShoppingCartEmpty = function() {
        var cart = fetchShoppingCart();
        var isEmpty = true;

        //iterate through cart object to find a(n) existing product
        cart.forEach(function(element,index,array) {
            if(element !== null) {
                isEmpty = false;
            }
        });

        return isEmpty;
    };

    var _initShoppingCart = function() {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var cart = JSON.parse(this.responseText);
                setShoppingCart(cart.data.cart);
            } else if(this.status == 500) {
                //check for 500 on response and generate cart token & resubmit request
                $.ajax({
                    url: 'https://api.readyshop.com/cart/temp?domain='+window.location.hostname,
                    success: function(response) {
                        var token = _getToken();
                        token['user']['cart'] = response.data.user.cart;
                        _setToken(token);
                    }
                })
            }
        };
        xmlhttp.open("GET", 'https://api.readyshop.com/cart/fetch/client/'+_getToken()['user']['cart'], true);
        xmlhttp.send();
    };

    var _initDependencies = function() {
        /**
         * External JS FILE LOADER
         */
        var scriptLoader = {
            _loadScript: function (url, callback) {
                var head = document.getElementsByTagName('head')[0];
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = url;
                if (callback) {
                    script.onreadystatechange = function () {
                        if (this.readyState == 'loaded') callback();
                    };
                    script.onload = callback;
                }
                head.appendChild(script);
            },
            load: function (items, iteration) {
                if (!iteration) iteration = 0;
                if (items[iteration]) {
                    scriptLoader._loadScript(
                        items[iteration],
                        function () {
                            scriptLoader.load(items, iteration+1);
                        }
                    )
                } else {
                    //$ = window.jQuery;
                    _initCart(); //Internal Constructor --> Universal Function Should Replace This with a "callbackFinal()" argument option
                }
            }
        };

        scriptLoader.load([
            //'//code.jquery.com/jquery-2.2.4.min.js',
            '/wp-content/plugins/ready-shop/plugins/toastr/toastr.min.js'
        ]);
    };

    var _initSubTotalCalculation = function() {
        var items = $('.cart-item');
        var subtotal = 0.00;
        items.each(function(index) {
            var qty = $(this).attr('data-qty');
            var total = $(this).attr('data-price');
            subtotal += (qty * total);
        });
        if(subtotal > 0) {
            $('.rs-uni-cart-subtotal').html(subtotal.toFixed(2));
            $('#check-out-button').html('Check Out');
        } else {
            $('.rs-uni-cart-subtotal').html('0.00');
            $('#check-out-button').html('Go To Store');
        }
    };

    var _removeProducts = function(id) {
        $('.remove-item').click(function() {
            var id = $(this).attr('data-id');
            $.ajax({
                url: 'https://api.readyshop.com/cart/remove/'+_getToken()['user']['cart']+'/'+id,
                success: function(response) {
                    $.ajax({
                        url: 'https://api.readyshop.com/wp/cart/products/'+_getToken()['user']['cart'],
                        success:function(response) {
                            $('#cart-body').html(response);
                            _initSubTotalCalculation();
                            //init shopping cart
                            _initShoppingCart(); //this will allow duplicate submissions of product addition while keeping track properly in the client
                        }
                    });
                }
            });
        });
    };

    var _updateItemQty = function() {
        $('.rs-uni-cart-item-qty').on({
            'change keyup paste': function(e) {
                var productId = $(this).attr('data-id');
                var productQty = $(this).val();
                var cartId = $(this).attr('data-cart-id');
                $.ajax({
                    url: 'https://api.readyshop.com/cart/reserve/'+cartId+'/'+productId+'/'+productQty,
                    success: function(response) {
                        toastr.success('Successfully Updated Your Product Quantity');
                        var cart = fetchShoppingCart();


                        _initSubTotalCalculation();
                    }
                });
            }
        })
    };


    var _productAddedSuccessMessage = function() {

        return '           <div style="color: #3c763d;background-color: #dff0d8; border-color: #d6e9c6; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px;">' +
            '                <a href="javascript:;" onclick="this.parentNode.style.display = \'none\';" style=" text-decoration: none; float: right; font-size: 21px; font-weight: 700; line-height: 1; color: #000; text-shadow: 0 1px 0 #fff; filter: alpha(opacity=20); opacity: .2;" data-dismiss="alert" aria-label="close">&times;</a>' +
            '                <strong>Success!</strong> This product was successfully added to your cart!' +
            '            </div>';
    };

    /**
     * Will Initialize & Handle Cart Operations
     *
     * @todo need to create TEMPORARY cart if none exists in client
     * @todo need to build out cart items if products exist in cart
     * @todo need to allow adding to cart by clicking single product line includes
     * @todo need to allow qty changing when adding to cart by clicking single product line includes
     * @todo need to allow removal from cart by clicking remove button
     * @todo need to make sure cart persists across all accounts logged in across all devices [Inverse Operations] Included.
     *
     * @private
     */
    var _initCart = function() {



        //Get User Client Cart Session ID or Create One **SHOULD ALWAYS RETURN TRUE **
        if(_getToken()['user']['cart'] !== '') {

            //will fetch the client cart from the server and build the local storage for it
            _initShoppingCart();

            //build universal cart footer based on cart ID
            $.ajax({
                url: 'https://api.readyshop.com/wp/cart/products/'+_getToken()['user']['cart'],
                success:function(response) {
                    $('#cart-body').html(response);

                    _updateItemQty();
                    _removeProducts();
                    _initSubTotalCalculation();
                }
            });

            //re-check and build universal cart footer based on cart ID every 5 seconds
            setInterval(function() {
                $.ajax({
                    url: 'https://api.readyshop.com/wp/cart/products/'+_getToken()['user']['cart'],
                    success:function(response) {
                        $('#cart-body').html(response);
                        _updateItemQty();
                        _removeProducts();
                        _initSubTotalCalculation();
                    }
                });
            },5000);
        };



        //navigation controls
        $( document ).ready(function() {

            if($_GET('checkout') === 'true') {
                $('#shopping-cart-trigger').click();
            }

            $('#check-out-button').on({
                'click': function(e) {
                    e.preventDefault();
                    if($('#shopping-cart-trigger').length == 0) {
                        window.location.href = '/shop?checkout=true'; //@todo needs to be dynamic based on generated slug made from wordpress plugin for site
                    } else {
                        $('#shopping-cart-trigger').click();
                    }
                }

            })

            // $("#cart-body, #cart-footer").hide();

            $("#cart-tab").click(function(){
                $("#cart-body, #cart-footer").toggle();
                // $("#show-cart-icon, #hide-cart-icon").toggle();
                // $('#show-cart-icon').css('-webkit-transform','180deg').css('-moz-transform','180deg')
                if($('#show-cart-icon').hasClass('fa-rotate-180')) {
                    $('#show-cart-icon').removeClass('fa-rotate-180');
                } else {
                    $('#show-cart-icon').addClass('fa-rotate-180');
                }
                $("#cart").css("max-height", window.innerHeight+"px");
            });

            //change images $.onClick();
            $('.rs-thumbnail-image').on({
                'click': function(e) {
                    e.preventDefault();
                    var uri = $(this).attr('src');
                    $('#rs-image-display').attr('src',uri);
                    $('.rs-thumbnail').removeClass('rs-selected');
                    $(this).parent().addClass('rs-selected');
                }
            });

            $(window).resize(function() {
                $("#cart").css("max-height", window.innerHeight+"px");
            });

            $(".open-modal-product").click(function(){
                var id =  $(this).attr('data-id');
                $("#rs-modal-bg-"+id).show();
                $("#rs-product-modal-"+id).show();
                $("body").addClass("rs-modal-open");
            });

            $(".rs-modal-close").click(function(){
                var id = $(this).attr('data-id');
                $("#rs-modal-bg-"+id).hide();
                $("#rs-product-modal-"+id).hide();
                $("body").removeClass("rs-modal-open");
            });

            $(".rs-modal-bg").click(function(){
                var id = $(this).attr('data-id');
                $("#rs-modal-bg-"+id).hide();
                // $("#rs-product-modal").hide();
                $('.rs-product-modal-reference').hide();
                $("body").removeClass("rs-modal-open");
            });

            $(".add-to-cart").click(function(){
                //todo trigger server addition of product via $.POST
                //todo trigger fetching of cart body via $.GET
                //todo trigger (n) alert on cart window if closed to show addition of product (optional)

                var id = $(this).attr('data-id'); //product ID
                var inputQty = 0;
                var newQty = 0;


                //get current product QTY
                if(fetchShoppingCart()[id] !== undefined && fetchShoppingCart()[id] !== null) {
                    var productQTY = fetchShoppingCart()[id]['qty'];
                    //add product QTY amount from inside input field -- defaults to 1

                    if($(this).hasClass('modal-add-to-cart-button')) {
                        inputQty = parseInt($(this).parent().find('input').val());
                    } else {
                        inputQty = 1;
                    }
                    newQty = productQTY + inputQty;
                } else {
                    if($(this).hasClass('modal-add-to-cart-button')) {
                        inputQty = parseInt($(this).parent().find('input').val());
                    } else {
                        inputQty = 1;
                    }
                    newQty = inputQty;
                }

                var isInsideModalWindow = $(this).hasClass('modal-add-to-cart-button');



                $.ajax({
                    url: 'https://api.readyshop.com/cart/reserve/'+_getToken()['user']['cart']+'/'+id+'/'+newQty,
                    success: function(response) {
                        $.ajax({
                            url: 'https://api.readyshop.com/wp/cart/products/'+_getToken()['user']['cart'],
                            success:function(response) {
                                $('#cart-body').html(response);

                                _updateItemQty();
                                _removeProducts();
                                _initSubTotalCalculation();

                                //display success message
                                $('.product-added-to-cart-success-'+id).append(_productAddedSuccessMessage());

                                //init shopping cart
                                _initShoppingCart(); //this will allow duplicate submissions of product addition while keeping track properly in the client
                                if(isInsideModalWindow !== true) {
                                    toastr.success('You Have Successfully Added The Item To Your Cart!');
                                    $('#check-out-button').html('Check Out'); //make sure cart text now refers to checkout instead of store
                                }
                            },
                            error: function(xhr, response) {
                                toastr.error('Could Not Add The Product To Your Cart.');
                            }
                        });
                    }
                });
            });

        });
    };

    var _initCheckoutWindow = function() {
        //some code here
    };

    /**
     *
     * @constructor
     */
    var Main = function() {
        _initDependencies();
        _initCheckoutWindow();
    };


    return {
        init: function() {
            Main();
        }
    }
}(jQuery);
UniversalCart.init();