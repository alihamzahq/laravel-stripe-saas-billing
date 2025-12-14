import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import DataTable from '@/Components/Admin/DataTable';
import Pagination from '@/Components/Admin/Pagination';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ users, filters }) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('admin.users.index'), search ? { search } : {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const getSubscriptionStatus = (user) => {
        const subscription = user.subscriptions?.[0];
        if (!subscription) return { label: 'No Subscription', variant: 'gray' };

        switch (subscription.stripe_status) {
            case 'active':
                return { label: 'Active', variant: 'success' };
            case 'trialing':
                return { label: 'Trialing', variant: 'info' };
            case 'canceled':
                return { label: 'Canceled', variant: 'gray' };
            case 'past_due':
                return { label: 'Past Due', variant: 'danger' };
            default:
                return { label: subscription.stripe_status, variant: 'gray' };
        }
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
                    <div className="font-medium text-gray-900">{row.name}</div>
                    <div className="text-sm text-gray-500">{row.email}</div>
                </div>
            ),
        },
        {
            header: 'Plan',
            render: (row) => {
                const subscription = row.subscriptions?.[0];
                return subscription?.plan?.name || 'None';
            },
        },
        {
            header: 'Status',
            render: (row) => {
                const status = getSubscriptionStatus(row);
                return <Badge variant={status.variant}>{status.label}</Badge>;
            },
        },
        {
            header: 'Joined',
            render: (row) => formatDate(row.created_at),
        },
        {
            header: 'Actions',
            render: (row) => (
                <Link
                    href={route('admin.users.show', row.id)}
                    className="text-indigo-600 hover:text-indigo-900"
                >
                    View
                </Link>
            ),
        },
    ];

    return (
        <AdminLayout header="Users">
            <Head title="Users" />

            <div className="mb-6 flex items-center justify-between">
                <p className="text-gray-600">Manage customer accounts</p>
                <form onSubmit={handleSearch} className="flex gap-2">
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search users..."
                        className="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <button
                        type="submit"
                        className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        Search
                    </button>
                    {filters.search && (
                        <button
                            type="button"
                            onClick={() => {
                                setSearch('');
                                router.get(route('admin.users.index'));
                            }}
                            className="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                        >
                            Clear
                        </button>
                    )}
                </form>
            </div>

            <DataTable
                columns={columns}
                data={users.data}
                emptyMessage="No users found"
            />

            {users.last_page > 1 && (
                <div className="mt-6">
                    <Pagination links={users.links} />
                </div>
            )}
        </AdminLayout>
    );
}
