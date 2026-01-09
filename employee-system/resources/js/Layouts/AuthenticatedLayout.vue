<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const isSidebarCollapsed = ref(false);
const isUserDropdownOpen = ref(false);
const expandedMenuItems = ref({});
const searchTerm = ref('');

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

const toggleUserDropdown = () => {
    isUserDropdownOpen.value = !isUserDropdownOpen.value;
};

const closeUserDropdown = () => {
    isUserDropdownOpen.value = false;
};

const toggleMenuItem = (itemName) => {
    expandedMenuItems.value[itemName] = !expandedMenuItems.value[itemName];
};

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
        'directory.index': '/directory',
        'my-info.index': '/my-info',
        'my-info.personal': '/my-info/personal',
        'my-info.contact': '/my-info/contact',
        'my-info.password': '/my-info/password',
        'my-info.password.update': '/my-info/password',
        'leave-requests.index': '/leave-requests',
        'leave-requests.create': '/leave-requests/create',
        'leave-requests.store': '/leave-requests',
        'leave-requests.admin': '/leave-requests/admin',
        'leave-requests.approve': (id) => `/leave-requests/${id}/approve`,
        'leave-requests.decline': (id) => `/leave-requests/${id}/decline`,
        'leave-requests.cancel': (id) => `/leave-requests/${id}/cancel`,
        'overtime-requests.index': '/overtime-requests',
        'overtime-requests.store': '/overtime-requests',
        'overtime-requests.admin': '/overtime-requests/admin',
        'overtime-requests.approve': (id) => `/overtime-requests/${id}/approve`,
        'overtime-requests.decline': (id) => `/overtime-requests/${id}/decline`,
        'overtime-requests.cancel': (id) => `/overtime-requests/${id}/cancel`,
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
        { name: 'Dashboard', icon: 'fas fa-home', href: route('dashboard'), exact: true }, 
        { name: 'My Info', icon: 'far fa-user-circle', href: route('my-info.index'), exact: false },
        { name: 'Directory', icon: 'fas fa-address-book', href: route('directory.index'), exact: true },
        { 
            name: 'Leave Requests', 
            icon: 'fas fa-calendar-alt', 
            href: route('leave-requests.index'),
            exact: false,
            subItems: page.props.auth?.user?.is_admin ? [
                { 
                    name: 'Leave Management', 
                    icon: 'fas fa-user-shield', 
                    href: route('leave-requests.admin') 
                }
            ] : []
        },
        { 
            name: 'Overtime', 
            icon: 'fas fa-clock', 
            href: route('overtime-requests.index'),
            exact: false,
            subItems: page.props.auth?.user?.is_admin ? [
                { 
                    name: 'Overtime Management', 
                    icon: 'fas fa-user-clock', 
                    href: route('overtime-requests.admin') 
                }
            ] : []
        }, 
    ];
    
    return items;
});

const filteredNavItems = computed(() => {
    const term = searchTerm.value.trim().toLowerCase();
    if (!term) return navItems.value;

    return navItems.value
        .map(item => {
            const matchesParent = item.name.toLowerCase().includes(term);
            if (!item.subItems || item.subItems.length === 0) {
                return matchesParent ? item : null;
            }

            const matchingSubs = item.subItems.filter(sub => sub.name.toLowerCase().includes(term));
            if (matchesParent || matchingSubs.length > 0) {
                return { ...item, subItems: matchingSubs };
            }
            return null;
        })
        .filter(Boolean);
});

// Helper function to check if nav item is active
const isNavItemActive = (item) => {
    if (item.exact) {
        return page.url === item.href;
    }
    // For non-exact matches, check if current URL starts with the href
    // but exclude sub-item specific pages for parent items
    if (item.subItems && item.subItems.length > 0) {
        // Check if we're on a sub-item page
        const isOnSubItemPage = item.subItems.some(subItem => page.url === subItem.href);
        if (isOnSubItemPage) {
            return false; // Don't highlight parent if we're on a sub-item page
        }
    }
    return page.url.startsWith(item.href);
};
// ------------------------------------------------------------------


const headerText = computed(() => {
    if (page.url.startsWith(route('my-info.index'))) {
        return "PIM (Personnel Information Management) - Centralized database for managing employee records and job-related information.";
    }
    if (page.url.startsWith(route('directory.index'))) {
        return "Directory - Company-wide contact list of employees with job titles and departments.";
    }
    if (page.url.startsWith(route('leave-requests.index'))) {
        return "Leave Management - Track, view, and manage employee time-off requests and history.";
    }
    if (page.url.startsWith(route('overtime-requests.index'))) {
        return "Overtime Management - Submit, track, and manage overtime work requests and approvals.";
    }
    return "Dashboard - Overview of key HR information, announcements, and quick links to main modules.";
});
</script>

