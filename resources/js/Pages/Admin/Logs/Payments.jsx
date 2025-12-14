import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Pagination from '@/Components/Admin/Pagination';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Payments({ logs, filters, statuses, actions }) {
    const [localFilters, setLocalFilters] = useState({
        status: filters.status || '',
        action: filters.action || '',
        from: filters.from || '',
        to: filters.to || '',
    });

    const handleFilterChange = (key, value) => {
        setLocalFilters((prev) => ({ ...prev, [key]: value }));
    };

    const applyFilters = () => {
        const activeFilters = Object.fromEntries(
            Object.entries(localFilters).filter(([_, v]) => v !== '')
        );
        router.get(route('admin.logs.payments'), activeFilters, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setLocalFilters({ status: '', action: '', from: '', to: '' });
        router.get(route('admin.logs.payments'));
    };

    const getStatusVariant = (status) => {
        switch (status) {
            case 'success':
                return 'success';
            case 'pending':
                return 'warning';
            case 'failed':
                return 'danger';
            default:
                return 'gray';
        }
    };

    const getActionLabel = (action) => {
        const labels = {
            subscribe: 'Subscribe',
            cancel: 'Cancel',
            resume: 'Resume',
            change_plan: 'Change Plan',
            refund: 'Refund',
            payment_failed: 'Payment Failed',
        };
        return labels[action] || action;
    };

    const formatDate = (date) => {
        if (!date) return 'N/A';
        return new Date(date).toLocaleString();
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    };

    const hasActiveFilters = Object.values(filters).some((v) => v);

    return (
        <AdminLayout header="Payment Logs">
            <Head title="Payment Logs" />

            {/* Filters */}
            <div className="mb-6 rounded-lg bg-white p-4 shadow">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Status
                        </label>
                        <select
                            value={localFilters.status}
                            onChange={(e) => handleFilterChange('status', e.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">All</option>
                            {statuses.map((status) => (
                                <option key={status} value={status}>
                                    {status}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            Action
                        </label>
                        <select
                            value={localFilters.action}
                            onChange={(e) => handleFilterChange('action', e.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">All</option>
                            {actions.map((action) => (
                                <option key={action} value={action}>
                                    {getActionLabel(action)}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            From
                        </label>
                        <input
                            type="date"
                            value={localFilters.from}
                            onChange={(e) => handleFilterChange('from', e.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">
                            To
                        </label>
                        <input
                            type="date"
                            value={localFilters.to}
                            onChange={(e) => handleFilterChange('to', e.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div className="flex items-end gap-2">
                        <button
                            onClick={applyFilters}
                            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            Filter
                        </button>
                        {hasActiveFilters && (
                            <button
                                onClick={clearFilters}
                                className="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                            >
                                Clear
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {/* Logs Table */}
            <div className="overflow-hidden rounded-lg bg-white shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    User
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Action
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Amount
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 bg-white">
                            {logs.data.length > 0 ? (
                                logs.data.map((log) => (
                                    <tr key={log.id} className="hover:bg-gray-50">
                                        <td className="whitespace-nowrap px-4 py-3">
                                            {log.user ? (
                                                <Link
                                                    href={route('admin.users.show', log.user.id)}
                                                    className="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                                >
                                                    {log.user.name}
                                                </Link>
                                            ) : (
                                                <span className="text-sm text-gray-500">
                                                    Unknown
                                                </span>
                                            )}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                            {getActionLabel(log.action)}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                            {log.amount ? formatCurrency(log.amount) : '-'}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3">
                                            <Badge variant={getStatusVariant(log.status)}>
                                                {log.status}
                                            </Badge>
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                            {formatDate(log.created_at)}
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="px-4 py-8 text-center text-sm text-gray-500"
                                    >
                                        No payment logs found
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {logs.last_page > 1 && (
                <div className="mt-6">
                    <Pagination links={logs.links} />
                </div>
            )}
        </AdminLayout>
    );
}
