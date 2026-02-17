<script setup lang="ts">
import { ref, computed } from 'vue';
import { store } from '@/actions/App/Http/Controllers/Api/CalculationController';

interface Props {
    onCalculationAdded?: () => void;
}

const props = defineProps<Props>();

const expression = ref('');
const result = ref<number | null>(null);
const error = ref('');
const isLoading = ref(false);

const displayExpression = computed(() => {
    if (expression.value === '') return '0';
    // Limit to 30 characters with ellipsis if too long
    return expression.value.length > 30 ? expression.value.substring(0, 30) + '...' : expression.value;
});

const displayResult = computed(() => {
    if (result.value === null) return '0';
    // Format to max 10 decimal places and remove trailing zeros
    const formatted = parseFloat(result.value.toFixed(10)).toString();
    // Limit display to 20 characters to prevent overflow
    return formatted.length > 20 ? formatted.substring(0, 20) + '...' : formatted;
});

const appendToExpression = (value: string) => {
    expression.value += value;
    error.value = '';
};

const clearExpression = () => {
    expression.value = '';
    result.value = null;
    error.value = '';
};

const backspace = () => {
    expression.value = expression.value.slice(0, -1);
    error.value = '';
};

const calculateResult = async () => {
    if (!expression.value.trim()) {
        error.value = 'Please enter an expression';
        return;
    }

    try {
        isLoading.value = true;
        error.value = '';

        // Prepare the expression for evaluation
        // Replace ^ with ** (exponentiation)
        let evaluableExpression = expression.value.replace(/\^/g, '**');

        // Create a safe evaluation scope with Math functions
        const sqrt = Math.sqrt;
        const pow = Math.pow;
        const sin = Math.sin;
        const cos = Math.cos;
        const tan = Math.tan;
        const log = Math.log;
        const log10 = Math.log10;
        const exp = Math.exp;
        const abs = Math.abs;
        const PI = Math.PI;
        const E = Math.E;

        // Evaluate the expression
        const calculatedResult = Function(
            'sqrt, pow, sin, cos, tan, log, log10, exp, abs, PI, E',
            '"use strict"; return (' + evaluableExpression + ')'
        )(sqrt, pow, sin, cos, tan, log, log10, exp, abs, PI, E);

        if (typeof calculatedResult !== 'number' || isNaN(calculatedResult)) {
            throw new Error('Invalid expression result');
        }

        // If the result is Infinity, show error
        if (!isFinite(calculatedResult)) {
            throw new Error('Division by zero or infinite result');
        }

        result.value = calculatedResult;

        const response = await fetch(store.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                expression: expression.value,
                result: calculatedResult,
            }),
        });

        if (!response.ok) {
            const data = await response.json();
            if (data.errors) {
                error.value = Object.values(data.errors).flat().join(', ');
            } else {
                error.value = 'Failed to save calculation';
            }
            result.value = null;
            return;
        }

        expression.value = '';
        props.onCalculationAdded?.();
    } catch (e) {
        error.value = 'Invalid expression or calculation error';
        result.value = null;
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="w-full max-w-sm rounded-xl bg-white shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
            <h2 class="text-2xl font-bold text-white">Calculator</h2>
        </div>

        <div class="p-6 space-y-6">
            <!-- Display Section -->
            <div class="space-y-3">
                <div class="bg-gray-50 rounded-lg p-4 text-right border border-gray-200">
                    <div class="text-sm text-gray-500 mb-1 font-medium">Expression</div>
                    <div class="text-lg text-gray-800 font-mono break-words min-h-8 max-h-16 overflow-hidden">{{ displayExpression }}</div>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 text-right border-2 border-blue-200">
                    <div class="text-sm text-gray-500 mb-1 font-medium">Result</div>
                    <div class="text-4xl font-bold text-blue-600 font-mono overflow-hidden break-words min-h-12">{{ displayResult }}</div>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="rounded-lg bg-red-50 border border-red-200 p-4">
                <p class="text-sm text-red-700 font-medium">{{ error }}</p>
            </div>

            <!-- Buttons Grid -->
            <div class="space-y-3">
                <!-- Row 1: Clear & Basic Operations -->
                <div class="grid grid-cols-4 gap-2">
                    <button
                        @click="clearExpression"
                        class="py-4 rounded-lg font-bold text-white bg-red-500 hover:bg-red-600 active:bg-red-700 transition transform hover:scale-105"
                        title="Clear"
                    >
                        C
                    </button>
                    <button
                        @click="backspace"
                        class="py-4 rounded-lg font-bold text-white bg-amber-500 hover:bg-amber-600 active:bg-amber-700 transition transform hover:scale-105"
                        title="Backspace"
                    >
                        ←
                    </button>
                    <button
                        @click="appendToExpression('(')"
                        class="py-4 rounded-lg font-bold text-gray-700 bg-gray-300 hover:bg-gray-400 transition transform hover:scale-105"
                    >
                        (
                    </button>
                    <button
                        @click="appendToExpression(')')"
                        class="py-4 rounded-lg font-bold text-gray-700 bg-gray-300 hover:bg-gray-400 transition transform hover:scale-105"
                    >
                        )
                    </button>
                </div>

                <!-- Row 2: Advanced Functions -->
                <div class="grid grid-cols-4 gap-2">
                    <button
                        @click="appendToExpression('sqrt(')"
                        class="py-4 rounded-lg font-bold text-white bg-purple-500 hover:bg-purple-600 transition transform hover:scale-105"
                        title="Square Root"
                    >
                        √
                    </button>
                    <button
                        @click="appendToExpression('^')"
                        class="py-4 rounded-lg font-bold text-white bg-purple-500 hover:bg-purple-600 transition transform hover:scale-105"
                        title="Power"
                    >
                        x^y
                    </button>
                    <button
                        @click="appendToExpression('abs(')"
                        class="py-4 rounded-lg font-bold text-white bg-indigo-500 hover:bg-indigo-600 transition transform hover:scale-105 text-sm"
                        title="Absolute Value"
                    >
                        |x|
                    </button>
                    <button
                        @click="appendToExpression('PI')"
                        class="py-4 rounded-lg font-bold text-white bg-indigo-500 hover:bg-indigo-600 transition transform hover:scale-105"
                        title="Pi"
                    >
                        π
                    </button>
                </div>

                <!-- Row 3: Numbers 7-9 & Division -->
                <div class="grid grid-cols-4 gap-2">
                    <button
                        @click="appendToExpression('7')"
                        class="py-4 rounded-lg font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 transition transform hover:scale-105 text-lg"
                    >
                        7
                    </button>
                    <button
                        @click="appendToExpression('8')"
                        class="py-4 rounded-lg font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 transition transform hover:scale-105 text-lg"
                    >
                        8
                    </button>
                    <button
                        @click="appendToExpression('9')"
                        class="py-4 rounded-lg font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 transition transform hover:scale-105 text-lg"
                    >
                        9
                    </button>
                    <button
                        @click="appendToExpression('/')"
                        class="py-4 rounded-lg font-bold text-white bg-blue-500 hover:bg-blue-600 transition transform hover:scale-105 text-lg"
                    >
                        ÷
                    </button>
                </div>

                <!-- Row 4: Numbers 4-6 & Multiplication -->
                <div class="grid grid-cols-4 gap-2">
                    <button
                        @click="appendToExpression('4')"
                        class="py-4 rounded-lg font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 transition transform hover:scale-105 text-lg"
                    >
                        4
                    </button>
                    <button
                        @click="appendToExpression('5')"
                        class="py-4 rounded-lg font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 transition transform hover:scale-105 text-lg"
                    >
                        5
                    </button>
                    <button
                        @click="appendToExpression('6')"
                        class="py-4 rounded-lg font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 transition transform hover:scale-105 text-lg"
                    >
                        6
                    </button>
                    <button
                        @click="appendToExpression('*')"
                        class="py-4 rounded-lg font-bold text-white bg-blue-500 hover:bg-blue-600 transition transform hover:scale-105 text-lg"
                    >
                        ×
                    </button>
                </div>

                <!-- Row 5: Numbers 1-3 & Subtraction -->
                <div class="grid grid-cols-4 gap-2">
                    <button
                        @click="appendToExpression('1')"
                        class="py-4 rounded-lg font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 transition transform hover:scale-105 text-lg"
                    >
                        1
                    </button>
                    <button
                        @click="appendToExpression('2')"
                        class="py-4 rounded-lg font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 transition transform hover:scale-105 text-lg"
                    >
                        2
                    </button>
                    <button
                        @click="appendToExpression('3')"
                        class="py-4 rounded-lg font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 transition transform hover:scale-105 text-lg"
                    >
                        3
                    </button>
                    <button
                        @click="appendToExpression('-')"
                        class="py-4 rounded-lg font-bold text-white bg-blue-500 hover:bg-blue-600 transition transform hover:scale-105 text-lg"
                    >
                        −
                    </button>
                </div>

                <!-- Row 6: Trig Functions -->
                <div class="grid grid-cols-4 gap-2">
                    <button
                        @click="appendToExpression('sin(')"
                        class="py-3 rounded-lg font-bold text-white bg-indigo-500 hover:bg-indigo-600 transition transform hover:scale-105 text-sm"
                        title="Sine"
                    >
                        sin
                    </button>
                    <button
                        @click="appendToExpression('cos(')"
                        class="py-3 rounded-lg font-bold text-white bg-indigo-500 hover:bg-indigo-600 transition transform hover:scale-105 text-sm"
                        title="Cosine"
                    >
                        cos
                    </button>
                    <button
                        @click="appendToExpression('tan(')"
                        class="py-3 rounded-lg font-bold text-white bg-indigo-500 hover:bg-indigo-600 transition transform hover:scale-105 text-sm"
                        title="Tangent"
                    >
                        tan
                    </button>
                    <button
                        @click="appendToExpression('log(')"
                        class="py-3 rounded-lg font-bold text-white bg-indigo-500 hover:bg-indigo-600 transition transform hover:scale-105 text-sm"
                        title="Natural Logarithm"
                    >
                        log
                    </button>
                </div>

                <!-- Row 7: Zero, Decimal, Addition -->
                <div class="grid grid-cols-4 gap-2">
                    <button
                        @click="appendToExpression('0')"
                        class="col-span-2 py-4 rounded-lg font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 transition transform hover:scale-105 text-lg"
                    >
                        0
                    </button>
                    <button
                        @click="appendToExpression('.')"
                        class="py-4 rounded-lg font-bold text-gray-800 bg-gray-200 hover:bg-gray-300 transition transform hover:scale-105 text-lg"
                    >
                        .
                    </button>
                    <button
                        @click="appendToExpression('+')"
                        class="py-4 rounded-lg font-bold text-white bg-blue-500 hover:bg-blue-600 transition transform hover:scale-105 text-lg"
                    >
                        +
                    </button>
                </div>

                <!-- Equals Button -->
                <button
                    @click="calculateResult"
                    :disabled="isLoading"
                    class="w-full py-5 rounded-lg font-bold text-white text-lg bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 disabled:from-gray-400 disabled:to-gray-400 transition transform hover:scale-105 active:scale-95 disabled:hover:scale-100"
                >
                    {{ isLoading ? 'Calculating...' : '=' }}
                </button>
            </div>

            <!-- Examples -->
            <div class="pt-4 border-t border-gray-200">
                <p class="text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wider">Examples:</p>
                <div class="space-y-1 text-xs text-gray-500">
                    <p>√16 + 9 = 13</p>
                    <p>2^8 = 256</p>
                    <p>sin(PI/2) = 1</p>
                </div>
            </div>
        </div>
    </div>
</template>
