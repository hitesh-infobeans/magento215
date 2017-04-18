define(
    ['jquery', 'uiComponent', 'ko', 'Magento_Ui/js/modal/modal','Magento_Ui/js/modal/confirm'],
    function ($, Component, ko,modal,confirmation) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Infobeans_Ordercancel/reasonpopup'
            },
            initialize: function () {
                this.orderId = ko.observable('');
                this.orderCancelUrl = ko.observable(window.orderCancelUrl);
                this._super();
            },
            showPopup: function (orderid) {
               
               var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Reason',
                buttons: [{
                    text: $.mage.__('Submit'),
                    class: '',
                    click: function () {
                        
                        if ($('#frmcancelorder').validation() &&
                            $('#frmcancelorder').validation('isValid')
                        ) {
                            $('#frmcancelorder').submit();
                        }
                    }
                }]
            };
            
            this.orderId(orderid);
            
            confirmation({
                title: 'Confirmation',
                content: 'Are you sure you want to cancel the order',
                actions: {
                    confirm: function () {
                        if (window.isCommentEnable) {
                            var popup = modal(options, $('#popup-modal'));
                            $('#popup-modal').modal('openModal');
                        } else {
                            $('#frmcancelorder').submit();
                        }
                    },
                    cancel: function () {},
                }
            });
             
               
        }
            
        });
    }
);