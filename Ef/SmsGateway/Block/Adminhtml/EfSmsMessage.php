<?php
namespace Ef\SmsGateway\Block\Adminhtml;

class EfSmsMessage extends \Magento\Backend\Block\Widget\Grid\Container
{

	protected function _construct() {
		$this->_controller = 'adminhtml_index';
		$this->_blockGroup = 'Ef_SmsGateway';
		$this->_headerText = __('SMS Messages');
		// $this->_addButtonLabel = __('Create New SMS');
		parent::_construct();
        $this->buttonList->remove('add');
        // $this->_removeButton('add');
	}
}