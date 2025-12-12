<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LogController extends Controller
{
    /**
     * Display webhook logs.
     */
    public function webhooks(Request $request): Response
    {
        $query = WebhookLog::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by event type
        if ($request->has('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->latest()->paginate(20);

        return Inertia::render('Admin/Logs/Webhooks', [
            'logs' => $logs,
            'filters' => $request->only(['status', 'event_type', 'from', 'to']),
            'statuses' => [
                WebhookLog::STATUS_RECEIVED,
                WebhookLog::STATUS_PROCESSED,
                WebhookLog::STATUS_FAILED,
            ],
        ]);
    }

    /**
     * Display payment logs.
     */
    public function payments(Request $request): Response
    {
        $query = PaymentLog::with('user');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->latest()->paginate(20);

        return Inertia::render('Admin/Logs/Payments', [
            'logs' => $logs,
            'filters' => $request->only(['status', 'action', 'from', 'to']),
            'statuses' => [
                PaymentLog::STATUS_SUCCESS,
                PaymentLog::STATUS_FAILED,
                PaymentLog::STATUS_PENDING,
            ],
            'actions' => [
                PaymentLog::ACTION_SUBSCRIBE,
                PaymentLog::ACTION_CANCEL,
                PaymentLog::ACTION_RESUME,
                PaymentLog::ACTION_CHANGE_PLAN,
                PaymentLog::ACTION_REFUND,
                PaymentLog::ACTION_PAYMENT_FAILED,
            ],
        ]);
    }
}
