<?php
/**
 * Copyright © 2015 Pay.nl All rights reserved.
 */

namespace Pasarela\Bancomer\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Pasarela\Bancomer\Model\Payment as BancomerPayment;
use Magento\Checkout\Model\Cart;

class BancomerConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        'pasarela_bancomer',        
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];
    
    /**
     * @var \Pasarela\Bancomer\Model\Payment
     */
    protected $payment ;

    protected $cart;


    /**     
     * @param PaymentHelper $paymentHelper
     * @param BancomerPayment $payment
     */
    public function __construct(PaymentHelper $paymentHelper, BancomerPayment $payment, Cart $cart) {        
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
        $this->cart = $cart;
        $this->payment = $payment;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {                
        $config = [];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $protocol = $this->hostSecure() === true ? 'https://' : 'http://';
                
                $config['payment']['bancomer_credentials'] = array("merchant_id" => $this->payment->getMerchantId(), "public_key" => $this->payment->getPublicKey(), "is_sandbox"  => $this->payment->isSandbox());                 
                $config['payment']['total'] = $this->cart->getQuote()->getGrandTotal();
                $config['payment']['is_logged_in'] = $this->payment->isLoggedIn();
                                
            }
        }
                
        return $config;
    }
    
    public function hostSecure() {
        $is_secure = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $is_secure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $is_secure = true;
        }
        
        return $is_secure;
    }
    
}
