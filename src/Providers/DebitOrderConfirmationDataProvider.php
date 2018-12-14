<?php

namespace Debit\Providers;

use Plenty\Modules\Order\Models\Order;
use Plenty\Plugin\Templates\Twig;

use Debit\Helper\DebitHelper;
use Debit\Services\SessionStorageService;
use Debit\Services\SettingsService;
/**
 * Class DebitOrderConfirmationDataProvider
 * @package Debit\Providers
 */
class DebitOrderConfirmationDataProvider
{
    public function call(   Twig $twig, SettingsService $settings, DebitHelper $debitHelper,
                            SessionStorageService $service, $args)
    {
        $mop = $service->getOrderMopId();
        $orderId = null;
        $content = '';

        /*
         * Load the method of payment id from the order
         */
        $order = $args[0];
        if($order instanceof Order) {
            $orderId = $order->id;
            foreach ($order->properties as $property) {
                if($property->typeId == 3) {
                    $mop = $property->value;
                    break;
                }
            }
        } elseif(is_array($order)) {
            $orderId = $order['id'];
            foreach ($order['properties'] as $property) {
                if($property['typeId'] == 3) {
                    $mop = $property['value'];
                    break;
                }
            }
        }

        if($mop ==$debitHelper->getDebitMopId())
        {
            $lang = $service->getLang();
            if($settings->getSetting('showBankData', $lang))
            {
                $content .= $twig->render('Debit::BankDetails');
            }

            if($settings->getSetting('showDesignatedUse', $lang))
            {
                $content .=  $twig->render('Debit::DesignatedUse', ['orderId'=>$orderId]);
            }
        }

        return $content;
    }
}