<?php 


namespace Infobeans\Ordercancel\Controller\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
 
class Cancel extends \Magento\Framework\App\Action\Action {

    
    const STATUS_CANCEL_REQUEST = 'cancel_request';
    
    protected $resultPageFactory;
    
    protected $orderRepository;
    
    protected $orderManagement;
    
    protected $_coreRegistry = null;
    
    protected $escaper;
    
    protected $helper;


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
        \Infobeans\Ordercancel\Helper\Data $helper
    )
    {
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository=$orderRepository;
        $this->orderManagement=$orderManagement;
        $this->escaper = $escaper;
        $this->helper = $helper;
        parent::__construct($context);
    }
    
    
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
    

    /**
     * Execute view action
     * 
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->_redirect('*/*/');
            return;
        }
        
        $error=false;
        
        if (!\Zend_Validate::is(trim($post['order_id']), 'NotEmpty')) {
                $error = true;
        }
        
        if (!\Zend_Validate::is(trim($post['reason']), 'NotEmpty')) {
                $error = true;
        }
        
        if ($error) {
            throw new \Exception();
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $order = $this->_initOrder();
        if ($order) {
            try {
                
               $message="Hello";
                $this->helper->sendOrderCancelMailToCustomer($order->getCustomerEmail(),$order->getCustomerName(),$message);
                exit;
                
                $reason = $this->_escaper->escapeHtml(trim($post['reason'])); 
                
                if ($order->canCancel()) {
                    $this->orderManagement->cancel($order->getEntityId());
                    $order->addStatusHistoryComment(
                                __("<strong>Reason</strong> : $reason<br><br>"
                                        . "Canceled by Customer")
                        )
                    ->setIsCustomerNotified(false)
                    ->save();
                    
                }
                else if ($order->getState()==\Magento\Sales\Model\Order::STATE_PROCESSING)
                { 
                    $order->setStatus(self::STATUS_CANCEL_REQUEST);
                    $order->addStatusHistoryComment(
                                __("<strong>Reason</strong> : $reason<br><br>"
                                        . "Canceled by Customer")
                        )
                    ->setIsCustomerNotified(false)
                    ->save();
                } 
                
                $this->messageManager->addSuccess(__('You canceled the order.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('You have not canceled the item.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }
            return $resultRedirect->setPath($this->_redirect->getRefererUrl());
        }
        return $resultRedirect->setPath($this->_redirect->getRefererUrl());
    }
}

