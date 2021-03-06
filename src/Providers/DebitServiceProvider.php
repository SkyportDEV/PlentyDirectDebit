<?php //strict

namespace Debit\Providers;

use Debit\Extensions\DebitTwigServiceProvider;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Plugin\ServiceProvider;
use Debit\Helper\DebitHelper;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use Plenty\Plugin\Events\Dispatcher;

use Debit\Methods\DebitPaymentMethod;

use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemAdd;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;
use Plenty\Plugin\Templates\Twig;

/**
 * Class DebitServiceProvider
 * @package Debit\Providers
 */
 class DebitServiceProvider extends ServiceProvider
 {
     public function register()
     {
         $this->getApplication()->register(DebitRouteServiceProvider::class);
     }

     /**
      * Boot additional services for the payment method
      *
      * @param Twig $twig
      * @param DebitHelper $paymentHelper
      * @param PaymentMethodContainer $payContainer
      * @param Dispatcher $eventDispatcher
      */
     public function boot(  Twig $twig,
                            DebitHelper $paymentHelper,
                            PaymentMethodContainer $payContainer,
                            Dispatcher $eventDispatcher)
     {

         $twig->addExtension(DebitTwigServiceProvider::class);

         // Register the Debit payment method in the payment method container
         $payContainer->register('plenty::DEBIT', DebitPaymentMethod::class,
                                [ AfterBasketChanged::class, AfterBasketItemAdd::class, AfterBasketCreate::class ]
         );

         // Listen for the event that gets the payment method content
         $eventDispatcher->listen(GetPaymentMethodContent::class,
                 function(GetPaymentMethodContent $event) use( $paymentHelper)
                 {
                     if($event->getMop() == $paymentHelper->getDebitMopId())
                     {
                         $event->setValue('');
                         $event->setType('continue');
                     }
                 });

         // Listen for the event that executes the payment
         $eventDispatcher->listen(ExecutePayment::class,
             function(ExecutePayment $event) use( $paymentHelper)
             {
                 if($event->getMop() == $paymentHelper->getDebitMopId())
                 {
                     $event->setValue('<h1>Lastschrift<h1>');
                     $event->setType('htmlContent');
                 }
             });
     }
 }
