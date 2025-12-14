const variants = {
    success: 'bg-green-100 text-green-800',
    danger: 'bg-red-100 text-red-800',
    warning: 'bg-yellow-100 text-yellow-800',
    info: 'bg-blue-100 text-blue-800',
    gray: 'bg-gray-100 text-gray-800',
    indigo: 'bg-indigo-100 text-indigo-800',
};

export default function Badge({ children, variant = 'gray', className = '' }) {
    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${variants[variant]} ${className}`}
        >
            {children}
        </span>
    );
}

// Helper function to get status badge variant
export function getStatusVariant(status) {
    const statusMap = {
        // Subscription statuses
        active: 'success',
        canceled: 'danger',
        incomplete: 'warning',
        incomplete_expired: 'danger',
        past_due: 'warning',
        trialing: 'info',
        unpaid: 'danger',
        paused: 'gray',

        // Payment statuses
        success: 'success',
        failed: 'danger',
        pending: 'warning',

        // Webhook statuses
        received: 'info',
        processed: 'success',

        // Plan statuses
        true: 'success',
        false: 'gray',
    };

    return statusMap[status] || 'gray';
}
