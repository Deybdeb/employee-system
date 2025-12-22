<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';

const employee = usePage().props.employee;

// Split name for display
const nameParts = (employee.name || '').split(' ');
const displayName = employee.name || 'User';

const menuItems = [
    { name: 'Personal Details', href: '/my-info/personal' },
    { name: 'Contact Details', href: '/my-info/contact' },
];
</script>

<template>
    <AuthenticatedLayout>
        <div class="flex flex-col lg:flex-row gap-8">

            <div class="w-full lg:w-1/4 shrink-0">
                <div class="bg-white rounded-card shadow-card overflow-hidden">
                    <div class="p-8 flex flex-col items-center text-center">
                        <h2 class="text-lg font-bold text-brand-dark mb-6">
                            {{ displayName }}
                        </h2>
                        <div class="w-32 h-32 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 text-6xl overflow-hidden border-4 border-white shadow-lg">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>

                    <div class="pb-6">
                        <ul class="flex flex-col">
                            <li v-for="item in menuItems" :key="item.name">
                                <Link
                                    :href="item.href"
                                    class="block px-8 py-3.5 text-sm font-medium transition-colors border-l-4"
                                    :class="$page.url.startsWith(item.href)
                                    ? 'bg-brand-light border-brand-yellow text-brand-dark'
                                    : 'border-transparent text-gray-500 hover:bg-gray-50 hover:text-gray-700'"
                                >
                                    {{ item.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>


            <div class="w-full lg:w-3/4 flex flex-col gap-6">
                <slot />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
