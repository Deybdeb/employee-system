<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

// Access the route helper from Ziggy via the global window or page props
// If not available globally, we can construct URLs manually
const route = (name, params = {}) => {
    // Try to use window.route if available (Ziggy plugin)
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    // Fallback: return a simple URI (won't work perfectly but prevents errors)
    const routes = {
        'dashboard': '/dashboard',
        'my-info.index': '/my-info',
        'leave-requests.index': '/leave-requests',
        'leave-requests.create': '/leave-requests/create',
        'leave-requests.store': '/leave-requests',
        'leave-requests.admin': '/leave-requests/admin',
        'leave-requests.approve': (id) => `/leave-requests/${id}/approve`,
        'leave-requests.decline': (id) => `/leave-requests/${id}/decline`,
        'leave-requests.cancel': (id) => `/leave-requests/${id}/cancel`,
        'logout': '/logout',
    };
    
    if (typeof routes[name] === 'function') {
        return routes[name](params);
    }
    return routes[name] || `/${name.replace(/\./g, '/')}`;
};

// --- THE FIX IS HERE: Using route() helper for all internal links ---
const navItems = computed(() => {
    const items = [
        { name: 'Dashboard', icon: 'fas fa-home', href: route('dashboard') }, 
        { name: 'My Info', icon: 'far fa-user-circle', href: route('my-info.index') }, 
        { name: 'Leave Requests', icon: 'fas fa-calendar-alt', href: route('leave-requests.index') }, 
    ];
    
    // Add HR Admin menu item for admin users
    if (page.props.auth?.user?.is_admin) {
        items.push({ 
            name: 'Leave Management (HR)', 
            icon: 'fas fa-user-shield', 
            href: route('leave-requests.admin') 
        });
    }
    
    return items;
});
// ------------------------------------------------------------------


const headerText = computed(() => {
    if (page.url.startsWith(route('my-info.index'))) {
        return "PIM (Personnel Information Management) - Centralized database for managing employee records and job-related information.";
    }
    // Also update the check here to use the named route for robustness
    if (page.url.startsWith(route('leave-requests.index'))) {
        return "Leave Management - Track, view, and manage employee time-off requests and history.";
    }
    return "Dashboard - Overview of key HR information, announcements, and quick links to main modules.";
});
</script>

<template>
    <div class="min-h-screen bg-brand-light font-sans text-brand-dark">

        <header class="fixed top-0 left-0 right-0 h-[70px] bg-brand-yellow z-10 flex items-center shadow-sm lg:pl-[260px] transition-all duration-300">
            <div class="w-full flex justify-between items-center px-8">
                <div class="text-[13px] font-medium hidden md:block text-brand-dark/80 truncate pr-4">
                    {{ headerText }}
                </div>

                <div class="flex items-center gap-3 text-[13px] font-semibold ml-auto text-brand-dark shrink-0">
                    <div class="w-9 h-9 bg-white/30 backdrop-blur-sm rounded-full border border-white/50 flex items-center justify-center overflow-hidden shadow-sm">
                        <i class="fas fa-user text-white text-lg mt-1 opacity-90"></i>
                    </div>
                    <span>{{ page.props.auth?.user?.name || 'User' }} <i class="fas fa-caret-down ml-1 opacity-70"></i></span>
                </div>
            </div>
        </header>


        <aside class="fixed top-0 bottom-0 left-0 w-[260px] bg-white z-30 hidden lg:flex flex-col py-8 rounded-r-[50px] shadow-[0_12px_40px_rgba(0,0,0,0.12)] overflow-hidden">

            <div class="mb-5 px-6 py-4">
                <a href="https://purplebug.net/" target="_blank" rel="noopener noreferrer" class="block">
                    <img src="https://purplebug.beeconnectedsolutions.com/web/images/Logo_BeeConnected_20250725.png?v=1757391258012" 
                         alt="BeeConnected Logo" 
                         class="w-full h-auto max-w-[200px] mx-auto hover:opacity-80 transition-opacity cursor-pointer">
                </a>
            </div>


            <div class="relative mb-6 px-6">
                <i class="fas fa-search absolute left-10 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" placeholder="Search" class="w-full bg-gray-50 rounded-full py-3 pl-10 pr-4 text-xs focus:outline-none focus:ring-1 focus:ring-brand-yellow transition-shadow">
            </div>


            <nav class="flex-1 overflow-y-auto custom-scrollbar">
                <ul class="space-y-1">
                    <li v-for="item in navItems" :key="item.name">
                        <Link
                            :href="item.href"
                            class="flex items-center py-4 pl-8 pr-6 text-sm font-medium transition-all duration-200 w-full rounded-r-full mr-12 border-l-4 border-transparent"
                            :class="$page.url.startsWith(item.href) // Checks if URL starts with item.href (e.g., /leave-requests or /leave-requests/create)
                                ? 'bg-brand-yellow text-gray-900 border-l-brand-dark'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                        >
                            <div class="w-6 text-center mr-3">
                                <i :class="[item.icon, 'text-base', $page.url.startsWith(item.href) ? 'text-gray-900' : 'text-gray-400']"></i>
                            </div>
                            {{ item.name }}
                        </Link>
                    </li>
                    
                    <li>
                        <Link
                            :href="route('logout')"  method="post"
                            as="button"
                            class="flex w-full items-center py-4 pl-8 pr-6 text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-600 transition-colors rounded-r-full mr-12 border-l-4 border-transparent"
                        >
                            <div class="w-6 text-center mr-3">
                                <i class="fas fa-sign-out-alt text-base text-gray-400"></i>
                            </div>
                            Logout
                        </Link>
                    </li>
                </ul>
            </nav>
        </aside>


        <main class="w-full lg:pl-[calc(260px+2rem)] pt-[100px] pb-8 pr-8 min-w-0 transition-all duration-300">
            <div class="max-w-6xl w-full mx-auto">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #eee; border-radius: 2px; }
.custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #ccc; }
</style>