<template>
    <div class="min-h-screen bg-brand-light font-sans text-brand-dark">

        <header 
            class="fixed top-0 left-0 right-0 h-[70px] bg-brand-yellow z-10 flex items-center shadow-sm transition-all duration-300"
            :class="isSidebarCollapsed ? 'lg:pl-[80px]' : 'lg:pl-[260px]'"
        >
            <div class="w-full flex justify-between items-center px-8">
                <div class="text-[13px] font-medium hidden md:block text-brand-dark/80 truncate pr-4">
                    {{ headerText }}
                </div>

                <div class="flex items-center gap-3 text-[13px] font-semibold ml-auto text-brand-dark shrink-0 relative">
                    <button 
                        @click="toggleUserDropdown"
                        class="flex items-center gap-3 hover:opacity-80 transition-opacity cursor-pointer"
                    >
                        <div class="w-9 h-9 bg-white/30 backdrop-blur-sm rounded-full border border-white/50 flex items-center justify-center overflow-hidden shadow-sm">
                            <i class="fas fa-user text-white text-lg mt-1 opacity-90"></i>
                        </div>
                        <span>{{ page.props.auth?.user?.name || 'User' }} <i class="fas fa-caret-down ml-1 opacity-70"></i></span>
                    </button>

                    <!-- Dropdown Menu -->
                    <div 
                        v-if="isUserDropdownOpen"
                        @click.away="closeUserDropdown"
                        class="absolute top-full right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                    >
                        <Link
                            :href="route('my-info.password')"
                            @click="closeUserDropdown"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                        >
                            <i class="fas fa-key mr-2 text-gray-400"></i>
                            Change Password
                        </Link>
                    </div>
                </div>
            </div>
        </header>


        <aside 
            class="fixed top-0 bottom-0 left-0 bg-white z-30 hidden lg:flex flex-col py-8 rounded-r-[50px] shadow-[0_12px_40px_rgba(0,0,0,0.12)] overflow-visible transition-all duration-300"
            :class="isSidebarCollapsed ? 'w-[80px]' : 'w-[260px]'"
        >
            <!-- Toggle Button -->
            <button 
                @click="toggleSidebar"
                class="absolute -right-4 top-24 w-8 h-8 bg-brand-yellow rounded-full shadow-md flex items-center justify-center hover:bg-brand-yellow/90 transition-colors z-40"
            >
                <i class="fas text-white text-sm transition-transform duration-300" 
                   :class="isSidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
            </button>

            <div class="mb-5 px-6 py-4">
                <a href="https://purplebug.net/" target="_blank" rel="noopener noreferrer" class="block" v-if="!isSidebarCollapsed">
                    <img src="https://purplebug.beeconnectedsolutions.com/web/images/Logo_BeeConnected_20250725.png?v=1757391258012" 
                         alt="BeeConnected Logo" 
                         class="w-full h-auto max-w-[200px] mx-auto hover:opacity-80 transition-opacity cursor-pointer">
                </a>
                <a href="https://purplebug.net/" target="_blank" rel="noopener noreferrer" class="block" v-else>
                    <img src="https://purplebug.beeconnectedsolutions.com/web/images/Icon1_BeeConnected_20250725.png?v=1757391258012"
                         alt="BeeConnected Icon"
                         class="w-16 h-16 object-contain mx-auto hover:opacity-80 transition-opacity cursor-pointer">
                </a>
            </div>


            <div v-if="!isSidebarCollapsed" class="relative mb-6 px-6">
                <i class="fas fa-search absolute left-10 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input
                    v-model="searchTerm"
                    type="text"
                    placeholder="Search"
                    class="w-full bg-gray-50 rounded-full py-3 pl-10 pr-10 text-xs focus:outline-none focus:ring-1 focus:ring-brand-yellow transition-shadow"
                >
                <button
                    v-if="searchTerm"
                    @click="searchTerm = ''"
                    class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs"
                    aria-label="Clear search"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>


            <nav class="flex-1 overflow-y-auto custom-scrollbar">
                <ul class="space-y-1">
                    <li v-if="filteredNavItems.length === 0" class="px-6 py-3 text-xs text-gray-400">
                        No modules found
                    </li>
                    <li v-for="item in filteredNavItems" :key="item.name">
                        <!-- Main navigation item -->
                        <div class="flex items-center relative">
                            <Link
                                :href="item.href"
                                class="flex items-center py-4 text-sm font-medium transition-all duration-200 w-full group relative"
                                :class="[
                                    isNavItemActive(item)
                                        ? 'bg-brand-yellow text-gray-900 border-l-brand-dark rounded-r-3xl mr-3 border-l-4'
                                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-r-full mr-12 border-l-4 border-transparent',
                                    isSidebarCollapsed ? 'justify-center px-0 !mr-0 !border-l-0' : (item.subItems && item.subItems.length > 0 ? 'pl-8 pr-12' : 'pl-8 pr-6')
                                ]"
                            >
                                <div v-if="!isSidebarCollapsed" class="w-6 text-center mr-3">
                                    <i :class="[item.icon, 'text-base', isNavItemActive(item) ? 'text-gray-900' : 'text-gray-400']"></i>
                                </div>
                                <i v-else :class="[item.icon, 'text-lg', isNavItemActive(item) ? 'text-gray-900' : 'text-gray-400']"></i>
                                <span v-if="!isSidebarCollapsed" class="flex-1">{{ item.name }}</span>
                            </Link>
                            
                            <!-- Dropdown toggle button -->
                            <button
                                v-if="item.subItems && item.subItems.length > 0 && !isSidebarCollapsed"
                                @click.prevent="toggleMenuItem(item.name)"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors z-10"
                            >
                                <i 
                                    class="fas fa-chevron-down text-xs transition-transform duration-200"
                                    :class="{ 'rotate-180': expandedMenuItems[item.name] }"
                                ></i>
                            </button>
                        </div>
                        
                        <!-- Sub-items (if any) with slide animation -->
                        <transition
                            enter-active-class="transition-all duration-200 ease-out"
                            enter-from-class="opacity-0 -translate-y-2 max-h-0"
                            enter-to-class="opacity-100 translate-y-0 max-h-96"
                            leave-active-class="transition-all duration-200 ease-in"
                            leave-from-class="opacity-100 translate-y-0 max-h-96"
                            leave-to-class="opacity-0 -translate-y-2 max-h-0"
                        >
                            <ul v-if="item.subItems && item.subItems.length > 0 && !isSidebarCollapsed && expandedMenuItems[item.name]" class="overflow-hidden">
                                <li v-for="subItem in item.subItems" :key="subItem.name">
                                    <Link
                                        :href="subItem.href"
                                        class="flex items-center py-3 text-sm font-medium transition-all duration-200 w-full border-l-4 border-transparent group relative"
                                        :class="[
                                            $page.url === subItem.href 
                                                ? 'bg-brand-yellow text-gray-900 border-l-brand-dark rounded-r-3xl pl-16 pr-6 mr-3'
                                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-r-full pl-16 pr-6 mr-12'
                                        ]"
                                    >
                                        <div class="w-5 text-center mr-3">
                                            <i :class="[subItem.icon, 'text-sm', $page.url === subItem.href ? 'text-gray-900' : 'text-gray-400']"></i>
                                        </div>
                                        <span>{{ subItem.name }}</span>
                                    </Link>
                                </li>
                            </ul>
                        </transition>
                    </li>
                    
                    <li>
                        <Link
                            :href="route('logout')"  method="post"
                            as="button"
                            class="flex w-full items-center py-4 text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-600 transition-colors group relative"
                            :class="isSidebarCollapsed ? 'justify-center px-0' : 'pl-8 pr-6 mr-12 rounded-r-full border-l-4 border-transparent'"
                        >
                            <div v-if="!isSidebarCollapsed" class="w-6 text-center mr-3">
                                <i class="fas fa-sign-out-alt text-base text-gray-400"></i>
                            </div>
                            <i v-else class="fas fa-sign-out-alt text-lg text-gray-400"></i>
                            <span v-if="!isSidebarCollapsed">Logout</span>
                        </Link>
                    </li>
                </ul>
            </nav>
        </aside>


        <main 
            class="w-full pt-[100px] pb-8 pr-8 min-w-0 transition-all duration-300"
            :class="isSidebarCollapsed ? 'lg:pl-[calc(80px+2rem)]' : 'lg:pl-[calc(260px+2rem)]'"
        >
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