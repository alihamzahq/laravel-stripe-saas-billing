import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import { Head, Link } from '@inertiajs/react';

export default function Show({ user, subscription, paymentHistory }) {
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

    return (
        <AdminLayout header="User Details">
            <Head title={user.name} />

            <div className="mx-auto max-w-4xl">
                <div className="mb-6">
                    <Link
                        href={route('admin.users.index')}
                        className="text-indigo-600 hover:text-indigo-900"
                    >
                        &larr; Back to Users
                    </Link>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {/* User Info Card */}
                    <div className="rounded-lg bg-white p-6 shadow">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">
                            User Information
                        </h3>
                        <dl className="space-y-4">
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Name</dt>
                                <dd className="mt-1 text-sm text-gray-900">{user.name}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Email</dt>
                                <dd className="mt-1 text-sm text-gray-900">{user.email}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Joined</dt>
                                <dd className="mt-1 text-sm text-gray-900">
                                    {formatDate(user.created_at)}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">
                                    Email Verified
                                </dt>
                                <dd className="mt-1">
                                    <Badge variant={user.email_verified_at ? 'success' : 'gray'}>
                                        {user.email_verified_at ? 'Verified' : 'Not Verified'}
                                    </Badge>
                                </dd>
                            </div>
                            {user.stripe_id && (
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">
                                        Stripe Customer ID
                                    </dt>
                                    <dd className="mt-1 font-mono text-xs text-gray-900">
                                        {user.stripe_id}
                                    </dd>
                                </div>
                            )}
                        </dl>
                    </div>

                    {/* Subscription Card */}
                    <div className="rounded-lg bg-white p-6 shadow">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">
                            Current Subscription
                        </h3>
                        {subscription ? (
                            <dl className="space-y-4">
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Plan</dt>
                                    <dd className="mt-1 text-sm text-gray-900">
                                        {subscription.plan?.name || subscription.type}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Status</dt>
                                    <dd className="mt-1">
                                        <Badge variant={getStatusVariant(subscription.stripe_status)}>
                                            {subscription.stripe_status}
                                        </Badge>
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Started</dt>
                                    <dd className="mt-1 text-sm text-gray-900">
                                        {formatDate(subscription.created_at)}
                                    </dd>
                                </div>
                                {subscription.ends_at && (
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500">
                                            Ends At
                                        </dt>
                                        <dd className="mt-1 text-sm text-gray-900">
                                            {formatDate(subscription.ends_at)}
                                        </dd>
                                    </div>
                                )}
                                <div className="pt-4">
                                    <Link
                                        href={route('admin.subscriptions.show', subscription.id)}
                                        className="text-indigo-600 hover:text-indigo-900"
                                    >
                                        View Subscription Details &rarr;
                                    </Link>
                                </div>
                            </dl>
                        ) : (
                            <p className="text-sm text-gray-500">No active subscription</p>
                        )}
                    </div>
                </div>

                {/* Payment History */}
                <div className="mt-6 rounded-lg bg-white p-6 shadow">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">
                        Recent Payment History
                    </h3>
                    {paymentHistory && paymentHistory.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Date
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Amount
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Description
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {paymentHistory.map((payment) => (
                                        <tr key={payment.id}>
                                            <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                                {formatDate(payment.created_at)}
                                            </td>
                                            <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                                {formatCurrency(payment.amount)}
                                            </td>
                                            <td className="whitespace-nowrap px-4 py-3">
                                                <Badge
                                                    variant={
                                                        payment.status === 'succeeded'
                                                            ? 'success'
                                                            : payment.status === 'failed'
                                                            ? 'danger'
                                                            : 'gray'
                                                    }
                                                >
                                                    {payment.status}
                                                </Badge>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-500">
                                                {payment.description || '-'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <p className="text-sm text-gray-500">No payment history</p>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
