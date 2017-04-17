<?php
     
namespace Infobeans\Ordercancel\Block\Order;

class History extends \Magento\Sales\Block\Order\History
{
    /**
     * Function for get Order cancel Url
     */
    public function getOrderCancelUrl()
    {
        return $this->getUrl('ordercancel/order/cancel');
    }
}
