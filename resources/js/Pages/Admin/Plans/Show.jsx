import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import { Head, Link } from '@inertiajs/react';

export default function Show({ plan }) {
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    };

    return (
        <AdminLayout header="Plan Details">
            <Head title={plan.name} />

            <div className="mx-auto max-w-2xl">
                <div className="mb-6 flex items-center justify-between">
                    <Link
                        href={route('admin.plans.index')}
                        className="text-indigo-600 hover:text-indigo-900"
                    >
                        &larr; Back to Plans
                    </Link>
                    <Link
                        href={route('admin.plans.edit', plan.id)}
                        className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        Edit Plan
                    </Link>
                </div>

                <div className="rounded-lg bg-white p-6 shadow">
                    <div className="mb-6 flex items-center justify-between">
                        <h2 className="text-2xl font-bold text-gray-900">{plan.name}</h2>
                        <Badge variant={plan.is_active ? 'success' : 'gray'}>
                            {plan.is_active ? 'Active' : 'Inactive'}
                        </Badge>
                    </div>

                    <dl className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <dt className="text-sm font-medium text-gray-500">Slug</dt>
                            <dd className="mt-1 text-sm text-gray-900">{plan.slug}</dd>
                        </div>

                        <div>
                            <dt className="text-sm font-medium text-gray-500">Sort Order</dt>
                            <dd className="mt-1 text-sm text-gray-900">{plan.sort_order}</dd>
                        </div>

                        <div>
                            <dt className="text-sm font-medium text-gray-500">Monthly Price</dt>
                            <dd className="mt-1 text-lg font-semibold text-gray-900">
                                {formatCurrency(plan.monthly_price)}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-sm font-medium text-gray-500">Yearly Price</dt>
                            <dd className="mt-1 text-lg font-semibold text-gray-900">
                                {formatCurrency(plan.yearly_price)}
                            </dd>
                        </div>

                        <div className="sm:col-span-2">
                            <dt className="text-sm font-medium text-gray-500">Description</dt>
                            <dd className="mt-1 text-sm text-gray-900">
                                {plan.description || 'No description'}
                            </dd>
                        </div>

                        <div className="sm:col-span-2">
                            <dt className="text-sm font-medium text-gray-500">Features</dt>
                            <dd className="mt-1">
                                {plan.features && plan.features.length > 0 ? (
                                    <ul className="list-inside list-disc space-y-1 text-sm text-gray-900">
                                        {plan.features.map((feature, index) => (
                                            <li key={index}>{feature}</li>
                                        ))}
                                    </ul>
                                ) : (
                                    <p className="text-sm text-gray-500">No features defined</p>
                                )}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-sm font-medium text-gray-500">Stripe Product ID</dt>
                            <dd className="mt-1 font-mono text-xs text-gray-900">
                                {plan.stripe_product_id || 'N/A'}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-sm font-medium text-gray-500">Created At</dt>
                            <dd className="mt-1 text-sm text-gray-900">
                                {new Date(plan.created_at).toLocaleDateString()}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-sm font-medium text-gray-500">Monthly Price ID</dt>
                            <dd className="mt-1 font-mono text-xs text-gray-900">
                                {plan.stripe_price_id_monthly || 'N/A'}
                            </dd>
                        </div>

                        <div>
                            <dt className="text-sm font-medium text-gray-500">Yearly Price ID</dt>
                            <dd className="mt-1 font-mono text-xs text-gray-900">
                                {plan.stripe_price_id_yearly || 'N/A'}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </AdminLayout>
    );
}
