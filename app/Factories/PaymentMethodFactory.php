<?php

namespace App\Factories;

use App\Handlers\BankTransferPaymentHandler;
use App\Handlers\CardPaymentHandler;

class PaymentMethodFactory
{
   public static function make(string $method)
   {
        return match($method){
            'card' => new CardPaymentHandler(),
            'bank_transfer' => new BankTransferPaymentHandler(),
            default => throw new \Exception("Invalid payment method {$method}"),
        };
   }
}
