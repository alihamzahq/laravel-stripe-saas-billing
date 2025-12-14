import AdminLayout from '@/Layouts/AdminLayout';
import Badge, { getStatusVariant } from '@/Components/Admin/Badge';
import DataTable from '@/Components/Admin/DataTable';
import StatsCard from '@/Components/Admin/StatsCard';
import { Head } from '@inertiajs/react';

export default function Dashboard({ stats }) {
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const paymentColumns = [
        {
            header: 'User',
            render: (row) => row.user?.name || 'N/A',
        },
        {
            header: 'Action',
            render: (row) => (
                <Badge variant={getStatusVariant(row.status)}>
                    {row.action}
                </Badge>
            ),
        },
        {
            header: 'Amount',
            render: (row) => (row.amount ? formatCurrency(row.amount) : '-'),
        },
        {
            header: 'Status',
            render: (row) => (
                <Badge variant={getStatusVariant(row.status)}>
                    {row.status}
                </Badge>
            ),
        },
        {
            header: 'Date',
            render: (row) => formatDate(row.created_at),
        },
    ];

    return (
        <AdminLayout header="Dashboard">
            <Head title="Admin Dashboard" />

            {/* Stats Grid */}
            <div className="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatsCard
                    title="Total Users"
                    value={stats.total_users}
                    icon={
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    }
                />
                <StatsCard
                    title="Active Subscriptions"
                    value={stats.active_subscriptions}
                    icon={
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    }
                />
                <StatsCard
                    title="Active Plans"
                    value={`${stats.active_plans} / ${stats.total_plans}`}
                    icon={
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    }
                />
                <StatsCard
                    title="Monthly Revenue"
                    value={formatCurrency(stats.monthly_revenue)}
                    icon={
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    }
                />
            </div>

            {/* Recent Payments */}
            <div>
                <h2 className="mb-4 text-lg font-semibold text-gray-900">Recent Payments</h2>
                <DataTable
                    columns={paymentColumns}
                    data={stats.recent_payments || []}
                    emptyMessage="No recent payments"
                />
            </div>
        </AdminLayout>
    );
}
