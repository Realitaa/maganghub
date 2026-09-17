<script setup lang="ts">
import type { ChartData, ChartOptions } from 'chart.js';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import BaseChart from '@/components/ui/chart/BaseChart.vue';
import type { CompanyStatistics } from '@/types';

const props = withDefaults(
    defineProps<{
        statistics: CompanyStatistics;
        showHavenotInLegend?: boolean;
        showNumericLegend?: boolean;
    }>(),
    {
        showHavenotInLegend: false,
        showNumericLegend: false,
    },
);

const isDark = ref(false);

const checkTheme = () => {
    if (typeof document !== 'undefined') {
        isDark.value = document.documentElement.classList.contains('dark');
    }
};

let observer: MutationObserver | null = null;

onMounted(() => {
    checkTheme();

    if (typeof document !== 'undefined') {
        observer = new MutationObserver(checkTheme);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });
    }
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
});

const chartData = computed<ChartData<'pie'>>(() => {
    const isAllZero =
        props.statistics.multinational === 0 &&
        props.statistics.national === 0 &&
        props.statistics.startup === 0 &&
        (props.statistics.havenot || 0) === 0;

    if (isAllZero) {
        return {
            labels: ['Belum Ada Data'],
            datasets: [
                {
                    data: [1],
                    backgroundColor: [isDark.value ? '#27272a' : '#e4e4e7'],
                    borderWidth: 0,
                    offset: [0],
                },
            ],
        };
    }

    const labels = [
        'Perusahaan Multinasional',
        'Perusahaan Nasional',
        'Startup Teknologi',
        'Belum Magang',
    ];

    const data = [
        props.statistics.multinational,
        props.statistics.national,
        props.statistics.startup,
        props.statistics.havenot,
    ];

    const bgColors = [
        '#059669', // Emerald 600 (Multinasional)
        '#34d399', // Emerald 400 (Nasional)
        '#a7f3d0', // Emerald 200 (Startup)
        isDark.value ? '#3f3f46' : '#e4e4e7', // Zinc 700/200 (Belum Magang)
    ];

    return {
        labels,
        datasets: [
            {
                data,
                backgroundColor: bgColors,
                borderColor: isDark.value ? '#18181b' : '#ffffff',
                borderWidth: 2,
                // Explode the first slice (Perusahaan Multinasional)
                offset: [16, 0, 0, 0],
                hoverOffset: 20,
            },
        ],
    };
});

const chartOptions = computed<ChartOptions<'pie'>>(() => {
    const textColor = isDark.value ? '#ededec' : '#1b1b18';

    return {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: 16,
        },
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    color: textColor,
                    padding: 16,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: {
                        family: 'Inter, sans-serif',
                        size: 11,
                        weight: 500,
                    },
                    generateLabels: (chart) => {
                        const data = chart.data;

                        if (!data.labels || !data.datasets.length) {
                            return [];
                        }

                        const dataset = data.datasets[0];

                        return data.labels
                            .map((label, i) => {
                                const val = (dataset.data[i] as number) || 0;
                                const labelStr = String(label);

                                if (
                                    !props.showHavenotInLegend &&
                                    labelStr === 'Belum Magang'
                                ) {
                                    return null;
                                }

                                const text =
                                    props.showNumericLegend ||
                                    props.showHavenotInLegend
                                        ? `${labelStr} (${val})`
                                        : labelStr;

                                return {
                                    text,
                                    fillStyle: (
                                        dataset.backgroundColor as string[]
                                    )[i],
                                    strokeStyle: (
                                        dataset.backgroundColor as string[]
                                    )[i],
                                    lineWidth: 0,
                                    hidden: !chart.getDataVisibility(i),
                                    index: i,
                                    pointStyle: 'circle',
                                };
                            })
                            .filter(Boolean) as any[];
                    },
                },
            },
            tooltip: {
                backgroundColor: isDark.value ? '#18181b' : '#ffffff',
                titleColor: isDark.value ? '#ffffff' : '#09090b',
                bodyColor: isDark.value ? '#e4e4e7' : '#27272a',
                borderColor: isDark.value ? '#27272a' : '#e4e4e7',
                borderWidth: 1,
                padding: 10,
                boxPadding: 4,
                usePointStyle: true,
                callbacks: {
                    label: (context) => {
                        const label = context.label || '';

                        if (
                            !props.showHavenotInLegend &&
                            label === 'Belum Magang'
                        ) {
                            return '';
                        }

                        const value = context.parsed;
                        const total = (context.dataset.data as number[]).reduce(
                            (a, b) => a + b,
                            0,
                        );
                        const pct =
                            total > 0
                                ? ((value / total) * 100).toFixed(1)
                                : '0.0';

                        return ` ${label}: ${value} (${pct}%)`;
                    },
                },
            },
        },
    };
});
</script>

<template>
    <div class="h-[360px] w-full max-w-full min-w-0">
        <BaseChart type="pie" :data="chartData" :options="chartOptions" />
    </div>
</template>
