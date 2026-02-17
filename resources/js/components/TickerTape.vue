<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { index, destroy, clearAll } from '@/actions/App/Http/Controllers/Api/CalculationController';

interface Calculation {
    id: number;
    expression: string;
    result: number | string;
    created_at: string;
}

interface Props {
    refreshTrigger?: number;
}

const props = defineProps<Props>();

const calculations = ref<Calculation[]>([]);
const isLoading = ref(false);
const error = ref('');

const fetchCalculations = async () => {
    try {
        isLoading.value = true;
        error.value = '';

        const response = await fetch(index.url());
        if (!response.ok) {
            throw new Error('Failed to fetch calculations');
        }

        const data = await response.json();
        calculations.value = data;
    } catch (e) {
        error.value = 'Failed to load calculation history';
        console.error(e);
    } finally {
        isLoading.value = false;
    }
};

const deleteCalculation = async (id: number) => {
    try {
        const response = await fetch(destroy.url(id), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to delete calculation');
        }

        calculations.value = calculations.value.filter((calc) => calc.id !== id);
    } catch (e) {
        error.value = 'Failed to delete calculation';
        console.error(e);
    }
};

const clearAllCalculations = async () => {
    if (!confirm('Are you sure you want to clear all calculations?')) {
        return;
    }

    try {
        const response = await fetch(clearAll.url(), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to clear calculations');
        }

        calculations.value = [];
    } catch (e) {
        error.value = 'Failed to clear calculations';
        console.error(e);
    }
};

onMounted(() => {
    fetchCalculations();
});

const watchRefreshTrigger = () => {
    if (props.refreshTrigger !== undefined) {
        fetchCalculations();
    }
};

// Watch for refresh trigger
const triggerWatch = ref(props.refreshTrigger);
const updateWatch = () => {
    if (props.refreshTrigger !== triggerWatch.value) {
        triggerWatch.value = props.refreshTrigger;
        watchRefreshTrigger();
    }
};

const formatResult = (result: number | string) => {
    // Convert to number if it's a string
    const numResult = typeof result === 'string' ? parseFloat(result) : result;
    
    if (!isFinite(numResult)) {
        return 'Error';
    }
    
    // Limit to 8 decimal places, removing trailing zeros
    let formatted = parseFloat(numResult.toFixed(8)).toString();

    // Limit display to 15 characters to prevent overflow
    if (formatted.length > 15) {
        formatted = formatted.substring(0, 15) + '...';
    }

    return formatted;
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
};

setInterval(updateWatch, 100);
</script>

<template>
    <div class="w-full rounded-xl bg-white shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-white">Calculation History</h2>
            <button
                v-if="calculations.length > 0"
                @click="clearAllCalculations"
                class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600 transition transform hover:scale-105 active:scale-95"
            >
                Clear All
            </button>
        </div>

        <div class="p-6">
            <!-- Error Message -->
            <div v-if="error" class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
                <p class="text-sm text-red-700 font-medium">{{ error }}</p>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="py-12 text-center">
                <p class="text-gray-500 font-medium">Loading calculations...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="calculations.length === 0" class="py-12 text-center">
                <p class="text-gray-400 font-medium">No calculations yet</p>
                <p class="text-gray-400 text-sm mt-1">Start by entering a calculation above!</p>
            </div>

            <!-- Calculations List -->
            <div v-else class="space-y-3">
                <div
                    v-for="calculation in calculations"
                    :key="calculation.id"
                    class="flex items-center justify-between p-4 rounded-lg bg-gradient-to-r from-slate-50 to-slate-100 border border-slate-200 hover:border-blue-300 hover:shadow-md transition group"
                >
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-gray-800 text-sm font-mono mb-1 truncate" :title="calculation.expression">
                            {{ calculation.expression }}
                        </div>
                        <div class="flex items-center gap-2 text-sm flex-wrap">
                            <span class="text-blue-600 font-bold">=</span>
                            <span class="text-gray-600 font-mono truncate" :title="formatResult(calculation.result)">{{ formatResult(calculation.result) }}</span>
                            <span class="text-gray-400 text-xs ml-auto flex-shrink-0 whitespace-nowrap">
                                {{ formatDate(calculation.created_at) }}
                            </span>
                        </div>
                    </div>
                    <button
                        @click="deleteCalculation(calculation.id)"
                        class="ml-2 rounded-lg bg-red-100 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-200 transition transform hover:scale-105 active:scale-95 flex-shrink-0 whitespace-nowrap"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
