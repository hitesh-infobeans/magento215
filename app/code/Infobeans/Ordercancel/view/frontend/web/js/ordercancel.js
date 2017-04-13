define(['jquery', 'uiComponent', 'ko','Magento_Ui/js/modal/modal'], function ($, Component, ko,modal) {
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
                        $('#frmcancelorder').submit();
                    }
                }] 
            }; 
            
            this.orderId(orderid);
             
            
            if(window.isCommentEnable)
            {
                var popup = modal(options, $('#popup-modal'));

                $('#popup-modal').modal('openModal');
            }
            else
            {
                $('#frmcancelorder').submit();
            }
            
               
            } 
            
        });
    }
);