<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { User } from "@/types";
import { Ticket, TicketRow } from "@/types/ticket";
import { Head, Link } from "@inertiajs/vue3";
import {
    Picture as IconPicture,
    Edit as IconEdit,
} from "@element-plus/icons-vue";
import { formatDate } from "@/Helpers/dateFormatter";
import { ref } from "vue";

interface StatusCounts {
    pending: number;
    approved: number;
    review: number;
    rejected: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator<T> {
    current_page: number;
    data: T[]; // El array real de tus elementos (T es Ticket en este caso)
    last_page: number;
    total: number;
    per_page: number; // Añadido, ya que es clave para la paginación
    links: PaginationLink[];
    first_page_url: string | null;
    last_page_url: string | null;
    next_page_url: string | null;
    prev_page_url: string | null;
}

const props = defineProps<{
    example: string;
    role: string;
    tickets: Paginator<Ticket>;
    totalTickets: number;
    user: User;
    supervisor?: User;
    statusCounts: StatusCounts;
}>();

const showImageDialog = ref(false);
const currentImageUrl = ref<string | null>(null);
const currentDownloadUrl = ref<string | null>(null);

const openImageModal = (row: TicketRow) => {
    currentImageUrl.value = `/tickets/${row.id}/image`;
    currentDownloadUrl.value = `/tickets/${row.id}/image/download`;
    showImageDialog.value = true;
};
</script>

<template>
    <Head title="Panel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Panel
            </h2>
        </template>

        <div class="py-8">
            <!-- Contenedor centrado al 80% -->
            <div class="w-4/5 mx-auto space-y-6">
                <!-- FILA 1: 2 CARDS (usuario / gráfico) -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Card izquierda: usuario + total tickets -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <!-- Luego meteremos props.userName y props.totalTickets -->

                        <div
                            class="flex flex-col gap-4 sm:flex-row sm:justify-between"
                        >
                            <div>
                                <p class="text-sm text-gray-500">Usuario</p>
                                <p
                                    class="mt-1 text-xl font-semibold text-gray-900"
                                >
                                    {{ user.name }}
                                </p>
                            </div>
                            <div v-if="supervisor !== null">
                                <p class="text-sm text-gray-500">Supervisor</p>
                                <p
                                    class="mt-1 text-xl font-semibold text-gray-900"
                                >
                                    {{ supervisor!.name }}
                                </p>
                            </div>
                        </div>

