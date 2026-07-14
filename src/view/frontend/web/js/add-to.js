define([
    'jquery'
], function ($) {
    return function (configData) {
        const options = {
            minicartSelector: '[data-block="minicart"]',
            tweakwiseProductId: '',
            productId: ''
        };

        window['twn-starter-config'].on['twn.add-to-cart'] = function (event) {
            if (shouldMoveMessages()) {
                moveMessages();
            }
            setProductData(event.data.itemno);
            addToCart();
        };

        window['twn-starter-config'].on['twn.add-to-favorites'] = function (event) {
            if (shouldMoveMessages()) {
                moveMessages();
            }
            setProductData(event.data.itemno);
            addToWishlist();
        };

        /**
         * Function to add product to the cart
         */
        function addToCart() {
            $(options.minicartSelector).trigger('contentLoading');
            addTo(getAddToCartUrl());
        }

        /**
         * Function to add product to the wishlist
         */
        function addToWishlist() {
            createWishlistForm().submit();
        }

        /**
         * @param {string} url
         */
        function addTo(url) {
            $.ajax({
                url: url,
                data: getFormData(),
                type: 'post',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,

                success: function (res) {
                    if (res.backUrl) {
                        window.location.href = res.backUrl;
                        return;
                    }
                },

                complete: function (res) {
                    if (res.state() === 'rejected') {
                        location.reload();
                    }
                }
            });
        }

        /**
         * If maincontent DIV is not displayed, the messages should be moved out of this DIV
         * @returns {Boolean}
         */
        function shouldMoveMessages() {
            return !$('#maincontent').is(':visible');
        }

        /**
         * @returns {void}
         */
        function moveMessages() {
            const $messages = $('#maincontent .page.messages');
            if (!$messages) {
                return;
            }

            $messages.insertBefore('#twn-starter-overlay');
        }

        /**
         * @param {string} tweakwiseProductId
         * @returns {string}
         */
        function getProductId(tweakwiseProductId) {
            return tweakwiseProductId.replace('1' + configData.storeId.padStart(4, 0), '');
        }

        /**
         * @returns {FormData}
         */
        function getFormData() {
            const formData = new FormData();
            formData.append('product', options.productId);
            formData.append('form_key', configData.formKey);
            formData.append('uenc', btoa(window.location.href));
            formData.append('tweakwise_event_handled', '1');

            return formData;
        }

        /**
         * @returns {jQuery}
         */
        function createWishlistForm() {
            const $form = $('<form>', {
                action: getAddToWishlistUrl(),
                method: 'POST'
            });

            const data = {
                product: options.productId,
                form_key: configData.formKey,
                uenc: btoa(window.location.href),
                tweakwise_event_handled: '1'
            };

            $.each(data, function (key, value) {
                $('<input>').attr({
                    type: 'hidden',
                    name: key,
                    value: value
                }).appendTo($form);
            });

            $form.appendTo('body');

            return $form;
        }

        /**
         * @returns {string}
         */
        function getAddToCartUrl() {
            return `${BASE_URL}checkout/cart/add/uenc/${btoa(window.location.href)}/product/${options.productId}/`;
        }

        /**
         * @returns {string}
         */
        function getAddToWishlistUrl() {
            return `${BASE_URL}wishlist/index/add/`;
        }

        /**
         * @param {string} tweakwiseProductId
         */
        function setProductData(tweakwiseProductId) {
            options.tweakwiseProductId = tweakwiseProductId;
            options.productId = getProductId(tweakwiseProductId);
        }
    }
});
