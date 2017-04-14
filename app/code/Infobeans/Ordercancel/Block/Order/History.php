<?php
     
namespace Infobeans\Ordercancel\Block\Order;

class History extends \Magento\Sales\Block\Order\History
{
    public function getOrderCancelUrl()
    {
        return $this->getUrl('ordercancel/order/cancel');
    }
}