                        <p class="mt-4 text-sm text-gray-500">
                            Tickets totales
                        </p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{ totalTickets }}
                        </p>
                    </div>

                    <!-- Card derecha: gráfico (de momento vacía) -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <p class="text-sm text-gray-500 mb-2">
                            Gráfico por meses
                        </p>
                        <div
                            class="flex items-center justify-center h-40 text-gray-400 text-sm border border-dashed border-gray-200 rounded-lg"
                        >
                            Aquí irá el gráfico
                        </div>
                    </div>
                </div>

                <!-- FILA 2: 4 CARDS DE ESTADO -->
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <p class="text-xs uppercase text-gray-500">
                            Pendientes
                        </p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            {{ statusCounts.pending }}
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <p class="text-xs uppercase text-gray-500">
                            En revisión
                        </p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            {{ statusCounts.review }}
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <p class="text-xs uppercase text-gray-500">Aprovados</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            {{ statusCounts.approved }}
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <p class="text-xs uppercase text-gray-500">
                            Rechazados
                        </p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            {{ statusCounts.rejected }}
                        </p>
                    </div>
                </div>

                <!-- FILA 3: TABLA DE TICKETS -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <p class="text-lg font-semibold text-gray-900">
                            Tickets
                        </p>

                        <Link :href="route('tickets.create')">
                            <el-button
                                type="success"
                                v-if="role === 'employee'"
                            >
                                Subir Ticket
                            </el-button>
                        </Link>
                    </div>

                    <!-- Aquí luego metemos la tabla real -->
                    <div class="mt-4 overflow-x-auto">
                        <el-table
                            :data="tickets.data"
                            style="width: 100%"
                            border
                            stripe
                            empty-text="No hay tickets registrados."
                        >
                            <el-table-column label="Fecha" width="120" sortable>
                                <template #default="scope">
                                    <span>{{
                                        formatDate(scope.row.created_at)
                                    }}</span>
                                </template>
                            </el-table-column>

                            <el-table-column
                                label="Empleado"
                                width="200"
                                v-if="role !== 'employee'"
                            >
                                <template #default="scope">
                                    <span>{{
                                        scope.row.user?.name || "N/A"
                                    }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column
                                prop="title"
                                label="Gasto"
                                width="300"
                            />

                            <el-table-column
                                prop="description"
                                label="Concepto"
                                show-overflow-tooltip
                            />
                            <el-table-column label="Tipo de gasto" width="150">
                                <template #default="scope">
                                    <span>{{
                                        scope.row.category?.name || "N/A"
                                    }}</span>
                                </template>
                            </el-table-column>

                            <el-table-column
                                prop="total_amount"
                                label="Importe"
                                width="120"
                                align="right"
                            >
                                <template #default="scope">
                                    <span>
                                        {{
                                            scope.row.total_amount // Paso 2: Verificar si existe y convertir a float
                                                ? `${parseFloat(
                                                      scope.row.total_amount
                                                  ).toFixed(2)}€` // Paso 3: Si no existe, mostrar N/A
                                                : "N/A"
                                        }}
                                    </span>
                                </template>
                            </el-table-column>

                            <el-table-column label="Estado" width="140">
                                <template #default="scope">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full text-white"
                                        :class="{
                                            'bg-green-500':
                                                scope.row.status === 'approved',
                                            'bg-red-500':
                                                scope.row.status === 'rejected',
                                            'bg-yellow-500':
                                                scope.row.status === 'review',
                                            'bg-violet-500':
                                                scope.row.status === 'pending',
                                            'bg-gray-400': !scope.row.status,
                                        }"
                                    >
                                        <!-- Texto bonito del estado -->
                                        <span
                                            v-if="
                                                scope.row.status === 'approved'
                                            "
                                            >Aprobado</span
                                        >
                                        <span
                                            v-else-if="
                                                scope.row.status === 'rejected'
                                            "
                                            >Rechazado</span
                                        >
                                        <span
                                            v-else-if="
                                                scope.row.status === 'review'
                                            "
                                            >En revisión</span
                                        >
                                        <span
                                            v-else-if="
                                                scope.row.status === 'pending'
                                            "
                                            >Pendiente</span
                                        >
                                        <span v-else>Desconocido</span>
                                    </span>
                                </template>
                            </el-table-column>

                            <el-table-column
                                label="Supervisor"
                                width="200"
                                v-if="role === 'admin'"
                            >
                                <template #default="scope">
                                    <span>{{
                                        scope.row.supervisor?.name || "N/A"
                                    }}</span>
                                </template>
                            </el-table-column>

                            <el-table-column
                                label="Ticket"
                                width="80"
                                align="center"
                            >
                                <template #default="scope">
                                    <el-button
                                        v-if="scope.row.uri"
                                        :icon="IconPicture"
                                        size="small"
                                        circle
                                        @click="openImageModal(scope.row)"
                                    />
                                    <span v-else>-</span>
                                </template>
                            </el-table-column>

                            <el-table-column
                                label="Acciones"
                                width="160"
                                fixed="right"
                                align="center"
                            >
                                <template #default="scope">
                                    <Link
                                        :href="
                                            route('tickets.edit', scope.row.id)
                                        "
                                        class="mr-2"
                                    >
                                        <el-button
                                            size="small"
                                            type="primary"
                                            :icon="IconEdit"
                                            circle
                                        />
                                    </Link>
                                </template>
                            </el-table-column>
                        </el-table>

                        <el-dialog
                            v-model="showImageDialog"
                            title="Imagen del ticket"
                            width="600px"
                        >
                            <div style="text-align: center">
                                <img
                                    v-if="currentImageUrl"
                                    :src="currentImageUrl"
                                    alt="Imagen del ticket"
                                    style="
                                        max-width: 100%;
                                        max-height: 400px;
                                        object-fit: contain;
                                    "
                                />

                                <div style="margin-top: 16px">
                                    <a
                                        v-if="currentDownloadUrl"
                                        :href="currentDownloadUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <el-button type="primary">
                                            Descargar
                                        </el-button>
                                    </a>
                                </div>
                            </div>
                        </el-dialog>

                        <div
                            v-if="tickets.links && tickets.links.length > 0"
                            class="flex justify-end mt-4 gap-1"
                        >
                            <Link
                                v-for="link in tickets.links"
                                :key="link.label + (link.url || '')"
                                :href="link.url || ''"
                                preserve-scroll
                                class="px-3 py-1 text-sm rounded border"
                                :class="[
                                    link.active
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100',
                                    !link.url &&
                                        'opacity-50 cursor-default pointer-events-none',
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
