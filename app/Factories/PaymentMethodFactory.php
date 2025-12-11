<?php

namespace App\Factories;

use App\Contracts\PaymentMethodInterface;
use App\Handlers\BankTransferPaymentHandler;
use App\Handlers\CardPaymentHandler;
use InvalidArgumentException;

class PaymentMethodFactory
{
    /**
     * Create a payment method handler based on the payment method type.
     *
     * @param string $method The payment method type ('card' or 'bank_transfer')
     * @return PaymentMethodInterface
     * @throws InvalidArgumentException
     */
    public static function make(string $method): PaymentMethodInterface
    {
        return match ($method) {
            'card' => new CardPaymentHandler(),
            'bank_transfer' => new BankTransferPaymentHandler(),
            default => throw new InvalidArgumentException("Invalid payment method: {$method}"),
        };
    }
}
