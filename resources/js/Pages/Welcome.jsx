import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

export default function Welcome({ plans }) {
    const [billingCycle, setBillingCycle] = useState('monthly');

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
        }).format(amount / 100);
    };

    const features = [
        {
            title: 'Easy Integration',
            description: 'Simple REST API with comprehensive documentation for quick integration.',
            icon: (
                <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
            ),
        },
        {
            title: 'Secure Payments',
            description: 'Powered by Stripe for secure, PCI-compliant payment processing.',
            icon: (
                <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            ),
        },
        {
            title: 'Flexible Plans',
            description: 'Multiple subscription tiers with monthly and yearly billing options.',
            icon: (
                <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                </svg>
            ),
        },
        {
            title: 'Real-time Webhooks',
            description: 'Instant notifications for subscription events and payment updates.',
            icon: (
                <svg className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            ),
        },
    ];

    return (
        <>
            <Head title="SaaS Billing Platform" />

            <div className="min-h-screen bg-white">
                {/* Header */}
                <header className="bg-white shadow-sm">
                    <nav className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex h-16 items-center justify-between">
                            <div className="flex items-center">
                                <span className="text-2xl font-bold text-indigo-600">SaaS Billing</span>
                            </div>
                            <div className="flex items-center gap-6">
                                <a href="#features" className="text-gray-600 hover:text-gray-900">
                                    Features
                                </a>
                                <a href="#pricing" className="text-gray-600 hover:text-gray-900">
                                    Pricing
                                </a>
                                <Link
                                    href={route('admin.login')}
                                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    Admin Login
                                </Link>
                            </div>
                        </div>
                    </nav>
                </header>

                {/* Hero Section */}
                <section className="bg-gradient-to-br from-indigo-600 to-purple-700 py-20 text-white">
                    <div className="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                        <h1 className="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                            Subscription Billing Made Simple
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-xl text-indigo-100">
                            A complete API-first subscription management platform. Handle payments,
                            manage subscriptions, and scale your SaaS business effortlessly.
                        </p>
                        <div className="mt-10">
                            <a
                                href="#pricing"
                                className="inline-block rounded-md bg-white px-8 py-3 text-lg font-semibold text-indigo-600 shadow-lg hover:bg-indigo-50"
                            >
                                View Plans
                            </a>
                        </div>
                    </div>
                </section>

                {/* Features Section */}
                <section id="features" className="py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="text-center">
                            <h2 className="text-3xl font-bold text-gray-900 sm:text-4xl">
                                Everything You Need
                            </h2>
                            <p className="mt-4 text-lg text-gray-600">
                                Built with modern technologies for reliability and performance
                            </p>
                        </div>

                        <div className="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                            {features.map((feature, index) => (
                                <div
                                    key={index}
                                    className="rounded-lg bg-white p-6 shadow-md transition hover:shadow-lg"
                                >
                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                        {feature.icon}
                                    </div>
                                    <h3 className="mt-4 text-lg font-semibold text-gray-900">
                                        {feature.title}
                                    </h3>
                                    <p className="mt-2 text-gray-600">{feature.description}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Pricing Section */}
                <section id="pricing" className="bg-gray-50 py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="text-center">
                            <h2 className="text-3xl font-bold text-gray-900 sm:text-4xl">
                                Simple, Transparent Pricing
                            </h2>
                            <p className="mt-4 text-lg text-gray-600">
                                Choose the plan that fits your needs
                            </p>

                            {/* Billing Toggle */}
                            <div className="mt-8 flex items-center justify-center gap-4">
                                <span
                                    className={`text-sm font-medium ${
                                        billingCycle === 'monthly'
                                            ? 'text-gray-900'
                                            : 'text-gray-500'
                                    }`}
                                >
                                    Monthly
                                </span>
                                <button
                                    onClick={() =>
                                        setBillingCycle(
                                            billingCycle === 'monthly' ? 'yearly' : 'monthly'
                                        )
                                    }
                                    className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
                                        billingCycle === 'yearly' ? 'bg-indigo-600' : 'bg-gray-300'
                                    }`}
                                >
                                    <span
                                        className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
                                            billingCycle === 'yearly'
                                                ? 'translate-x-6'
                                                : 'translate-x-1'
                                        }`}
                                    />
                                </button>
                                <span
                                    className={`text-sm font-medium ${
                                        billingCycle === 'yearly'
                                            ? 'text-gray-900'
                                            : 'text-gray-500'
                                    }`}
                                >
                                    Yearly
                                    <span className="ml-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                                        Save 17%
                                    </span>
                                </span>
                            </div>
                        </div>

                        {/* Pricing Cards */}
                        <div className="mt-12 grid gap-8 lg:grid-cols-3">
                            {plans.map((plan, index) => (
                                <div
                                    key={plan.id}
                                    className={`rounded-2xl bg-white p-8 shadow-lg ${
                                        index === 1
                                            ? 'ring-2 ring-indigo-600 lg:scale-105'
                                            : ''
                                    }`}
                                >
                                    {index === 1 && (
                                        <span className="mb-4 inline-block rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-600">
                                            Most Popular
                                        </span>
                                    )}
                                    <h3 className="text-xl font-bold text-gray-900">{plan.name}</h3>
                                    <p className="mt-2 text-sm text-gray-600">{plan.description}</p>

                                    <div className="mt-6">
                                        <span className="text-4xl font-extrabold text-gray-900">
                                            {formatCurrency(
                                                billingCycle === 'monthly'
                                                    ? plan.monthly_price
                                                    : plan.yearly_price / 12
                                            )}
                                        </span>
                                        <span className="text-gray-600">/month</span>
                                        {billingCycle === 'yearly' && (
                                            <p className="mt-1 text-sm text-gray-500">
                                                Billed {formatCurrency(plan.yearly_price)} yearly
                                            </p>
                                        )}
                                    </div>

                                    <ul className="mt-6 space-y-3">
                                        {plan.features.map((feature, featureIndex) => (
                                            <li
                                                key={featureIndex}
                                                className="flex items-center gap-3 text-sm text-gray-600"
                                            >
                                                <svg
                                                    className="h-5 w-5 flex-shrink-0 text-green-500"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                                {feature}
                                            </li>
                                        ))}
                                    </ul>

                                    <button
                                        className={`mt-8 w-full rounded-lg py-3 text-sm font-semibold transition ${
                                            index === 1
                                                ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                                                : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
                                        }`}
                                    >
                                        Get Started
                                    </button>
                                </div>
                            ))}
                        </div>

                        <p className="mt-8 text-center text-sm text-gray-500">
                            This is an API-first platform. Subscribe via our REST API.
                        </p>
                    </div>
                </section>

                {/* Tech Stack Section */}
                <section className="py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="text-center">
                            <h2 className="text-3xl font-bold text-gray-900 sm:text-4xl">
                                Built With Modern Tech Stack
                            </h2>
                            <p className="mt-4 text-lg text-gray-600">
                                Powered by industry-leading technologies
                            </p>
                        </div>

                        <div className="mt-12 grid grid-cols-2 gap-8 md:grid-cols-4">
                            <div className="flex flex-col items-center">
                                <div className="flex h-16 w-16 items-center justify-center rounded-lg bg-red-100">
                                    <span className="text-2xl font-bold text-red-600">L</span>
                                </div>
                                <h3 className="mt-4 font-semibold text-gray-900">Laravel 12</h3>
                                <p className="mt-1 text-sm text-gray-500">PHP Framework</p>
                            </div>
                            <div className="flex flex-col items-center">
                                <div className="flex h-16 w-16 items-center justify-center rounded-lg bg-blue-100">
                                    <span className="text-2xl font-bold text-blue-600">R</span>
                                </div>
                                <h3 className="mt-4 font-semibold text-gray-900">React</h3>
                                <p className="mt-1 text-sm text-gray-500">Frontend Library</p>
                            </div>
                            <div className="flex flex-col items-center">
                                <div className="flex h-16 w-16 items-center justify-center rounded-lg bg-purple-100">
                                    <span className="text-2xl font-bold text-purple-600">I</span>
                                </div>
                                <h3 className="mt-4 font-semibold text-gray-900">Inertia.js</h3>
                                <p className="mt-1 text-sm text-gray-500">Modern Monolith</p>
                            </div>
                            <div className="flex flex-col items-center">
                                <div className="flex h-16 w-16 items-center justify-center rounded-lg bg-indigo-100">
                                    <span className="text-2xl font-bold text-indigo-600">S</span>
                                </div>
                                <h3 className="mt-4 font-semibold text-gray-900">Stripe</h3>
                                <p className="mt-1 text-sm text-gray-500">Payment Processing</p>
                            </div>
                        </div>

                        <div className="mt-12 grid grid-cols-2 gap-8 md:grid-cols-4">
                            <div className="flex flex-col items-center">
                                <div className="flex h-16 w-16 items-center justify-center rounded-lg bg-cyan-100">
                                    <span className="text-2xl font-bold text-cyan-600">T</span>
                                </div>
                                <h3 className="mt-4 font-semibold text-gray-900">Tailwind CSS</h3>
                                <p className="mt-1 text-sm text-gray-500">Styling</p>
                            </div>
                            <div className="flex flex-col items-center">
                                <div className="flex h-16 w-16 items-center justify-center rounded-lg bg-orange-100">
                                    <span className="text-2xl font-bold text-orange-600">C</span>
                                </div>
                                <h3 className="mt-4 font-semibold text-gray-900">Cashier</h3>
                                <p className="mt-1 text-sm text-gray-500">Billing Library</p>
                            </div>
                            <div className="flex flex-col items-center">
                                <div className="flex h-16 w-16 items-center justify-center rounded-lg bg-green-100">
                                    <span className="text-2xl font-bold text-green-600">S</span>
                                </div>
                                <h3 className="mt-4 font-semibold text-gray-900">Sanctum</h3>
                                <p className="mt-1 text-sm text-gray-500">API Authentication</p>
                            </div>
                            <div className="flex flex-col items-center">
                                <div className="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-100">
                                    <span className="text-2xl font-bold text-gray-600">M</span>
                                </div>
                                <h3 className="mt-4 font-semibold text-gray-900">MySQL</h3>
                                <p className="mt-1 text-sm text-gray-500">Database</p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Demo Credentials Section */}
                <section className="bg-indigo-50 py-16">
                    <div className="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                        <h2 className="text-2xl font-bold text-gray-900">
                            Try the Admin Panel
                        </h2>
                        <p className="mt-2 text-gray-600">
                            Use these demo credentials to explore the admin dashboard
                        </p>

                        <div className="mt-8 rounded-lg bg-white p-6 shadow-md">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Email</p>
                                    <p className="mt-1 font-mono text-lg text-gray-900">admin@example.com</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-gray-500">Password</p>
                                    <p className="mt-1 font-mono text-lg text-gray-900">password</p>
                                </div>
                            </div>
                            <div className="mt-6">
                                <Link
                                    href={route('admin.login')}
                                    className="inline-block rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                                >
                                    Go to Admin Login
                                </Link>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="bg-gray-900 py-12 text-gray-400">
                    <div className="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                        <p className="text-lg font-semibold text-white">SaaS Billing</p>
                        <p className="mt-2 text-sm">
                            A Laravel + Stripe subscription billing demo project
                        </p>
                        <p className="mt-4 text-xs">
                            &copy; {new Date().getFullYear()} All rights reserved.
                        </p>
                    </div>
                </footer>
            </div>
        </>
    );
}
