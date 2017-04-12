define(['jquery', 'uiComponent', 'ko','Magento_Ui/js/modal/modal'], function ($, Component, ko,modal) {
        'use strict';
        return Component.extend({
            /*defaults: {
                template: 'Webkul_Knockout/knockout-test'
            },*/
            initialize: function () {
                this.orderId = ko.observable('');
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
            
            var popup = modal(options, $('#popup-modal'));

            $('#popup-modal').modal('openModal');
            
             this.orderId(orderid);
               
            }
            
            
        });
    }
);