<?php
     
namespace Infobeans\Ordercancel\Block\Order;

class Recent extends \Magento\Sales\Block\Order\Recent
{
    /**
     * Function for get Order cancel Url
     */
    public function getOrderCancelUrl()
    {
        return $this->getUrl('ordercancel/order/cancel');
    }
}
