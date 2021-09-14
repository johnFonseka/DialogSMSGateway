<?php 
namespace Ef\SmsGateway\Model\ResourceModel\EfSmsMessage;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

	public function _construct() {
		$this->_init("Ef\SmsGateway\Model\EfSmsMessage","Ef\SmsGateway\Model\ResourceModel\EfSmsMessage");
	}
}

?>