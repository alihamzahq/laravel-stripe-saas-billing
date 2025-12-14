import AdminLayout from '@/Layouts/AdminLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Edit({ plan }) {
    const { data, setData, put, processing, errors } = useForm({
        name: plan.name || '',
        slug: plan.slug || '',
        description: plan.description || '',
        features: plan.features?.length > 0 ? plan.features : [''],
        monthly_price: plan.monthly_price || '',
        yearly_price: plan.yearly_price || '',
        is_active: plan.is_active ?? true,
        sort_order: plan.sort_order || 0,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('admin.plans.update', plan.id));
    };

    const addFeature = () => {
        setData('features', [...data.features, '']);
    };

    const removeFeature = (index) => {
        setData('features', data.features.filter((_, i) => i !== index));
    };

    const updateFeature = (index, value) => {
        const newFeatures = [...data.features];
        newFeatures[index] = value;
        setData('features', newFeatures);
    };

    return (
        <AdminLayout header="Edit Plan">
            <Head title={`Edit ${plan.name}`} />

            <div className="mx-auto max-w-2xl">
                <div className="rounded-lg bg-white p-6 shadow">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <InputLabel htmlFor="name" value="Name" />
                            <TextInput
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="mt-1 block w-full"
                                required
                            />
                            <InputError message={errors.name} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel htmlFor="slug" value="Slug" />
                            <TextInput
                                id="slug"
                                type="text"
                                value={data.slug}
                                onChange={(e) => setData('slug', e.target.value)}
                                className="mt-1 block w-full"
                                required
                            />
                            <InputError message={errors.slug} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel htmlFor="description" value="Description" />
                            <textarea
                                id="description"
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows={3}
                            />
                            <InputError message={errors.description} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel value="Features" />
                            <div className="mt-2 flex flex-col gap-4">
                                {data.features.map((feature, index) => (
                                    <div key={index} className="flex items-center gap-3">
                                        <TextInput
                                            type="text"
                                            value={feature}
                                            onChange={(e) => updateFeature(index, e.target.value)}
                                            className="block w-full"
                                            placeholder="Feature description"
                                        />
                                        {data.features.length > 1 && (
                                            <button
                                                type="button"
                                                onClick={() => removeFeature(index)}
                                                className="flex-shrink-0 p-2 text-red-500 hover:text-red-700"
                                                title="Remove feature"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fillRule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clipRule="evenodd" />
                                                </svg>
                                            </button>
                                        )}
                                    </div>
                                ))}
                            </div>
                            <button
                                type="button"
                                onClick={addFeature}
                                className="mt-3 text-sm text-indigo-600 hover:text-indigo-900"
                            >
                                + Add Feature
                            </button>
                            <InputError message={errors.features} className="mt-2" />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel htmlFor="monthly_price" value="Monthly Price (cents)" />
                                <TextInput
                                    id="monthly_price"
                                    type="number"
                                    value={data.monthly_price}
                                    onChange={(e) => setData('monthly_price', e.target.value)}
                                    className="mt-1 block w-full"
                                    min="0"
                                    required
                                />
                                <p className="mt-1 text-xs text-gray-500">
                                    e.g., 999 = $9.99
                                </p>
                                <InputError message={errors.monthly_price} className="mt-2" />
                            </div>

                            <div>
                                <InputLabel htmlFor="yearly_price" value="Yearly Price (cents)" />
                                <TextInput
                                    id="yearly_price"
                                    type="number"
                                    value={data.yearly_price}
                                    onChange={(e) => setData('yearly_price', e.target.value)}
                                    className="mt-1 block w-full"
                                    min="0"
                                    required
                                />
                                <p className="mt-1 text-xs text-gray-500">
                                    e.g., 9999 = $99.99
                                </p>
                                <InputError message={errors.yearly_price} className="mt-2" />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel htmlFor="sort_order" value="Sort Order" />
                                <TextInput
                                    id="sort_order"
                                    type="number"
                                    value={data.sort_order}
                                    onChange={(e) => setData('sort_order', e.target.value)}
                                    className="mt-1 block w-full"
                                    min="0"
                                />
                                <InputError message={errors.sort_order} className="mt-2" />
                            </div>

                            <div className="flex items-center pt-6">
                                <label className="flex items-center">
                                    <input
                                        type="checkbox"
                                        checked={data.is_active}
                                        onChange={(e) => setData('is_active', e.target.checked)}
                                        className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span className="ml-2 text-sm text-gray-600">Active</span>
                                </label>
                            </div>
                        </div>

                        <div className="flex items-center justify-end gap-4">
                            <Link
                                href={route('admin.plans.index')}
                                className="text-gray-600 hover:text-gray-900"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                            >
                                Update Plan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
