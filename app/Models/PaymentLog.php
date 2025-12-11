<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\Subscription;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'action',
        'payment_method',
        'amount',
        'status',
        'stripe_payment_intent_id',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Action constants.
     */
    public const ACTION_SUBSCRIBE = 'subscribe';
    public const ACTION_CANCEL = 'cancel';
    public const ACTION_RESUME = 'resume';
    public const ACTION_CHANGE_PLAN = 'change_plan';
    public const ACTION_REFUND = 'refund';
    public const ACTION_PAYMENT_FAILED = 'payment_failed';

    /**
     * Status constants.
     */
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PENDING = 'pending';

    /**
     * Payment method constants.
     */
    public const PAYMENT_METHOD_CARD = 'card';
    public const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';

    /**
     * Get the user that owns the payment log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription associated with the payment log.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by payment method.
     */
    public function scopePaymentMethod($query, string $paymentMethod)
    {
        return $query->where('payment_method', $paymentMethod);
    }

    /**
     * Scope to get successful logs.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope to get failed logs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Get the formatted amount.
     */
    public function getFormattedAmountAttribute(): ?string
    {
        if ($this->amount === null) {
            return null;
        }

        return '$' . number_format($this->amount / 100, 2);
    }

    /**
     * Create a new payment log entry.
     */
    public static function log(
        int $userId,
        string $action,
        string $paymentMethod,
        string $status,
        ?int $subscriptionId = null,
        ?int $amount = null,
        ?string $stripePaymentIntentId = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'subscription_id' => $subscriptionId,
            'action' => $action,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'status' => $status,
            'stripe_payment_intent_id' => $stripePaymentIntentId,
            'metadata' => $metadata,
        ]);
    }
}
