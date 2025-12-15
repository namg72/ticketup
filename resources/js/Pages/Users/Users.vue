<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import NavLink from "@/Components/NavLink.vue";
import { formatDate } from "@/Helpers/dateFormatter";
import {
    Edit as IconEdit,
    Delete as IconDelete,
} from "@element-plus/icons-vue";
import { Link, useForm } from "@inertiajs/vue3";

import { ref } from "vue";
import UserFormModal from "@/Components/Users/UserFormModal.vue";
import { User } from "@/types";
import { ElMessage, ElMessageBox } from "element-plus";

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    users: {
        current_page: number;
        data: Array<any>; // Array de usuarios
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
    type: "employee" | "supervisor";
    supervisors: { id: number; name: string }[];
}>();

const form = useForm({});

const showModal = ref(false);

const userToEdit = ref<User | null>(null);

const handleCloseModal = () => {
    // 🚨 Cerrar el modal
    showModal.value = false;
};
const handleCreateUser = () => {
    showModal.value = true;
};
const handleEditUser = (user: User) => {
    userToEdit.value = user as User; // ⬅️ Carga el objeto del usuario a editar
    showModal.value = true;
};
const handleDeleteUser = (user: User) => {
    ElMessageBox.confirm(
        "¿Estás seguro de que quieres eliminar a este usuario?",
        "Advertencia",
        {
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            type: "warning",
        }
    ).then(() => {
        form.delete(
            route("users.destroy", {
                user: user,
            })
        ),
            {
                preserveScroll: true,
                onSuccess: () => {
                    ElMessage({
                        type: "success",
                        message: "Usuario eliminado correctamente",
                    });
                },
                onError: () => {
                    ElMessage.error("Error al eliminar el usuario");
                },
            };
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Gestion de usuarios
            </h2>
        </template>

        <div class="flex w-4/5 mx-auto mt-10">
            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                <NavLink
                    :href="route('users', { type: 'employee' })"
                    :active="
                        route().params.type === 'employee' ||
                        !route().params.type
                    "
                >
                    Empleados
                </NavLink>
                <NavLink
                    :href="route('users', { type: 'supervisor' })"
                    :active="route().params.type === 'supervisor'"
                >
                    Supervisores
                </NavLink>
            </div>
        </div>
        <div class="mt-8 w-[80%] flex justify-end">
            <el-button type="warning" @click="handleCreateUser()">
                {{
                    props.type === "employee"
                        ? "Crear Empleado"
                        : "Crear Supervisor"
                }}
            </el-button>
            <UserFormModal
                :show="showModal"
                :roleType="props.type"
                :supervisors="props.supervisors"
                :user="userToEdit"
                @close="handleCloseModal"
            />
        </div>
        <div class="flex justify-center w-full mt-8 mx-auto overflow-x-auto">
            <el-table
                :data="users.data"
                style="width: 60%"
                border
                stripe
                empty-text="No hay registros."
            >
                <!-- Nombre -->
                <el-table-column
                    prop="name"
                    label="Nombre"
                    show-overflow-tooltip
                    min-width="150"
                    header-align="center"
                />

                <!-- Email -->
                <el-table-column
                    prop="email"
                    label="Email"
                    show-overflow-tooltip
                    min-width="150"
                    header-align="center"
                />

                <!-- Fecha creación (si la tienes en el modelo) -->
                <el-table-column
                    label="Fecha alta"
                    width="150"
                    sortable
                    align="center"
                >
                    <template #default="scope">
                        <span>{{ formatDate(scope.row.created_at) }}</span>
                    </template>
                </el-table-column>

                <el-table-column
                    label="Acciones"
                    width="160"
                    fixed="right"
                    align="center"
                >
                    <template #default="scope">
                        <el-button
                            size="small"
                            type="primary"
                            :icon="IconEdit"
                            circle
                            @click="handleEditUser(scope.row)"
                        />

                        <el-button
                            size="small"
                            type="danger"
                            :icon="IconDelete"
                            circle
                            @click="handleDeleteUser(scope.row)"
                        />
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <div class="flex w-[80%] justify-end mt-6 mb-12">
            <template v-for="(link, index) in users.links" :key="index">
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
,
