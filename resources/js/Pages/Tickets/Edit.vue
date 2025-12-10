<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm } from "@inertiajs/vue3";
import FormTicket from "@/Components/Tickets/FormTicket.vue";
import { formatDate } from "@/Helpers/dateFormatter";
import {
    Edit as IconEdit,
    Delete as IconDelete,
} from "@element-plus/icons-vue";
import { ref } from "vue";
import CommentModal from "@/Components/Tickets/CommentModal.vue";
const props = defineProps<{
    ticket: {
        id: number;
        user_id: number;
        title: string | null;
        description: string | null;
        category_id: number;
        category?: { id: number; name: string } | null;
        total_amount: number | null;
        supervisor?: { name: string } | null;
        user?: { name: string } | null;
        created_at: string;
        updated_at: string;
        uri: string;
    };
    categories: {
        id: number;
        name: string;
    }[];
    role: string;
    comments: {
        id: number;
        message: string;
        ticket_id: number;
        user_id: number;
        created_at: string;
        updated_at: string;
        user?: { name: string } | null;
    }[];
    user: {
        id: number;
    };
}>();

const form = useForm({
    id: props.ticket.id,
    title: props.ticket.title ?? "",
    description: props.ticket.description ?? "",
    category: props.ticket.category!.name ?? null,
    supervisor: props.ticket.supervisor?.name,
    total_amount: props.ticket.total_amount ?? "",
});
const showCommentModal = ref(false);
const modalMode = ref<"create" | "update">("create");
const activeCommentId = ref<number | null>(null);
const activeCommentMessage = ref("");

const openCreate = () => {
    modalMode.value = "create";
    activeCommentId.value = null;
    activeCommentMessage.value = "";
    showCommentModal.value = true;
};

const openEdit = (comment: any) => {
    modalMode.value = "update";
    activeCommentId.value = comment.id;
    activeCommentMessage.value = comment.message;
    showCommentModal.value = true;
};
const delteCommenet = (id: number) => {
    console.log("delte", id);
};
</script>

<template>
    <AuthenticatedLayout>
        <!-- Contenedor principal: 80% del ancho -->
        <div class="w-4/5 mx-auto p-6">
            <!-- Contenedor de 2 columnas -->
            <div class="flex gap-x-16 items-stretch">
                <!-- Columna edición (2/3) -->
                <div class="basis-3/5">
                    <h1 class="text-2xl font-bold mb-4">
                        Editar ticket Nº {{ form.id }}
                    </h1>
                    <div class="h-full">
                        <FormTicket
                            :ticket="ticket"
                            :categories="categories"
                            :role="role"
                            mode="edit"
                        />
                    </div>
                </div>

                <!-- Columna comentarios (1/3) -->
                <div class="basis-2/5">
                    <div class="basis-1/3">
                        <div class="border rounded-lg p-4 h-full">
                            <h2 class="text-lg font-semibold mb-3">
                                Comentarios
                            </h2>

                            <div v-if="comments.length">
                                <div
                                    v-for="comment in comments"
                                    :key="comment.id"
                                    class="border-b pb-2 mb-2 last:border-b-0 last:pb-0 last:mb-0"
                                >
                                    <div class="text-xs text-gray-500 mb-1 m-4">
                                        {{
                                            comment.user?.name ??
                                            "Usuario desconocido"
                                        }}
                                        ·
                                        {{ formatDate(comment.created_at) }}
                                    </div>
                                    <div
                                        class="text-sm bg-white m-4 p-6 flex justify-between items-start"
                                    >
                                        {{ comment.message }}
                                        <span
                                            v-if="comment.user_id === user.id"
                                        >
                                            <el-button
                                                size="small"
                                                type="primary"
                                                :icon="IconEdit"
                                                circle
                                                @click="openEdit(comment)"
                                            />

                                            <el-button
                                                size="small"
                                                type="danger"
                                                :icon="IconDelete"
                                                circle
                                                @click="
                                                    delteCommenet(comment.id)
                                                "
                                            />
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <p v-else class="text-sm text-gray-500">
                                No hay comentarios todavía.
                            </p>
                        </div>
                        <div class="mt-4">
                            <el-button
                                type="success"
                                @click="showCommentModal = true"
                            >
                                Crear comentario
                            </el-button>

                            <CommentModal
                                v-if="showCommentModal"
                                :id="activeCommentId"
                                :mode="modalMode"
                                :ticket-id="ticket.id"
                                :initial-message="activeCommentMessage"
                                @close="showCommentModal = false"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
