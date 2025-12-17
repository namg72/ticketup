<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, router } from "@inertiajs/vue3";
import FormTicket from "@/Components/Tickets/FormTicket.vue";
import { formatDate } from "@/Helpers/dateFormatter";
import {
    Edit as IconEdit,
    Delete as IconDelete,
} from "@element-plus/icons-vue";
import { ref } from "vue";
import CommentModal from "@/Components/Tickets/CommentModal.vue";
import { ElMessage, ElMessageBox } from "element-plus";
import { Ticket } from "@/types/ticket";
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
        status: string | number;
        finalized_by_admin: boolean;
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

console.log(props.ticket.finalized_by_admin);

const form = useForm({
    id: props.ticket.id,
    title: props.ticket.title ?? "",
    description: props.ticket.description ?? "",
    category: props.ticket.category!.name ?? null,
    supervisor: props.ticket.supervisor?.name,
    total_amount: props.ticket.total_amount ?? "",
    status: props.ticket.status,
    finalized_by_admin: props.ticket.finalized_by_admin ?? null,
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
const delteCommenet = (commentId: number) => {
    // 1. Mostrar un cuadro de confirmación (Buena Práctica)
    ElMessageBox.confirm(
        "¿Estás seguro de que quieres eliminar este comentario?",
        "Advertencia",
        {
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            type: "warning",
        }
    )
        .then(() => {
            // 2. Ejecutar la solicitud DELETE
            router.delete(
                route("tickets.comments.destroy", {
                    ticket: props.ticket.id,
                    comment: commentId,
                }),
                {
                    // Opciones de Inertia (opcional)
                    preserveScroll: true,
                    onSuccess: () => {
                        ElMessage({
                            type: "success",
                            message: "Comentario eliminado correctamente.",
                        });
                    },
                    onError: () => {
                        ElMessage.error("Error al eliminar el comentario.");
                    },
                }
            );
        })
        .catch((err) => {
            console.log(err);
        });
};

//habiliamos el borrado con un tiempo maximo de 15 minutos tras la creación del comentario
const isDeletable = (commentCreated_at: string) => {
    const commentDate = new Date(commentCreated_at);

    const now = new Date();

    const limitTimeMs = now.getTime() - 15 * 60 * 1000; // 15 minutos

    return commentDate.getTime() >= limitTimeMs;
};

const isCommentAvailable = () => {
    if (
        props.ticket?.status === "approved" ||
        props.ticket?.status === "rejected"
    ) {
        return false;
    } else {
        return true;
    }
};
const handleStatus = (newStatus: number) => {
    if (props.role === "admin" && (newStatus === 3 || newStatus === 4)) {
        form.finalized_by_admin = true;
    } else if (props.role === "admin" && newStatus === 2) {
        form.finalized_by_admin = false;
    }

    form.status = newStatus;
    form.put(route("tickets.change.status", props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset("status");

            ElMessage({
                type: "success",
                message: "Estado acutalizado correctamente.",
            });
        },
        onError: () => {
            ElMessage.error("Error al actualizar el estado.");
        },
    });
};

const statusDisabled = () => {
    if (props.role !== "admin" && props.ticket.finalized_by_admin === true) {
        return true;
    } else {
        return false;
    }
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
                    <div
                        class="flex justify-end mb-10"
                        v-if="props.role !== 'employee'"
                    >
                        <div>
                            <el-button
                                type="warning"
                                :disabled="statusDisabled()"
                                @click="handleStatus(2)"
                            >
                                Revisar ticket
                            </el-button>
                            <el-button
                                type="success"
                                :disabled="statusDisabled()"
                                @click="handleStatus(3)"
                            >
                                Aprobar ticket
                            </el-button>
                            <el-button
                                type="danger"
                                :disabled="statusDisabled()"
                                @click="handleStatus(4)"
                            >
                                Rechazar ticket
                            </el-button>
                        </div>
                    </div>
                    <div class="flex">
                        <h1 class="text-2xl font-bold mb-4">
                            Editar ticket Nº {{ form.id }}
                        </h1>
                        <div class="ml-10">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 text-l font-semibold rounded-full text-white"
                                :class="{
                                    'bg-green-500':
                                        ticket.status === 'approved',
                                    'bg-red-500': ticket.status === 'rejected',
                                    'bg-yellow-500': ticket.status === 'review',
                                    'bg-violet-500':
                                        ticket.status === 'pending',
                                }"
                            >
                                <!-- Texto bonito del estado -->
                                <span v-if="ticket.status === 'approved'"
                                    >Aprobado</span
                                >
                                <span v-else-if="ticket.status === 'rejected'"
                                    >Rechazado</span
                                >
                                <span v-else-if="ticket.status === 'review'"
                                    >En revisión</span
                                >
                                <span v-else-if="ticket.status === 'pending'"
                                    >Pendiente</span
                                >
                            </span>
                        </div>
                    </div>
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
                                                :disabled="
                                                    !isCommentAvailable()
                                                "
                                            />

                                            <el-button
                                                v-if="
                                                    isDeletable(
                                                        comment.created_at
                                                    )
                                                "
                                                size="small"
                                                type="danger"
                                                :icon="IconDelete"
                                                @click="
                                                    delteCommenet(comment.id)
                                                "
                                                :disabled="
                                                    !isCommentAvailable()
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
                                :disabled="!isCommentAvailable()"
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
