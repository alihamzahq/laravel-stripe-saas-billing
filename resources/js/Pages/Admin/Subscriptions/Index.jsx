import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import DataTable from '@/Components/Admin/DataTable';
import Pagination from '@/Components/Admin/Pagination';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ subscriptions, filters }) {
    const statusOptions = [
        { value: '', label: 'All Statuses' },
        { value: 'active', label: 'Active' },
        { value: 'canceled', label: 'Canceled' },
        { value: 'past_due', label: 'Past Due' },
        { value: 'trialing', label: 'Trialing' },
        { value: 'incomplete', label: 'Incomplete' },
    ];

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

    const handleFilterChange = (e) => {
        const status = e.target.value;
        router.get(route('admin.subscriptions.index'), status ? { status } : {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const formatDate = (date) => {
        if (!date) return 'N/A';
        return new Date(date).toLocaleDateString();
    };

    const columns = [
        {
            header: 'User',
            render: (row) => (
                <div>
                    <div className="font-medium text-gray-900">
                        {row.user?.name || 'Unknown'}
                    </div>
                    <div className="text-sm text-gray-500">
                        {row.user?.email || 'N/A'}
                    </div>
                </div>
            ),
        },
        {
            header: 'Plan',
            render: (row) => row.plan?.name || row.type || 'N/A',
        },
        {
            header: 'Status',
            render: (row) => (
                <Badge variant={getStatusVariant(row.stripe_status)}>
                    {row.stripe_status}
                </Badge>
            ),
        },
        {
            header: 'Started',
            render: (row) => formatDate(row.created_at),
        },
        {
            header: 'Ends At',
            render: (row) => row.ends_at ? formatDate(row.ends_at) : '-',
        },
        {
            header: 'Actions',
            render: (row) => (
                <Link
                    href={route('admin.subscriptions.show', row.id)}
                    className="text-indigo-600 hover:text-indigo-900"
                >
                    View
                </Link>
            ),
        },
    ];

    return (
        <AdminLayout header="Subscriptions">
            <Head title="Subscriptions" />

            <div className="mb-6 flex items-center justify-between">
                <p className="text-gray-600">Manage customer subscriptions</p>
                <select
                    value={filters.status || ''}
                    onChange={handleFilterChange}
                    className="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    {statusOptions.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
            </div>

            <DataTable
                columns={columns}
                data={subscriptions.data}
                emptyMessage="No subscriptions found"
            />

            {subscriptions.last_page > 1 && (
                <div className="mt-6">
                    <Pagination links={subscriptions.links} />
                </div>
            )}
        </AdminLayout>
    );
}
