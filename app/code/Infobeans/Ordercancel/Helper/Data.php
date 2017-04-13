<?php

namespace Infobeans\Ordercancel\Helper;
 
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $scopeConfig;
    
    protected $storeManager;
    
    protected $_transportBuilder;
    
    const XML_PATH_ENABLE_MODULE = 'ordercancel_section/general/enable_module';
    
    const XML_PATH_ENABLE_COMMENT = 'ordercancel_section/general/enable_comment';
    
    const XML_PATH_SENDER_EMAIL = 'ordercancel_section/general/email_sender';
    
    const XML_PATH_SENDER_NAME = 'ordercancel_section/general/email_sender_name';
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder    
            
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        $this->_transportBuilder=$transportBuilder;
    }
    
    
    public function isModuleEnable()
    {     
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_MODULE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function isCommentEnable()
    {     
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_COMMENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    
    public function getSenderEmail()
    {     
        return $this->scopeConfig->getValue(
            self::XML_PATH_SENDER_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getSenderName()
    {     
        return $this->scopeConfig->getValue(
            self::XML_PATH_SENDER_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    
    public function canCancel($order)
    {
               
        if(!$this->isModuleEnable())
        {
            return false;
        } 
        
        if ($order->canCancel()) {
            return true; 
        }
         
        if($order->getStatus()=="processing")
        {
            return true;
        }
        return false;
    }
    
    // Send Email to Customer
    public function sendOrderCancelMailToCustomer($customerEmail,$customerName,$message)
    {
      
    
           $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
           $templateVars = array(
                               'store' => $this->storeManager->getStore(),
                               'customer_name' => $customerName,
                               'message'   => $message
                            );
           
           print_r($templateVars);exit;
           
           $from = array('email' => $this->getSenderEmail(), 'name' => $this->getSenderName());
           $this->inlineTranslation->suspend();
           $to = array($customerEmail);
           $transport = $this->_transportBuilder->setTemplateIdentifier('ordercancel_template')
                           ->setTemplateOptions($templateOptions)
                           ->setTemplateVars($templateVars)
                           ->setFrom($from)
                           ->addTo($to)
                           ->getTransport();
           $transport->sendMessage();
           $this->inlineTranslation->resume();
        
    }
    
    
    
    
    
    
}
