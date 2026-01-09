<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    requests: Array,
});

const activeTab = ref('apply');

// Time picker state
const showStartTimePicker = ref(false);
const showEndTimePicker = ref(false);

const startHour = ref(1);
const startMinute = ref(0);
const startPeriod = ref('AM');
const endHour = ref(1);
const endMinute = ref(0);
const endPeriod = ref('PM');

const form = useForm({
    date: '',
    start_time: '',
    end_time: '',
    reason: '',
});

// Computed formatted time displays
const startTimeDisplay = computed(() => {
    if (form.start_time) {
        const [h, m] = form.start_time.split(':');
        const hour = parseInt(h);
        const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
        const period = hour >= 12 ? 'PM' : 'AM';
        return `${displayHour.toString().padStart(2, '0')}:${m} ${period}`;
    }
    return '--:--';
});

const endTimeDisplay = computed(() => {
    if (form.end_time) {
        const [h, m] = form.end_time.split(':');
        const hour = parseInt(h);
        const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
        const period = hour >= 12 ? 'PM' : 'AM';
        return `${displayHour.toString().padStart(2, '0')}:${m} ${period}`;
    }
    return '--:--';
});

// Update form time from picker values
const updateStartTime = () => {
    let hour = startHour.value;
    if (startPeriod.value === 'PM' && hour !== 12) hour += 12;
    if (startPeriod.value === 'AM' && hour === 12) hour = 0;
    form.start_time = `${hour.toString().padStart(2, '0')}:${startMinute.value.toString().padStart(2, '0')}`;
};

const updateEndTime = () => {
    let hour = endHour.value;
    if (endPeriod.value === 'PM' && hour !== 12) hour += 12;
    if (endPeriod.value === 'AM' && hour === 12) hour = 0;
    form.end_time = `${hour.toString().padStart(2, '0')}:${endMinute.value.toString().padStart(2, '0')}`;
};

// Increment/decrement functions
const incrementStartHour = () => {
    startHour.value = startHour.value === 12 ? 1 : startHour.value + 1;
    updateStartTime();
};

const decrementStartHour = () => {
    startHour.value = startHour.value === 1 ? 12 : startHour.value - 1;
    updateStartTime();
};

const incrementStartMinute = () => {
    startMinute.value = startMinute.value === 59 ? 0 : startMinute.value + 1;
    updateStartTime();
};

const decrementStartMinute = () => {
    startMinute.value = startMinute.value === 0 ? 59 : startMinute.value - 1;
    updateStartTime();
};

const incrementEndHour = () => {
    endHour.value = endHour.value === 12 ? 1 : endHour.value + 1;
    updateEndTime();
};

const decrementEndHour = () => {
    endHour.value = endHour.value === 1 ? 12 : endHour.value - 1;
    updateEndTime();
};

const incrementEndMinute = () => {
    endMinute.value = endMinute.value === 59 ? 0 : endMinute.value + 1;
    updateEndTime();
};

const decrementEndMinute = () => {
    endMinute.value = endMinute.value === 0 ? 59 : endMinute.value - 1;
    updateEndTime();
};

const submit = () => {
    form.post(route('overtime-requests.store'), {
        onSuccess: () => {
            form.reset();
            activeTab.value = 'my-overtime';
        },
    });
};

const calculatedHours = computed(() => {
    if (!form.start_time || !form.end_time) return '0.00';
    
    const [startHour, startMin] = form.start_time.split(':').map(Number);
    const [endHour, endMin] = form.end_time.split(':').map(Number);
    
    const startMinutes = startHour * 60 + startMin;
    const endMinutes = endHour * 60 + endMin;
    
    if (endMinutes <= startMinutes) return '0.00';
    
    const diffMinutes = endMinutes - startMinutes;
    const hours = (diffMinutes / 60).toFixed(2);
    
    return hours;
});

