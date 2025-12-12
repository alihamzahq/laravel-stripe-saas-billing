<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Subscription;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Display a listing of subscriptions.
     */
    public function index(Request $request): Response
    {
        $query = Subscription::with(['user', 'plan']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('stripe_status', $request->status);
        }

        $subscriptions = $query->latest()->paginate(15);

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Display the specified subscription.
     */
    public function show(Subscription $subscription): Response
    {
        $subscription->load(['user', 'plan']);

        return Inertia::render('Admin/Subscriptions/Show', [
            'subscription' => $subscription,
        ]);
    }

    /**
     * Cancel the specified subscription.
     */
    public function cancel(Subscription $subscription): RedirectResponse
    {
        $user = $subscription->user;

        $this->subscriptionService->cancel($user);

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription cancelled successfully.');
    }

    /**
     * Refund the specified subscription.
     */
    public function refund(Request $request, Subscription $subscription): RedirectResponse
    {
        $amount = $request->input('amount') ? (int) ($request->input('amount') * 100) : null;

        $success = $this->subscriptionService->refund($subscription, $amount);

        if ($success) {
            return redirect()->route('admin.subscriptions.show', $subscription)
                ->with('success', 'Refund processed successfully.');
        }

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('error', 'Failed to process refund.');
    }
}
