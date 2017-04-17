<?php

namespace Infobeans\Ordercancel\Controller\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
 
class Cancel extends \Magento\Framework\App\Action\Action
{
    const STATUS_CANCEL_REQUEST = 'cancel_request';
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManagement;
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;
    
    /**
     * @var \Infobeans\Ordercancel\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Escaper $escaper,
        \Infobeans\Ordercancel\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository=$orderRepository;
        $this->orderManagement=$orderManagement;
        $this->escaper = $escaper;
        $this->helper = $helper;
        $this->logger = $logger;
        parent::__construct($context);
    }
    
    /**
     *
     * @return Order Object
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getPost('order_id');
         
        try {
            $order = $this->orderRepository->get($id);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        return $order;
    }
    
    protected function getRedirectUrl()
    {
        if (strpos($this->_redirect->getRefererUrl(), "sales/guest/view") !== false) {
            $redirectUrl = str_replace("view", "form", $this->_redirect->getRefererUrl());
        } else {
            $redirectUrl = $this->_redirect->getRefererUrl();
        }
        return $redirectUrl;
    }
    
    protected function validateForm($post)
    {
        if (!\Zend_Validate::is(trim($post['order_id']), 'NotEmpty')) {            
            return false;
        }
        
        if ($this->helper->isCommentEnable() && !\Zend_Validate::is(trim($post['reason']), 'NotEmpty')) {
            return false;
        }
        return true;
    }
    
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
       
        $redirectUrl=$this->getRedirectUrl();
        
        $resultRedirect = $this->resultRedirectFactory->create();
         
        if (!$this->helper->isModuleEnable()) {
            return $resultRedirect->setPath($redirectUrl);
        }
        
        $canceled=false;
        
        if ($post) {
            try {
                $order = $this->_initOrder();
                
                if (!$this->validateForm($post)) {                     
                    throw new \Magento\Framework\Exception\LocalizedException();
                }
                
                $reason = $this->escaper->escapeHtml(trim($post['reason']));
                
                if ($order->canCancel()) {
                    $this->orderManagement->cancel($order->getEntityId());
                    $canceled=true;
                    $frontendEmailTemplate = "ordercancel_template";
                    $adminEmailTemplate = "admin_ordercancel_template";
                    $message=$this->helper->getPendingOrderMessage();
                } elseif ($order->getState()==\Magento\Sales\Model\Order::STATE_PROCESSING) {
                    $order->setStatus(self::STATUS_CANCEL_REQUEST);
                    $order->save();
                    $canceled=true;
                    $frontendEmailTemplate="ordercancelrequest_template";
                    $adminEmailTemplate="admin_ordercancelrequest_template";
                    $message=$this->helper->getPaidOrderMessage();
                }
                
                if ($canceled) {
                    $order->addStatusHistoryComment(
                        __("<strong>Reason</strong> : $reason<br><br>"
                                        . "Canceled by Customer")
                    )
                        ->setIsCustomerNotified(false)
                        ->save();
                
                    // Send Email Notification
                    $this->helper->sendOrderCancelMailToCustomer($order, $frontendEmailTemplate);
                    $this->helper->sendOrderCancelMailToAdmin($order, $adminEmailTemplate);
                    $this->messageManager->addSuccess(__($message));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something is Wrong. Please try again'));
                $this->logger->critical($e);
            }
            return $resultRedirect->setPath($redirectUrl);
        }
        return $resultRedirect->setPath($redirectUrl);
    }
}