const getStatusClass = (status) => {
    const classes = {
        'Pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'Approved': 'bg-green-100 text-green-800 border-green-200',
        'Rejected': 'bg-red-100 text-red-800 border-red-200',
        'Cancelled': 'bg-gray-100 text-gray-800 border-gray-200',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const cancelRequest = (id) => {
    if (confirm('Are you sure you want to cancel this overtime request?')) {
        form.post(route('overtime-requests.cancel', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <!-- Header Section -->
            <div class="border-b border-gray-100 px-8 py-8">
                <h2 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <i class="fas fa-clock text-brand-yellow text-2xl"></i>
                    Overtime
                </h2>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-100 px-8 pt-6">
                <div class="flex gap-2">
                    <button
                        @click="activeTab = 'apply'"
                        :class="[
                            'px-8 py-3 rounded-t-2xl text-sm transition-all border-b-2',
                            activeTab === 'apply' 
                                ? 'bg-brand-yellow text-brand-dark font-bold shadow-md border-brand-dark' 
                                : 'text-gray-500 hover:text-brand-dark font-medium border-gray-200'
                        ]"
                    >
                        Apply
                    </button>
                    <button
                        @click="activeTab = 'my-overtime'"
                        :class="[
                            'px-8 py-3 rounded-t-2xl text-sm transition-all border-b-2',
                            activeTab === 'my-overtime' 
                                ? 'bg-brand-yellow text-brand-dark font-bold shadow-md border-brand-dark' 
                                : 'text-gray-500 hover:text-brand-dark font-medium border-gray-200'
                        ]"
                    >
                        My Overtime
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-8 space-y-6">
                <!-- Apply Overtime Tab -->
                <div v-if="activeTab === 'apply'" class="max-w-2xl">
                    <h3 class="text-2xl font-bold text-gray-900 mb-8">Apply Overtime</h3>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Date -->
                        <div>
                            <label for="date" class="block text-sm font-semibold text-gray-700 mb-3">
                                Date<span class="text-red-500">*</span>
                            </label>
                            <input
                                id="date"
                                type="date"
                                v-model="form.date"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all bg-white text-gray-900"
                                :class="{ 'border-red-500 focus:ring-red-500': form.errors.date }"
                                required
                            >
                            <p v-if="form.errors.date" class="mt-2 text-sm text-red-600">{{ form.errors.date }}</p>
                        </div>

                        <!-- Time Container with Two Columns -->
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Start Time -->
                            <div class="relative">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    Start Time<span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        :value="startTimeDisplay"
                                        @focus="showStartTimePicker = true"
                                        readonly
                                        class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all bg-white text-gray-900 cursor-pointer"
                                        :class="{ 'border-red-500 focus:ring-red-500': form.errors.start_time }"
                                        placeholder="--:--"
                                    >
                                    <i class="fas fa-clock absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <p v-if="form.errors.start_time" class="mt-2 text-sm text-red-600">{{ form.errors.start_time }}</p>
                                
                                <!-- Time Picker Dropdown -->
                                <div v-if="showStartTimePicker" class="absolute top-full mt-2 right-0 bg-white rounded-2xl shadow-2xl border border-gray-200 p-6 z-50">
                                    <div class="flex items-center gap-4">
                                        <!-- Hour -->
                                        <div class="flex flex-col items-center">
                                            <button type="button" @click="incrementStartHour" class="text-gray-400 hover:text-gray-600 mb-2">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <input
                                                v-model.number="startHour"
                                                @input="updateStartTime"
                                                type="number"
                                                min="1"
                                                max="12"
                                                class="w-16 text-center text-2xl font-semibold border border-gray-200 rounded-xl py-2 focus:outline-none focus:ring-2 focus:ring-brand-yellow"
                                            >
                                            <button type="button" @click="decrementStartHour" class="text-gray-400 hover:text-gray-600 mt-2">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                        
                                        <span class="text-2xl font-bold text-gray-400">:</span>
                                        
                                        <!-- Minute -->
                                        <div class="flex flex-col items-center">
                                            <button type="button" @click="incrementStartMinute" class="text-gray-400 hover:text-gray-600 mb-2">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <input
                                                v-model.number="startMinute"
                                                @input="updateStartTime"
                                                type="number"
                                                min="0"
                                                max="59"
                                                class="w-16 text-center text-2xl font-semibold border border-gray-200 rounded-xl py-2 focus:outline-none focus:ring-2 focus:ring-brand-yellow"
                                            >
                                            <button type="button" @click="decrementStartMinute" class="text-gray-400 hover:text-gray-600 mt-2">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- AM/PM -->
                                        <div class="flex flex-col gap-2 ml-2">
                                            <button
                                                type="button"
                                                @click="startPeriod = 'AM'; updateStartTime()"
                                                :class="startPeriod === 'AM' ? 'bg-brand-yellow text-brand-dark' : 'bg-gray-300 text-gray-600'"
                                                class="px-5 py-2 rounded-xl font-bold text-sm transition-colors"
                                            >
                                                AM
                                            </button>
                                            <button
                                                type="button"
                                                @click="startPeriod = 'PM'; updateStartTime()"
                                                :class="startPeriod === 'PM' ? 'bg-gray-600 text-white' : 'bg-gray-300 text-gray-600'"
                                                class="px-5 py-2 rounded-xl font-bold text-sm transition-colors"
                                            >
                                                PM
                                            </button>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="showStartTimePicker = false"
                                        class="mt-4 w-full bg-brand-yellow text-brand-dark font-bold py-2 rounded-xl hover:bg-yellow-500 transition-colors"
                                    >
                                        Done
                                    </button>
                                </div>
                            </div>

                            <!-- End Time -->
                            <div class="relative">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    End Time<span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        :value="endTimeDisplay"
                                        @focus="showEndTimePicker = true"
                                        readonly
                                        class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all bg-white text-gray-900 cursor-pointer"
                                        :class="{ 'border-red-500 focus:ring-red-500': form.errors.end_time }"
                                        placeholder="--:--"
                                    >
                                    <i class="fas fa-clock absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <p v-if="form.errors.end_time" class="mt-2 text-sm text-red-600">{{ form.errors.end_time }}</p>
                                
                                <!-- Time Picker Dropdown -->
                                <div v-if="showEndTimePicker" class="absolute top-full mt-2 right-0 bg-white rounded-2xl shadow-2xl border border-gray-200 p-6 z-50">
                                    <div class="flex items-center gap-4">
                                        <!-- Hour -->
                                        <div class="flex flex-col items-center">
                                            <button type="button" @click="incrementEndHour" class="text-gray-400 hover:text-gray-600 mb-2">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <input
                                                v-model.number="endHour"
                                                @input="updateEndTime"
                                                type="number"
                                                min="1"
                                                max="12"
                                                class="w-16 text-center text-2xl font-semibold border border-gray-200 rounded-xl py-2 focus:outline-none focus:ring-2 focus:ring-brand-yellow"
                                            >
                                            <button type="button" @click="decrementEndHour" class="text-gray-400 hover:text-gray-600 mt-2">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                        
                                        <span class="text-2xl font-bold text-gray-400">:</span>
                                        
                                        <!-- Minute -->
                                        <div class="flex flex-col items-center">
                                            <button type="button" @click="incrementEndMinute" class="text-gray-400 hover:text-gray-600 mb-2">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <input
                                                v-model.number="endMinute"
                                                @input="updateEndTime"
                                                type="number"
                                                min="0"
                                                max="59"
                                                class="w-16 text-center text-2xl font-semibold border border-gray-200 rounded-xl py-2 focus:outline-none focus:ring-2 focus:ring-brand-yellow"
                                            >
                                            <button type="button" @click="decrementEndMinute" class="text-gray-400 hover:text-gray-600 mt-2">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- AM/PM -->
                                        <div class="flex flex-col gap-2 ml-2">
                                            <button
                                                type="button"
                                                @click="endPeriod = 'AM'; updateEndTime()"
                                                :class="endPeriod === 'AM' ? 'bg-brand-yellow text-brand-dark' : 'bg-gray-300 text-gray-600'"
                                                class="px-5 py-2 rounded-xl font-bold text-sm transition-colors"
                                            >
                                                AM
                                            </button>
                                            <button
                                                type="button"
                                                @click="endPeriod = 'PM'; updateEndTime()"
                                                :class="endPeriod === 'PM' ? 'bg-gray-600 text-white' : 'bg-gray-300 text-gray-600'"
                                                class="px-5 py-2 rounded-xl font-bold text-sm transition-colors"
                                            >
                                                PM
                                            </button>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="showEndTimePicker = false"
                                        class="mt-4 w-full bg-brand-yellow text-brand-dark font-bold py-2 rounded-xl hover:bg-yellow-500 transition-colors"
                                    >
                                        Done
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Calculated Hours Display -->
                        <div v-if="form.start_time && form.end_time" class="bg-gradient-to-br from-brand-yellow/5 to-brand-yellow/10 border border-brand-yellow/20 rounded-xl p-6">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-700">Total Hours:</span>
                                <span class="text-4xl font-bold text-brand-dark">{{ calculatedHours }}</span>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label for="reason" class="block text-sm font-semibold text-gray-700 mb-3">
                                Reason<span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="reason"
                                v-model="form.reason"
                                rows="5"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all bg-white text-gray-900 resize-none"
                                :class="{ 'border-red-500 focus:ring-red-500': form.errors.reason }"
                                placeholder="Please provide a reason for overtime..."
                                required
                            ></textarea>
                            <p v-if="form.errors.reason" class="mt-2 text-sm text-red-600">{{ form.errors.reason }}</p>
                        </div>

                        <!-- Required Note -->
                        <p class="text-sm text-gray-500 font-medium">* Required</p>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-gray-900 text-white px-10 py-3 rounded-full font-bold hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl"
                            >
                                {{ form.processing ? 'Submitting...' : 'Apply' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- My Overtime Tab -->
                <div v-else-if="activeTab === 'my-overtime'">
                    <h3 class="text-2xl font-bold text-gray-900 mb-8">My Overtime</h3>
                    
                    <div v-if="requests.length === 0" class="text-center py-16 bg-gray-50 rounded-2xl border border-gray-200">
                        <i class="fas fa-clock text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg font-medium mb-4">No overtime requests found.</p>
                        <button
                            @click="activeTab = 'apply'"
                            class="text-brand-yellow hover:text-yellow-600 font-semibold transition-colors"
                        >
                            Submit your first overtime request
                        </button>
                    </div>

                    <div v-else class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm">
                        <table class="w-full text-sm bg-white">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Date</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Start</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">End</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Hours</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Status</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="request in requests" :key="request.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ request.date }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ request.start_time }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ request.end_time }}</td>
                                    <td class="px-6 py-4 font-bold text-brand-dark">{{ request.hours }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="getStatusClass(request.status)"
                                            class="inline-flex px-3 py-1.5 text-xs font-semibold rounded-full border"
                                        >
                                            {{ request.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button
                                            v-if="request.status === 'Pending'"
                                            @click="cancelRequest(request.id)"
                                            class="text-red-600 hover:text-red-800 font-semibold text-sm hover:underline transition-colors"
                                        >
                                            Cancel
                                        </button>
                                        <span v-else class="text-gray-400 text-sm">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
