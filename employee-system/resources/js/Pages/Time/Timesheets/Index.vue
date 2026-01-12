<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

// Access global route helper
const route = window.route;

const props = defineProps({
    timesheet: Object,
    timesheets: Array,
    currentWeekStart: String,
    currentWeekEnd: String,
    serverTime: String,
});

const activeTab = ref('my-timesheets');
const isEditing = ref(false);

// Generate week dates array
const weekDates = computed(() => {
    const dates = [];
    const start = new Date(props.currentWeekStart);
    for (let i = 0; i < 7; i++) {
        const date = new Date(start);
        date.setDate(start.getDate() + i);
        dates.push({
            date: date.toISOString().split('T')[0],
            dayName: date.toLocaleDateString('en-US', { weekday: 'short' }),
            dayNumber: date.getDate(),
        });
    }
    return dates;
});

// Entries organized by project/activity row
const entries = ref([]);

// Initialize entries from timesheet
const initializeEntries = () => {
    if (props.timesheet?.entries?.length > 0) {
        // Group entries by project/activity
        const grouped = {};
        props.timesheet.entries.forEach(entry => {
            const key = `${entry.project || ''}-${entry.activity || ''}`;
            if (!grouped[key]) {
                grouped[key] = {
                    project: entry.project || '',
                    activity: entry.activity || '',
                    hours: {},
                };
            }
            grouped[key].hours[entry.date] = entry.hours;
        });
        entries.value = Object.values(grouped);
    }
    
    // Ensure at least one empty row
    if (entries.value.length === 0) {
        addRow();
    }
};

const addRow = () => {
    const newRow = {
        project: '',
        activity: '',
        hours: {},
    };
    weekDates.value.forEach(d => {
        newRow.hours[d.date] = 0;
    });
    entries.value.push(newRow);
};

const removeRow = (index) => {
    if (entries.value.length > 1) {
        entries.value.splice(index, 1);
    }
};

const getRowTotal = (row) => {
    return Object.values(row.hours).reduce((sum, h) => sum + (parseFloat(h) || 0), 0).toFixed(2);
};

const getDayTotal = (date) => {
    return entries.value.reduce((sum, row) => sum + (parseFloat(row.hours[date]) || 0), 0).toFixed(2);
};

const getGrandTotal = computed(() => {
    return entries.value.reduce((sum, row) => {
        return sum + Object.values(row.hours).reduce((s, h) => s + (parseFloat(h) || 0), 0);
    }, 0).toFixed(2);
});

// Form submission
const form = useForm({
    timesheet_id: props.timesheet?.id,
    entries: [],
});

