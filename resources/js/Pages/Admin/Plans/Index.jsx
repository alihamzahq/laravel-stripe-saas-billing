import AdminLayout from '@/Layouts/AdminLayout';
import Badge, { getStatusVariant } from '@/Components/Admin/Badge';
import DataTable from '@/Components/Admin/DataTable';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ plans }) {
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount / 100);
    };

    const handleToggleActive = (plan) => {
        router.post(route('admin.plans.toggle-active', plan.id), {}, {
            preserveScroll: true,
        });
    };

    const handleDelete = (plan) => {
        if (confirm(`Are you sure you want to delete "${plan.name}"?`)) {
            router.delete(route('admin.plans.destroy', plan.id));
        }
    };

    const columns = [
        {
            header: 'Name',
            render: (row) => (
                <div>
                    <div className="font-medium text-gray-900">{row.name}</div>
                    <div className="text-sm text-gray-500">{row.slug}</div>
                </div>
            ),
        },
        {
            header: 'Monthly',
            render: (row) => formatCurrency(row.monthly_price),
        },
        {
            header: 'Yearly',
            render: (row) => formatCurrency(row.yearly_price),
        },
        {
            header: 'Status',
            render: (row) => (
                <Badge variant={row.is_active ? 'success' : 'gray'}>
                    {row.is_active ? 'Active' : 'Inactive'}
                </Badge>
            ),
        },
        {
            header: 'Actions',
            render: (row) => (
                <div className="flex items-center gap-2">
                    <Link
                        href={route('admin.plans.show', row.id)}
                        className="text-indigo-600 hover:text-indigo-900"
                    >
                        View
                    </Link>
                    <Link
                        href={route('admin.plans.edit', row.id)}
                        className="text-indigo-600 hover:text-indigo-900"
                    >
                        Edit
                    </Link>
                    <button
                        onClick={() => handleToggleActive(row)}
                        className={`${row.is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'}`}
                    >
                        {row.is_active ? 'Deactivate' : 'Activate'}
                    </button>
                    <button
                        onClick={() => handleDelete(row)}
                        className="text-red-600 hover:text-red-900"
                    >
                        Delete
                    </button>
                </div>
            ),
        },
    ];

    return (
        <AdminLayout header="Plans">
            <Head title="Plans" />

            <div className="mb-6 flex items-center justify-between">
                <p className="text-gray-600">Manage subscription plans</p>
                <Link
                    href={route('admin.plans.create')}
                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    Add Plan
                </Link>
            </div>

            <DataTable
                columns={columns}
                data={plans}
                emptyMessage="No plans found"
            />
        </AdminLayout>
    );
}
