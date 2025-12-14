import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Show({ subscription }) {
    const [showRefundModal, setShowRefundModal] = useState(false);
    const { data, setData, post, processing } = useForm({
        amount: '',
    });

    const getStatusVariant = (status) => {
        switch (status) {
            case 'active':
                return 'success';
            case 'trialing':
                return 'info';
            case 'canceled':
                return 'gray';
            case 'past_due':
            case 'incomplete':
                return 'danger';
            default:
                return 'gray';
        }
    };

    const formatDate = (date) => {
        if (!date) return 'N/A';
        return new Date(date).toLocaleDateString();
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    };

    const handleCancel = () => {
        if (confirm('Are you sure you want to cancel this subscription?')) {
            router.post(route('admin.subscriptions.cancel', subscription.id));
        }
    };

    const handleRefund = (e) => {
        e.preventDefault();
        post(route('admin.subscriptions.refund', subscription.id), {
            onSuccess: () => setShowRefundModal(false),
        });
    };

    const isActive = subscription.stripe_status === 'active';
    const isCanceled = subscription.stripe_status === 'canceled' || subscription.ends_at;

    return (
        <AdminLayout header="Subscription Details">
            <Head title="Subscription Details" />

            <div className="mx-auto max-w-3xl">
                <div className="mb-6">
                    <Link
                        href={route('admin.subscriptions.index')}
                        className="text-indigo-600 hover:text-indigo-900"
                    >
                        &larr; Back to Subscriptions
                    </Link>
                </div>

                <div className="overflow-hidden rounded-lg bg-white shadow">
                    {/* Header */}
                    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h2 className="text-xl font-semibold text-gray-900">
                                    {subscription.user?.name || 'Unknown User'}
                                </h2>
                                <p className="text-sm text-gray-500">
                                    {subscription.user?.email}
                                </p>
                            </div>
                            <Badge variant={getStatusVariant(subscription.stripe_status)}>
                                {subscription.stripe_status}
                            </Badge>
                        </div>
                    </div>

                    {/* Details */}
                    <div className="px-6 py-4">
                        <dl className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Plan</dt>
                                <dd className="mt-1 text-sm text-gray-900">
                                    {subscription.plan?.name || subscription.type || 'N/A'}
                                </dd>
                            </div>

                            <div>
                                <dt className="text-sm font-medium text-gray-500">Subscription ID</dt>
                                <dd className="mt-1 font-mono text-xs text-gray-900">
                                    {subscription.stripe_id}
                                </dd>
                            </div>

                            <div>
                                <dt className="text-sm font-medium text-gray-500">Started</dt>
                                <dd className="mt-1 text-sm text-gray-900">
                                    {formatDate(subscription.created_at)}
                                </dd>
                            </div>

                            <div>
                                <dt className="text-sm font-medium text-gray-500">
                                    {subscription.ends_at ? 'Ends At' : 'Next Billing'}
                                </dt>
                                <dd className="mt-1 text-sm text-gray-900">
                                    {subscription.ends_at
                                        ? formatDate(subscription.ends_at)
                                        : formatDate(subscription.current_period_end)}
                                </dd>
                            </div>

                            {subscription.trial_ends_at && (
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Trial Ends</dt>
                                    <dd className="mt-1 text-sm text-gray-900">
                                        {formatDate(subscription.trial_ends_at)}
                                    </dd>
                                </div>
                            )}

                            {subscription.plan && (
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Price</dt>
                                    <dd className="mt-1 text-sm text-gray-900">
                                        {formatCurrency(subscription.plan.monthly_price)}/month
                                    </dd>
                                </div>
                            )}
                        </dl>
                    </div>

                    {/* Actions */}
                    <div className="border-t border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center gap-4">
                            {isActive && !isCanceled && (
                                <button
                                    onClick={handleCancel}
                                    className="rounded-md bg-yellow-600 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-700"
                                >
                                    Cancel Subscription
                                </button>
                            )}

                            <button
                                onClick={() => setShowRefundModal(true)}
                                className="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                            >
                                Issue Refund
                            </button>

                            {subscription.user && (
                                <Link
                                    href={route('admin.users.show', subscription.user.id)}
                                    className="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                                >
                                    View User
                                </Link>
                            )}
                        </div>
                    </div>
                </div>

                {/* Refund Modal */}
                {showRefundModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div className="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">
                                Issue Refund
                            </h3>
                            <form onSubmit={handleRefund}>
                                <div className="mb-4">
                                    <label className="block text-sm font-medium text-gray-700">
                                        Amount (USD)
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.amount}
                                        onChange={(e) => setData('amount', e.target.value)}
                                        placeholder="Leave empty for full refund"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p className="mt-1 text-xs text-gray-500">
                                        Leave empty to refund the full amount
                                    </p>
                                </div>
                                <div className="flex justify-end gap-3">
                                    <button
                                        type="button"
                                        onClick={() => setShowRefundModal(false)}
                                        className="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                                    >
                                        Process Refund
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
