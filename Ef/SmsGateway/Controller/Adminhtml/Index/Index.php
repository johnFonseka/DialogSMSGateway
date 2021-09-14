<?php
namespace Ef\SmsGateway\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Index extends Action {
	const MENU_ID = 'Ef_SmsGateway::index_index';

	protected $_pageFactory;

	public function __construct(
		Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory) {
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute() {
		$resultPage = $this->_pageFactory->create();
		$resultPage->getConfig()->getTitle()->prepend((__('All SMS Messages')));

		return $resultPage;
	}
}