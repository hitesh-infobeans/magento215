<?php

namespace Infobeans\Ordercancel\Helper;
 
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $scopeConfig;
    
    protected $storeManager;
    
    protected $_transportBuilder;
    
    protected $inlineTranslation;
    
    const XML_PATH_ENABLE_MODULE = 'ordercancel_section/general/enable_module';
    
    const XML_PATH_ENABLE_COMMENT = 'ordercancel_section/general/enable_comment';
    
    const XML_PATH_SENDER_EMAIL = 'ordercancel_section/general/email_sender';
    
    const XML_PATH_SENDER_NAME = 'ordercancel_section/general/email_sender_name';
    
    const XML_PATH_ADMIN_EMAIL = 'ordercancel_section/general/email_to';
    
    const XML_PATH_PENDING_ORDER_MESSAGE = 'ordercancel_section/general/ordercancel_message_pendingorder';
    
    const XML_PATH_PAID_ORDER_MESSAGE = 'ordercancel_section/general/ordercancel_message_paidorder';
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation   
            
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        $this->_transportBuilder=$transportBuilder;
        $this->inlineTranslation=$inlineTranslation;
        
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
    
    public function getAdminEmail()
    {     
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getPendingOrderMessage()
    {     
        return $this->scopeConfig->getValue(
            self::XML_PATH_PENDING_ORDER_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getPaidOrderMessage()
    {     
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAID_ORDER_MESSAGE,
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
    public function sendOrderCancelMailToCustomer($order,$emailTemplate)
    {
      
    
           $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
           $templateVars = array(
                               'store' => $this->storeManager->getStore(),
                               'order' => $order,                               
                            );
           
           $from = array('email' => $this->getSenderEmail(), 'name' => $this->getSenderName());
           $this->inlineTranslation->suspend();
           $to = array($order->getCustomerEmail());
           $transport = $this->_transportBuilder->setTemplateIdentifier($emailTemplate)
                           ->setTemplateOptions($templateOptions)
                           ->setTemplateVars($templateVars)
                           ->setFrom($from)
                           ->addTo($to)
                           ->getTransport();
           $transport->sendMessage();
           $this->inlineTranslation->resume();
        
    }
    
    
    // Send Email to Customer
    public function sendOrderCancelMailToAdmin($order,$emailTemplate)
    {
           $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_ADMINHTML, 'store' => $this->storeManager->getStore()->getId());
           $templateVars = array(
                               'store' => $this->storeManager->getStore(),
                               'order'=>$order,                               
                            );
           
           $from = array('email' => $this->getSenderEmail(), 'name' => $this->getSenderName());
           $this->inlineTranslation->suspend();
           $to = array($this->getAdminEmail());
           $transport = $this->_transportBuilder->setTemplateIdentifier($emailTemplate)
                           ->setTemplateOptions($templateOptions)
                           ->setTemplateVars($templateVars)
                           ->setFrom($from)
                           ->addTo($to)
                           ->getTransport();
           $transport->sendMessage();
           $this->inlineTranslation->resume();
        
    }
    
    
    
    
    
    
}
