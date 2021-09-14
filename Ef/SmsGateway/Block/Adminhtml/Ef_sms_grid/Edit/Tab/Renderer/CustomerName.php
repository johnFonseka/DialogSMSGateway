<?php

namespace Ef\SmsGateway\Block\Adminhtml\Ef_sms_grid\Edit\Tab\Renderer;

use Magento\Framework\DataObject;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class CustomerName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {
    
    protected $registry;
    protected $customerFactory;
    /**
     * @param \Magento\Catalog\Model\CategoryFactory $customerFactory
     */
    public function __construct(
        \Magento\Customer\Model\Customer $customers,
        Context $context,
        array $data = array() ) {
        $this->customerFactory = $customers;
        parent::__construct($context, $data);
    }

    public function _getValue(\Magento\Framework\DataObject $row) {
        // // Get default value:
        // $value = parent::_getValue($row);
        
 
        // if (!$options) {
        //     $options = $this->attributeFactory->create()->loadByCode('catalog_product', 'manufacturer')->getOptions();
        // }
        
        // foreach ($options as $option) {
        //     if ($option->getValue() == $value) {
        //         return $option->getLabel();
        //     }
        //}
        
        return "John";
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row) {
        $value = parent::_getValue($row);
        $customer = $this->customerFactory->load($value);
        return $value.'. '.$customer->getFirstname().' '.$customer->getLastname();
    }
}