export default function StatsCard({ title, value, icon, trend, trendValue, className = '' }) {
    return (
        <div className={`rounded-lg bg-white p-6 shadow ${className}`}>
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm font-medium text-gray-500">{title}</p>
                    <p className="mt-1 text-2xl font-semibold text-gray-900">{value}</p>
                    {trend && (
                        <p className={`mt-1 text-sm ${trend === 'up' ? 'text-green-600' : 'text-red-600'}`}>
                            {trend === 'up' ? (
                                <span className="inline-flex items-center">
                                    <svg className="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                    {trendValue}
                                </span>
                            ) : (
                                <span className="inline-flex items-center">
                                    <svg className="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                    {trendValue}
                                </span>
                            )}
                        </p>
                    )}
                </div>
                {icon && (
                    <div className="rounded-full bg-indigo-100 p-3 text-indigo-600">
                        {icon}
                    </div>
                )}
            </div>
        </div>
    );
}
