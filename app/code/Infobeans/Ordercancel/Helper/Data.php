<?php

namespace Infobeans\Ordercancel\Helper;
 
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
     
    public function canCancel($order)
    {
        
        
        
        
        if ($order->canCancel()) {
            return true; 
        }
         
        if($order->getStatus()=="processing")
        {
            return true;
        }
        return false;
    }
    
    
    
}
