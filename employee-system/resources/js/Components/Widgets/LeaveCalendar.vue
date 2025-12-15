<script setup>
import DashboardCard from '@/Components/DashboardCard.vue';
</script>

<template>
    <DashboardCard title="Employees on Leave" icon="fas fa-door-open" actionIcon="fas fa-expand-alt" :colSpan="2">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-3 text-sm font-bold text-brand-dark">
                <i class="fas fa-chevron-left text-xs text-gray-400 cursor-pointer hover:text-brand-dark p-1"></i>
                <span>Nov 30 - Dec 6, 2025</span>
                <i class="fas fa-chevron-right text-xs text-gray-400 cursor-pointer hover:text-brand-dark p-1"></i>
            </div>
            <div class="flex gap-1 bg-brand-light p-1 rounded-lg">
                <button class="bg-brand-yellow text-brand-dark px-3 py-1 rounded-md text-[11px] font-bold shadow-sm">Week</button>
                <button class="text-gray-500 hover:text-brand-dark px-3 py-1 text-[11px] font-medium transition-colors">Month</button>
            </div>
        </div>

        <div class="border border-gray-100 rounded-lg overflow-hidden">
            <table class="w-full border-collapse text-[11px] table-fixed">
                <thead>
                    <tr class="bg-white text-brand-dark">
                        <th class="border-b border-r border-gray-100 p-2 w-16 bg-gray-50"></th>
                        <th class="border-b border-r border-gray-100 p-2 font-semibold">Sun 11/30</th>
                        <th class="border-b border-r border-gray-100 p-2 font-semibold bg-brand-yellow/10">Mon 12/1</th>
                        <th class="border-b border-r border-gray-100 p-2 font-semibold">Tue 12/2</th>
                        <th class="border-b border-r border-gray-100 p-2 font-semibold">Wed 12/3</th>
                        <th class="border-b border-r border-gray-100 p-2 font-semibold">Thu 12/4</th>
                        <th class="border-b border-r border-gray-100 p-2 font-semibold">Fri 12/5</th>
                        <th class="border-b border-gray-100 p-2 font-semibold">Sat 12/6</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border-b border-r border-gray-100 p-2 text-right text-gray-400 text-[10px] align-top bg-gray-50">all-day</td>
                        <td class="border-b border-r border-gray-100 p-1 align-top h-12"></td>
                        <td class="border-b border-r border-gray-100 p-1 align-top h-12 bg-brand-yellow/10">
                            <div class="bg-red-400 text-white rounded-[3px] px-1.5 py-0.5 text-[9px] mb-1 truncate shadow-sm border border-red-500/20">Mark Ersan</div>
                        </td>
                        <td class="border-b border-r border-gray-100 p-1 align-top h-12"></td>
                        <td class="border-b border-r border-gray-100 p-1 align-top h-12"></td>
                        <td class="border-b border-r border-gray-100 p-1 align-top h-12">
                            <div class="bg-red-400 text-white rounded-[3px] px-1.5 py-0.5 text-[9px] mb-1 truncate shadow-sm">Angelo Day</div>
                            <div class="bg-brand-yellow text-brand-dark rounded-[3px] px-1.5 py-0.5 text-[9px] truncate shadow-sm border border-yellow-400/30">Angelo Day</div>
                        </td>
                        <td class="border-b border-r border-gray-100 p-1 align-top h-12"></td>
                        <td class="border-b border-gray-100 p-1 align-top h-12"></td>
                    </tr>
                    <tr v-for="time in ['6am', '7am', '8am', '9am']" :key="time">
                        <td class="border-b border-r border-gray-100 p-2 text-right text-gray-400 text-[10px] align-top bg-gray-50">{{ time }}</td>
                        <td class="border-b border-r border-gray-100 p-1 h-10"></td>
                        <td class="border-b border-r border-gray-100 p-1 h-10 bg-brand-yellow/10"></td>
                        <td class="border-b border-r border-gray-100 p-1 h-10"></td>
                        <td class="border-b border-r border-gray-100 p-1 h-10"></td>
                        <td class="border-b border-r border-gray-100 p-1 h-10"></td>
                        <td class="border-b border-r border-gray-100 p-1 h-10"></td>
                        <td class="border-b border-gray-100 p-1 h-10"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </DashboardCard>
</template>
