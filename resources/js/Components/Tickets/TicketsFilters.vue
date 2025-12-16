<script setup lang="ts">
import { ref, watch } from "vue";
import { pickBy, throttle } from "lodash";
import { useForm } from "@inertiajs/vue3";
import { FiltersTickets } from "@/types/ticket";
import { TicketCategory } from "@/types/ticketCategory";
import { SupervisorUser } from "@/types/user";
import { type UploadFile } from "element-plus";
import ApplicationLogo from "../../../../vendor/laravel/breeze/stubs/inertia-react-ts/resources/js/Components/ApplicationLogo";

const props = withDefaults(
    defineProps<{
        role: string;

        categories: TicketCategory[]; // <-- ¡Debe ser un array de objetos!

        filters: FiltersTickets;

        supervisorList: SupervisorUser[];
    }>(),
    {
        categories: () => [],
        // El valor por defecto de 'filters' es un objeto vacío
        filters: () => ({}),
    }
);

const showFilters = ref(true);

const statusName = ["pending", "review", "approved", "rejected"];
const form = useForm({
    from: props.filters.from || null,
    to: props.filters.to || null,
    status: props.filters.status || null,
    supervisor_id: props.filters.supervisor_id || null,
    user_name: props.filters.user_name || null,
    category_id: props.filters.category_id || null,
});

const dateRange = ref<[string | null, string | null]>([form.from, form.to]);
watch(dateRange, (newRange) => {
    // Si el array existe y tiene dos elementos
    if (newRange && newRange.length === 2 && newRange[0] !== null) {
        form.from = newRange[0];
        form.to = newRange[1];
    } else {
        // Si el usuario borra la fecha
        form.from = null;
        form.to = null;
    }
});
const emit = defineEmits<{
    (e: "filtersChanged", filters: Record<string, any>): void;
}>();

const sendFilters = () => {
    // Limpiar los valores nulos/vacíos (¡Esto es clave!)
    const cleanFilters = pickBy(form.data(), (value) => !!value);

    // Emitir el evento, pasando los filtros limpios
    emit("filtersChanged", cleanFilters);
};
const throttledSendFilters = throttle(sendFilters, 300);
watch(
    () => form.data(),
    () => {
        throttledSendFilters();
    },
    { deep: true }
);

const resetFilters = () => {
    form.to = "";
    form.from = "";
    form.status = "";
    form.supervisor_id = null;
    form.user_name = null;
    form.category_id = null;
};
</script>

<template>
    <div class="filter-section mb-8" style="margin-bottom: 20px">
        <el-button
            @click="showFilters = !showFilters"
            type="primary"
            plain
            class="my-8"
        >
            {{ showFilters ? "Ocultar Filtros" : "Mostrar Filtros" }}
        </el-button>

        <el-collapse-transition>
            <div v-show="showFilters">
                <el-form :model="form" label-position="top">
                    <div class="flex flex-col gap-4 md:flex-row md:gap-5">
                        <el-form-item
                            label="Rango de Fechas"
                            style="width: 40%; display: block"
                        >
                            <el-date-picker
                                v-model="dateRange"
                                type="daterange"
                                range-separator="A"
                                start-placeholder="Fecha Inicio"
                                end-placeholder="Fecha Fin"
                                value-format="YYYY-MM-DD"
                                format="DD-MM-YYYY"
                                style="width: 100%"
                            />
                        </el-form-item>
                        <el-form-item
                            label="Empleado"
                            style="width: 100%; display: block"
                            v-if="props.role !== 'employee'"
                        >
                            <el-input
                                v-model="form.user_name"
                                style="width: 100%"
                            />
                        </el-form-item>

                        <el-form-item
                            label="Supervisor"
                            style="width: 100%; display: block"
                            v-if="props.role === 'admin'"
                        >
                            <el-select
                                placeholder="Selecciona un supervisor"
                                v-model="form.supervisor_id"
                                style="width: 100%"
                            >
                                <el-option
                                    v-for="sup in supervisorList"
                                    :key="sup.id"
                                    :label="sup.name"
                                    :value="sup.id"
                                />
                            </el-select>
                        </el-form-item>
                        <el-form-item
                            label="Categoría"
                            style="width: 100%; display: block"
                        >
                            <el-select
                                placeholder="Selecciona una categoría"
                                v-model="form.category_id"
                                style="width: 100%"
                            >
                                <el-option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :label="cat.name"
                                    :value="cat.id"
                                />
                            </el-select>
                        </el-form-item>

                        <el-form-item
                            label="Estado"
                            style="width: 100%; display: block"
                        >
                            <el-select
                                placeholder="Selecciona Estado"
                                v-model="form.status"
                                style="width: 100%"
                            >
                                <el-option
                                    v-for="st in statusName"
                                    :key="st"
                                    :label="st"
                                    :value="st"
                                />
                            </el-select>
                        </el-form-item>
                    </div>
                </el-form>
                <div class="flex justify-end mt-4">
                    <el-button type="warning" @click="resetFilters">
                        Limpiar filtros
                    </el-button>
                </div>
            </div>
        </el-collapse-transition>
    </div>
</template>
