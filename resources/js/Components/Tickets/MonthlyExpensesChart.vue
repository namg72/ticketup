<script setup lang="ts">
import { computed } from "vue";
import VChart, { THEME_KEY } from "vue-echarts";
import { use } from "echarts/core";
// Importaciones mínimas de los componentes de ECharts que usarás
import { BarChart } from "echarts/charts";
import {
    GridComponent,
    TooltipComponent,
    TitleComponent,
    LegendComponent,
} from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";

// 💡 Registrar los módulos necesarios de ECharts
use([
    CanvasRenderer,
    BarChart,
    GridComponent,
    TooltipComponent,
    TitleComponent,
    LegendComponent,
]);

// 💡 PROPS: Recibe los datos ya listos del componente padre (Dashboard.vue)
const props = defineProps<{
    monthlyData: Array<{ month: string; total: number }>;
}>();

// Opcional: Define una inyección si usas temas de ECharts (ej: en main.js)
// provide(THEME_KEY, 'dark');

// 💡 PROPIEDAD COMPUTADA: Transforma los datos de la prop en opciones de ECharts
const chartOptions = computed(() => {
    // 1. Separar el array de objetos en dos arrays: etiquetas (meses) y datos (totales)
    const months = props.monthlyData.map((item) => item.month);
    const totals = props.monthlyData.map((item) => item.total);

    return {
        title: {
            text: `Gastos Mensuales`,
            subtext: `Totales en €`,
            left: "center",
        },
        tooltip: {
            trigger: "axis",
            axisPointer: { type: "shadow" }, // Sombra para la barra actual
        },
        grid: {
            left: "3%",
            right: "4%",
            bottom: "3%",
            containLabel: true, // Asegura que las etiquetas no se corten
        },
        xAxis: {
            type: "category",
            data: months, // 💡 Eje X: Los meses
            axisLabel: {
                interval: 0, // Muestra todas las etiquetas
                rotate: 45, // Rota las etiquetas para que no se superpongan
                margin: 10,
                formatter: function (value: string) {
                    // Formatea la etiqueta X para mostrar el mes en dos líneas (Ej: Ene\n2025)
                    return value.replace(".", "").replace(" ", "\n");
                },
            },
        },
        yAxis: {
            type: "value",
            name: "Total (€)",
            // Asegura que el eje Y empiece en 0, clave para gráficos de barras
            min: 0,
        },
        series: [
            {
                name: "Gasto",
                type: "bar", // Gráfico de barras
                data: totals, // 💡 Serie de datos: Los totales
                barWidth: "60%",
                itemStyle: {
                    // Puedes definir un color fijo o una función para cambiar el color
                    color: "#409EFF", // Color azul primario de Element Plus
                },
                // Mostrar el valor encima de cada barra
                label: {
                    show: true,
                    position: "top",
                    formatter: function (params: any) {
                        // Formato de moneda simple
                        return params.value.toFixed(2) + " €";
                    },
                },
            },
        ],
    };
});
</script>

<template>
    <div class="chart-container" style="height: 400px; width: 100%">
        <v-chart class="chart" :option="chartOptions" autoresize />
    </div>
</template>

<style scoped>
.chart-container {
    /* Define la altura para que ECharts pueda renderizar */
    min-height: 400px;
}
.chart {
    height: 100%;
}
</style>
