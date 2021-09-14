<?php

namespace Ef\SmsGateway\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;

use Ef\SmsGateway\Model\EfSmsMessageFactory;
use Ef\SmsGateway\DialogSms\DialogSms;

class OnOrder implements \Magento\Framework\Event\ObserverInterface {

    public $orderRepository;
    public $ProductRepository;
    public $_customerSession;
    public $order_success_sms;

    public function __construct(OrderRepositoryInterface $OrderRepositoryInterface, ProductRepository $ProductRepository, \Magento\Customer\Model\Session $customerSession, \Ef\SmsGateway\Model\EfSmsMessageFactory  $order_success_sms) {
        $this->orderRepository = $OrderRepositoryInterface;
        $this->ProductRepository =$ProductRepository;
        $this->_customerSession = $customerSession;
        $this->order_success_sms = $order_success_sms;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {

        // A strange error. This order id is not actually existing. Have to fix
        $order_id = $observer->getEvent()->getOrderIds()[0];
        $order = $this->orderRepository->get($order_id);
        $order_total = 0.00;
        $order_id = $order->getId();
        $order_total = $order->getData('base_grand_total');

        $sms_body = "Thank you for shopping with us. Your order ID is #".$order_id.". ";

        $shipping_address = $order->getShippingAddress();
        $order_telephone = $shipping_address->getData("telephone");

        $delivery_text = "";
        $delivery_info = $order->getDeliverySlotInfo();
        if (!is_null($delivery_info) && !empty($delivery_info)) {
            $delivery_info = json_decode($delivery_info);

            if (is_object($delivery_info)) {
                if (isset($delivery_info->delivety_methord)) {
                    if ($delivery_info->delivety_methord == 'deliveryslots_deliveryslots') {
                        $delivery_text = "Your order amount is ".$order_total." and will be delivered to ".$delivery_info->city." on ".$delivery_info->date." between ".$delivery_info->start_time." - ".$delivery_info->end_time.". ";
                    } else if ($delivery_info->delivety_methord == 'expressdelivery_expressdelivery') {
                        $delivery_text = "Your order amount is ".$order_total." and will be delivered to ".$delivery_info->city." on our next express delivery slot. ";
                    } else if ($delivery_info->delivety_methord == 'storepickup_storepickup') {
                        $delivery_text = "Your order amount is ".$order_total." and you can pick the order on ".$delivery_info->date." after ".$delivery_info->start_time.". from our warehouse facility at Rajagiriya. ";
                    }
                }
            }
        }

        $model = $this->order_success_sms->create();
        if($this->_customerSession->isLoggedIn()) {
            $customer_id = $this->_customerSession->getCustomer()->getId();
            $saveData = $model->addSms($customer_id, $sms_body." ".$delivery_text." - Providore.shop");
        } else {
            $customer_id = "-1";
            $saveData = $model->addSms($customer_id, $sms_body." ".$delivery_text." - Providore.shop", $order_telephone);
        }
    }
}