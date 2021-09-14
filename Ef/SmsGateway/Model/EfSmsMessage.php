<?php 
namespace Ef\SmsGateway\Model;

class EfSmsMessage extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

	public function _construct() {
		$this->_init("Ef\SmsGateway\Model\ResourceModel\EfSmsMessage");
	}

	public function getIdentities() {}

	public function getDefaultValues() {}

	public function addSms($client_id, $message, $phone = null) {

		$this->addData([
            "client_id" => $client_id,
            "message" => $message,
            "number" => $phone
        ]);
        return $this->save();
	}
}

?>