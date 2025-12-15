<script setup lang="ts">
import TicketsCategoryModal from "@/Components/TicketsCategory/TicketsCategoryModal.vue";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { TicketCategory } from "@/types/ticketCategory";

import { Edit as IconEdit } from "@element-plus/icons-vue";
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
const props = defineProps<{
    categories: {
        current_page: number;
        data: TicketCategory[]; // Array de usuarios
        first_page_url: string | null;
        last_page: number;
        last_page_url: string | null;
        next_page_url: string | null;
        prev_page_url: string | null;
        links: PaginationLink[];
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}>();

const showModal = ref(false);

const categoryToEdit = ref<TicketCategory | null>(null);
const handleCloseModal = () => {
    // 🚨 Cerrar el modal
    showModal.value = false;
};

const handleCreateTicketCategory = () => {
    categoryToEdit.value = null;
    showModal.value = true;
};
const handleEditTicketCategory = (category: TicketCategory) => {
    categoryToEdit.value = category;
    showModal.value = true;
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Gestion de Gastos
            </h2>
        </template>

        <div class="mt-8 w-[80%] flex justify-end">
            <el-button type="warning" @click="handleCreateTicketCategory">
                Nueva Categoria
            </el-button>
        </div>

        <TicketsCategoryModal
            :show="showModal"
            :category="categoryToEdit"
            @close="handleCloseModal"
        />

        <div class="flex justify-center w-full mt-8 mx-auto overflow-x-auto">
            <el-table
                :data="categories.data"
                style="width: 60%"
                border
                stripe
                empty-text="No hay registros."
            >
                <el-table-column
                    prop="name"
                    label="Nombre"
                    show-overflow-tooltip
                    min-width="150"
                    header-align="center"
                />
                <el-table-column
                    prop="description"
                    label="Descripción"
                    show-overflow-tooltip
                    min-width="150"
                    header-align="center"
                />
                <el-table-column
                    label="Estado"
                    show-overflow-tooltip
                    min-width="100"
                    header-align="center"
                    align="center"
                >
                    <template #default="scope">
                        <span
                            class="inline-flex items-center justify-center px-2.5 py-0.5 text-xs font-semibold rounded-full text-white"
                            :class="{
                                'bg-green-500': scope.row.active,
                                'bg-red-500': !scope.row.active,
                            }"
                        >
                            <span v-if="scope.row.active"> Activa </span>
                            <span v-if="!scope.row.active"> Inactiva </span>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column
                    label="Acciones"
                    show-overflow-tooltip
                    min-width="100"
                    header-align="center"
                    align="center"
                >
                    <template #default="scope">
                        <el-button
                            size="small"
                            type="primary"
                            :icon="IconEdit"
                            circle
                            @click="handleEditTicketCategory(scope.row)"
                        />
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <div class="flex w-[80%] justify-end mt-6 mb-12">
            <template v-for="(link, index) in categories.links" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="px-3 py-2 text-sm leading-4 border rounded"
                    :class="{
                        'bg-blue-600 text-white border-blue-600': link.active,
                        'text-gray-700 hover:bg-gray-100': !link.active,
                    }"
                    v-html="link.label"
                />

                <span
                    v-else
                    class="px-3 py-2 text-sm leading-4 border rounded text-gray-400 cursor-default"
                    v-html="link.label"
                />
            </template>
        </div>
    </AuthenticatedLayout>
</template>
