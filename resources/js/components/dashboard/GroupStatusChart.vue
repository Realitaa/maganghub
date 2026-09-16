<script setup lang="ts">
import { computed } from 'vue';
import HighchartsChart from '@/components/ui/highcharts/HighchartsChart.vue';
import type { GroupStatusDistributionItem } from '@/types';

const props = defineProps<{
    items: GroupStatusDistributionItem[];
    totalGroups?: number;
}>();

const hasData = computed(() => {
    return props.items && props.items.length > 0 && (props.totalGroups ?? 0) > 0;
});

const chartOptions = computed(() => {
    return {
        chart: {
            type: 'pie',
            height: 320,
            spacingTop: 10,
            spacingBottom: 10,
            spacingLeft: 0,
            spacingRight: 0,
        },
        title: {
            text: '',
        },
        credits: {
            enabled: false,
        },
        tooltip: {
            formatter: function (this: any) {
                return (
                    '<span style="color:' +
                    this.point.color +
                    '">\u25CF</span> <b>' +
                    this.point.name +
                    '</b>: <b>' +
                    this.point.y +
                    '</b> kelompok (' +
                    this.point.percentage.toFixed(1) +
                    '%)'
                );
            },
        },
        plotOptions: {
            pie: {
                innerSize: '58%',
                allowPointSelect: true,
                cursor: 'pointer',
                showInLegend: true,
                slicedOffset: 8,
                dataLabels: {
                    enabled: true,
                    format: '{point.percentage:.1f}%',
                    distance: 12,
                },
            },
        },
        legend: {
            enabled: true,
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'bottom',
            itemMarginTop: 4,
            itemMarginBottom: 4,
        },
        series: [
            {
                name: 'Status Kelompok',
                colorByPoint: true,
                data: props.items,
            },
        ],
    };
});
</script>

<template>
    <div class="flex min-h-[320px] w-full items-center justify-center">
        <HighchartsChart v-if="hasData" :options="chartOptions" />
        <div
            v-else
            class="flex flex-col items-center justify-center p-8 text-center text-muted-foreground"
        >
            <div
                class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-muted"
            >
                <span class="text-xl">📊</span>
            </div>
            <p class="text-sm font-medium">Belum ada kelompok magang</p>
            <p class="mt-1 text-xs text-muted-foreground">
                Data distribusi status akan muncul ketika kelompok magang mulai
                dibuat.
            </p>
        </div>
    </div>
</template>
