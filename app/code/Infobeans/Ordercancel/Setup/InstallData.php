<?php
namespace Infobeans\Ordercancel\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class InstallData implements InstallDataInterface
{ 
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    { 

        $setup->startSetup();
        
        $data = [];
        $statuses = [
            'cancel_request' => __('Requested for Cancel'),           
        ];
        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }
        $setup->getConnection()->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data); 
        
        $data = [];
        $data[] = [
                        'status' => "cancel_request",
                        'state' => "processing",
                        'is_default' => 0,
                        'visible_on_front' => 1,
                    ];
        
        $setup->getConnection()->insertArray(
            $setup->getTable('sales_order_status_state'),
            ['status', 'state', 'is_default','visible_on_front'],
            $data
        );  

        $setup->endSetup();
    }
}
