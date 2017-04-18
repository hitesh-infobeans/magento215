<?php

namespace Infobeans\Ordercancel\Helper;
 
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $scopeConfig;
    
    protected $storeManager;
    
    protected $_transportBuilder;
    
    protected $inlineTranslation;
    
    const STATUS_CANCEL_REQUEST = 'cancel_request';
    
    const XML_PATH_ENABLE_MODULE = 'ordercancel_section/general/enable_module';
    
    const XML_PATH_ENABLE_COMMENT = 'ordercancel_section/general/enable_comment';
        
    const XML_PATH_ADMIN_EMAIL = 'ordercancel_section/general/email_to';
    
    const XML_PATH_PENDING_ORDER_MESSAGE = 'ordercancel_section/general/ordercancel_message_pendingorder';
    
    const XML_PATH_PAID_ORDER_MESSAGE = 'ordercancel_section/general/message_paidorder';
    
    const XML_PATH_EMAIL_IDENTITY = 'ordercancel_section/general/identity';
    
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
    
    /**
     * Check if Module is enabled or not
     */
    public function isModuleEnable()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_MODULE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Check if Comment is enabled or not
     */
    public function isCommentEnable()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_COMMENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Retrieve sender email
     */
    public function getSenderEmail()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SENDER_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Retrieve sender name
     */
    public function getSenderName()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SENDER_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Retrieve Admin email
     */
    public function getAdminEmail()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Retrieve Order message after cancel for pending order
     */
    public function getPendingOrderMessage()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PENDING_ORDER_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Retrieve Order message after cancel for paid order
     */
    public function getPaidOrderMessage()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAID_ORDER_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getEmailTemplateConfig($emailTemplate)
    {
        $configPath='ordercancel_section/general/'.$emailTemplate;
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * check if Order can cancel
     */
    public function canCancel($order)
    {
        if (!$this->isModuleEnable()) {
            return false;
        }
        
        if ($order->canCancel()) {
            return "Cancel";
        }
         
        if ($order->getState()==\Magento\Sales\Model\Order::STATE_PROCESSING && $order->getStatus()!=self::STATUS_CANCEL_REQUEST) {
            return "Cancel Request";
        }
        return false;
    }
    
    /**
     * Return email identity
     *
     * @return mixed
     */
    public function getEmailIdentity()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_IDENTITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Send Order cancel email to customer
     */
    public function sendOrderCancelMailToCustomer($order, $emailTemplate)
    {
        $templateOptions =  [
                               'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                               'store' => $this->storeManager->getStore()->getId()
                            ];
        $templateVars = [
                            'store' => $this->storeManager->getStore(),
                            'order' => $order,
                        ];
       
        $template = $this->getEmailTemplateConfig($emailTemplate);
        $this->inlineTranslation->suspend();
        $to = [$order->getCustomerEmail()];
        $transport = $this->_transportBuilder->setTemplateIdentifier($template)
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($this->getEmailIdentity())
                        ->addTo($to)
                        ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
    
    /**
     * Send Order cancel email notification to admin
     */
    public function sendOrderCancelMailToAdmin($order, $emailTemplate)
    {
        $templateOptions =  [
                              'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                              'store' => $this->storeManager->getStore()->getId()
                            ];
        $templateVars = [
                            'store' => $this->storeManager->getStore(),
                            'order'=>$order,
                        ];
                   
        $this->inlineTranslation->suspend();
        $to = [$this->getAdminEmail()];
        $transport = $this->_transportBuilder->setTemplateIdentifier($emailTemplate)
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($this->getEmailIdentity())
                        ->addTo($to)
                        ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
}
