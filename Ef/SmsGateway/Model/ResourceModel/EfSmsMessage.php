<?php 
namespace Ef\SmsGateway\Model\ResourceModel;

use Magento\Customer\Model\Customer;

class EfSmsMessage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

	public function _construct() {
		$this->_init("ef_sms_messages","entity_id");
	}

	protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object) {

		$data = $object->getData();

		if (isset($data["client_id"])) { // For saving SMS details
			if (!isset($data['number']) || empty($data['number'])) {
				$data['number'] = $this->get_customer_mobile_from_id($data["client_id"]);
			}
			$data['number'] = $this->format_phone_number($data['number']);

			if (!isset($data['campaign']) || empty($data['campaign'])) {
				// $data['campaign'] = "Test";
				$data['campaign'] = "Providore.shop";
			}

			$data['status'] = "NEW";
			$data['tries'] = 0;
			$data['created'] = date('Y-m-d H:i:s');

			$object->setData($data);
		}
		return $object;
    }

    /**
     * Number formats that can expect
     *	0715424542
     *	715424542
     *	94715424542
     *	0094715424542
     *	+94715424542
     *
     * Number format that should return
     *	94715424542
     *
     * Invalid numbers will return empty str as it will return API error
     */
    private function format_phone_number($number) {
    	if (strlen($number) > 9) $temp_num = substr($number, -9); // last 9 chars
    	else if (strlen($number) < 9) return "";
    	else $temp_num = $number;

    	if (preg_match("/^\d+$/", $temp_num)) { // checking $temp_num contain only numbers
			// "is valid"
			$temp_num = "94".$temp_num;
	    	return $temp_num;
		} else { // "invalid"
			return "";
		}
    }

    private function get_customer_mobile_from_id($id) {
    	$mobile = "";
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customerBlock = $objectManager->get('Magento\Customer\Model\Customer');
		$customerBlock->load($id);
		$mobile = $customerBlock->getData();
		if(isset($mobile['mobile']))
			$mobile = $mobile['mobile'];

		return $mobile;
    }
}

?>