const saveTimesheet = () => {
    const flatEntries = [];
    entries.value.forEach(row => {
        weekDates.value.forEach(d => {
            if (parseFloat(row.hours[d.date]) > 0 || row.project || row.activity) {
                flatEntries.push({
                    project: row.project,
                    activity: row.activity,
                    date: d.date,
                    hours: parseFloat(row.hours[d.date]) || 0,
                });
            }
        });
    });

    form.entries = flatEntries;
    form.timesheet_id = props.timesheet.id;

    form.post(route('timesheets.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

const submitTimesheet = () => {
    if (confirm('Are you sure you want to submit this timesheet for approval?')) {
        router.post(route('timesheets.submit', props.timesheet.id), {}, {
            preserveScroll: true,
        });
    }
};

// Week navigation
const goToPreviousWeek = () => {
    const date = new Date(props.currentWeekStart);
    date.setDate(date.getDate() - 7);
    router.get(route('timesheets.index'), { week_start: date.toISOString().split('T')[0] });
};

const goToNextWeek = () => {
    const date = new Date(props.currentWeekStart);
    date.setDate(date.getDate() + 7);
    router.get(route('timesheets.index'), { week_start: date.toISOString().split('T')[0] });
};

const formatPeriod = computed(() => {
    const start = new Date(props.currentWeekStart);
    const end = new Date(props.currentWeekEnd);
    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return `${start.toLocaleDateString('en-CA')} to ${end.toLocaleDateString('en-CA')}`;
});

const getStatusClass = (status) => {
    const classes = {
        'draft': 'bg-gray-100 text-gray-700',
        'submitted': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
};

const canEdit = computed(() => {
    return props.timesheet?.status === 'draft' || props.timesheet?.status === 'rejected';
});

const canSubmit = computed(() => {
    return props.timesheet?.status === 'draft' || props.timesheet?.status === 'rejected';
});

// Initialize on mount
initializeEntries();

// Watch for timesheet changes
watch(() => props.timesheet, () => {
    initializeEntries();
}, { deep: true });
</script>

<template>
    <AuthenticatedLayout>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <!-- Header -->
            <div class="border-b border-gray-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <i class="fas fa-clock text-brand-yellow"></i>
                    Time
                </h2>
                <p class="text-sm text-gray-500 mt-1">Module for tracking attendance, time sheets, and working hours.</p>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-100 px-8 pt-4">
                <div class="flex gap-2">
                    <button
                        @click="activeTab = 'my-timesheets'"
                        :class="[
                            'px-6 py-3 rounded-t-xl text-sm transition-all',
                            activeTab === 'my-timesheets'
                                ? 'bg-brand-yellow text-brand-dark font-bold shadow-sm'
                                : 'text-gray-500 hover:text-brand-dark font-medium'
                        ]"
                    >
                        My Timesheets
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <!-- My Timesheet Section -->
                <div v-if="activeTab === 'my-timesheets'">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">My Timesheet</h3>
                        
                        <!-- Week Navigation -->
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-600">Timesheet Period</span>
                            <div class="flex items-center gap-2">
                                <button 
                                    @click="goToPreviousWeek"
                                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                >
                                    <i class="fas fa-chevron-left text-gray-600"></i>
                                </button>
                                <div class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium min-w-[200px] text-center">
                                    {{ formatPeriod }}
                                </div>
                                <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="far fa-calendar text-gray-600"></i>
                                </button>
                                <button 
                                    @click="goToNextWeek"
                                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                >
                                    <i class="fas fa-chevron-right text-gray-600"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Timesheet Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-4 px-3 text-sm font-semibold text-blue-600 w-40">Project</th>
                                    <th class="text-left py-4 px-3 text-sm font-semibold text-blue-600 w-40">Activity</th>
                                    <th 
                                        v-for="day in weekDates" 
                                        :key="day.date"
                                        class="text-center py-4 px-2 text-sm font-semibold text-gray-700 w-20"
                                    >
                                        <div>{{ day.dayNumber }}</div>
                                        <div class="text-xs text-gray-500">{{ day.dayName }}</div>
                                    </th>
                                    <th class="text-center py-4 px-3 text-sm font-semibold text-gray-700 w-20">Total</th>
                                    <th v-if="isEditing" class="w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data Rows -->
                                <tr v-for="(row, index) in entries" :key="index" class="border-b border-gray-100">
                                    <td class="py-3 px-3">
                                        <input 
                                            v-if="isEditing"
                                            v-model="row.project"
                                            type="text"
                                            placeholder="Project name"
                                            class="w-full px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-brand-yellow focus:border-brand-yellow"
                                        />
                                        <span v-else class="text-sm text-gray-700">{{ row.project || '-' }}</span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <input 
                                            v-if="isEditing"
                                            v-model="row.activity"
                                            type="text"
                                            placeholder="Activity"
                                            class="w-full px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-brand-yellow focus:border-brand-yellow"
                                        />
                                        <span v-else class="text-sm text-gray-700">{{ row.activity || '-' }}</span>
                                    </td>
                                    <td v-for="day in weekDates" :key="day.date" class="py-3 px-2 text-center">
                                        <input 
                                            v-if="isEditing"
                                            v-model.number="row.hours[day.date]"
                                            type="number"
                                            min="0"
                                            max="24"
                                            step="0.5"
                                            class="w-16 px-2 py-1 border border-gray-200 rounded text-sm text-center focus:ring-1 focus:ring-brand-yellow focus:border-brand-yellow"
                                        />
                                        <span v-else class="text-sm text-gray-600">{{ row.hours[day.date] || 0 }}</span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-medium text-gray-900">
                                        {{ getRowTotal(row) }}
                                    </td>
                                    <td v-if="isEditing" class="py-3 px-2">
                                        <button 
                                            @click="removeRow(index)"
                                            class="text-red-500 hover:text-red-700"
                                            :disabled="entries.length === 1"
                                        >
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- No Records Row -->
                                <tr v-if="entries.length === 0 || (entries.length === 1 && !entries[0].project && !entries[0].activity && getRowTotal(entries[0]) === '0.00')">
                                    <td colspan="10" class="py-8 text-center text-blue-500 text-sm">
                                        No Records Found
                                    </td>
                                </tr>

                                <!-- Add Row Button -->
                                <tr v-if="isEditing">
                                    <td colspan="10" class="py-3 px-3">
                                        <button 
                                            @click="addRow"
                                            class="text-brand-yellow hover:text-yellow-600 text-sm font-medium flex items-center gap-2"
                                        >
                                            <i class="fas fa-plus"></i> Add Row
                                        </button>
                                    </td>
                                </tr>

                                <!-- Totals Row -->
                                <tr class="bg-gray-50">
                                    <td colspan="2" class="py-4 px-3 font-semibold text-gray-700">Daily Total</td>
                                    <td v-for="day in weekDates" :key="day.date" class="py-4 px-2 text-center font-medium text-gray-700">
                                        {{ getDayTotal(day.date) }}
                                    </td>
                                    <td class="py-4 px-3 text-center font-bold text-gray-900">
                                        {{ getGrandTotal }}
                                    </td>
                                    <td v-if="isEditing"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Status and Actions -->
                    <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Status:</span>
                            <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatusClass(timesheet?.status)]">
                                {{ timesheet?.status === 'draft' ? 'Not Submitted' : 
                                   timesheet?.status === 'submitted' ? 'Pending Approval' :
                                   timesheet?.status === 'approved' ? 'Approved' :
                                   timesheet?.status === 'rejected' ? 'Rejected' : 'Unknown' }}
                            </span>
                            <span v-if="timesheet?.rejection_reason" class="text-sm text-red-600 ml-2">
                                ({{ timesheet.rejection_reason }})
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <template v-if="!isEditing">
                                <button 
                                    v-if="canEdit"
                                    @click="isEditing = true"
                                    class="px-6 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                                >
                                    Edit
                                </button>
                                <button 
                                    v-if="canSubmit"
                                    @click="submitTimesheet"
                                    class="px-6 py-2 bg-brand-dark text-white rounded-full text-sm font-medium hover:bg-gray-800 transition-colors"
                                >
                                    Submit
                                </button>
                            </template>
                            <template v-else>
                                <button 
                                    @click="isEditing = false; initializeEntries();"
                                    class="px-6 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button 
                                    @click="saveTimesheet"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-brand-yellow text-brand-dark rounded-full text-sm font-bold hover:bg-yellow-400 transition-colors disabled:opacity-50"
                                >
                                    Save
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
