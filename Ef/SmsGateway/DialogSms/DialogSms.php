<?php

/**
	This class will responsible for creating queue for send sms using Dialog sms API.
	Sms messages will be stored in 'ef_sms_messages' database table with status. 

	This class will extract first to send 50 SMSs and will feed that information to 
	Dialog API. 
	Results will be process according to the response received.


	STATUS
		NEW => Before SMS sent at least 1 time
		TRIED => Module attempted to send the sms, but failed
		SENT => SMS sent successfully
		ERRNUM => Error in number as per Dialog SMS API Doc
		ERRAPI => Error received from API call as per Dialog SMS API Doc
 */

namespace Ef\SmsGateway\DialogSms;

use Ef\SmsGateway\Model\EfSmsMessageFactory;

class DialogSms {

	protected $smsModelFactory;
	private $scopeConfig;
	private $logger;

	private $api_username;
	private $api_password;
	private $api_digest;

	private $api_request_payload;
	private $api_request_headers;

	const SMS_PER_REQUEST = 50;
	const API_SEND_SMS_URL = "https://richcommunication.dialog.lk/api/sms/send";

	public function __construct(\Ef\SmsGateway\Model\EfSmsMessageFactory  $_sampleSms = null) {

		$this->get_api_config();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->logger = $objectManager->get('Psr\Log\LoggerInterface');

		if ($this->checkEnabledFlag()) {
			if (is_null($_sampleSms)) {
				$_sampleSms = $objectManager->get('\Ef\SmsGateway\Model\EfSmsMessageFactory');
			}
			$this->smsModelFactory = $_sampleSms;
		}
		
	}

	public function get_api_config() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$scopeConfigObj = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

		$this->scopeConfig = $scopeConfigObj->getValue('ef_sms_gateway_tab');
		return $this->scopeConfig;
	}

	public function queue_sms($client_id, $message) {
		$this->smsModelFactory->create();
		if (!is_null($client_id) && !empty($client_id))
        	return $this->smsModelFactory->addSms($client_id, $message);

        return false;
	}

	public function retrieve_sms() {
		if (!$this->checkEnabledFlag()) {
			return false;
		}

		$sms_messges_from_db = $this->smsModelFactory->create();
        $collection = $sms_messges_from_db->getCollection()
        	->addFieldToFilter('number', ['neq' => ''])
        	->addFieldToFilter('status', ['neq' => 'SENT'])
        	->addFieldToFilter('status', ['neq' => 'ERRNUM'])
        	->addFieldToFilter('tries', ['lteq' => 5])
        	->setPageSize(self::SMS_PER_REQUEST);

        $temp_sms_payload = array();
        foreach($collection as $item){
        	$temp_sms_payload[] = $item->getData();
        }
        return $temp_sms_payload;
	}

	/**
	'{
		"messages": [
			{
				"clientRef":"0934345",
				"number":"94715424542",
				"mask":"Test",
				"text":"This is a test message",
				"campaignName":"Test Campaign"
			}
		]
	}'
	 */
	public function create_api_paylod($list_messages_from_db = NULL) {
		if (!$this->checkEnabledFlag()) {
			return false;
		}

		if (is_null($list_messages_from_db)) {
			$list_messages_from_db = $this->retrieve_sms();
		}

		$formatted_messages = array();
		$temp = array();
		foreach ($list_messages_from_db as $key => $value) {
			$temp['clientRef'] = $value['entity_id'].'.'.$value['client_id'];
			$temp['number'] = $value['number'];
			$temp['mask'] = "Test";
			$temp['text'] = $value['message'];
			$temp['campaignName'] = "Merkado Order";

			$formatted_messages['messages'][] = $temp;
		}
		$this->api_request_payload = json_encode($formatted_messages);

		return $this->api_request_payload;
	}

	/**
	$headers = [
		'Content-Type: application/json',
		'USER: '.$username,
		'DIGEST: '.$digest,
		'CREATED: '.$now
	];
	 */
	private function create_headers() {
		if (!$this->checkEnabledFlag()) {
			return false;
		}
		$now = date("Y-m-d\TH:i:s");
		$headers = [
			'Content-Type: application/json',
			'USER: '.$this->scopeConfig['api_config_info']['api_user_name'],
			'DIGEST: '.md5($this->scopeConfig['api_config_info']['api_user_password']),
			'CREATED: '.$now
		];
		return $headers;
	}

	public function send_sms() {
		date_default_timezone_set('Asia/Colombo');	// This is must or Dialog API will reject API request

		if (!$this->checkEnabledFlag()) {
			return false;
		}

		$headers = $this->create_headers();
		$body = $this->create_api_paylod();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::API_SEND_SMS_URL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$body); //Post Fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$server_output = curl_exec($ch);
		curl_close ($ch);

		$this->process_response($server_output);
		return $server_output;
	}

	/**
	{
		"resultCode": 100,
		"resultDesc": "success",
		"messages": [
			{
				"clientRef": "0934345",
				"serverRef": "5748594745893090",
				"resultCode": 0,
				"resultDesc": "SUCCESS"
			},
			...
		]
	}
	 */
	private function process_response($response) {

		$smsModel = $this->smsModelFactory->create();
		$response = json_decode($response);

		if (is_object($response) && isset($response->resultCode)) {
			if ($response->resultCode == 0) {
				/**/
				$resultCode = $response->resultCode;
				$resultDesc = $response->resultDesc;
				$messages = $response->messages;

				foreach ($messages as $key => $value) {
					$res_data = array();
					$sms_id = explode(".", $value->clientRef);
					$smsModel->load($sms_id[0]);

					$res_data['entity_id'] = $sms_id[0];
					$res_data['api_response'] = json_encode($value);

					if ($value->resultDesc == "SUCCESS") {
						$res_data['status'] = "SENT";
						$res_data['sent'] = date('Y-m-d H:i:s');
					} else {
						$res_data['status'] = "ERRAPI";
					}
					$tries = $smsModel->getTries();
					$res_data['tries'] = intval($tries) + 1;

					$smsModel->setData($res_data);
					$smsModel->save();
				}
				/**/
			} else {
				$this->logger->critical('Dialog SMS API Error ', ['exception' => $response]);
			}
		}
		// Write API call info to log file
	}

	private function checkEnabledFlag() {
		return $this->scopeConfig['general']['ef_sms_gateway_enabled'];
	}
}