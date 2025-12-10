<script setup lang="ts">
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { ElMessage } from "element-plus";

const props = defineProps<{
    ticketId: number;
    mode: string;
    id: number | null;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const form = useForm({
    message: "",
    id: props.id ?? null,
});

const visible = ref(true);

watch(visible, (val) => {
    if (!val) {
        emit("close");
    }
});

const submit = () => {
    if (props.mode === "create") {
        form.post(route("tickets.comments.store", props.ticketId), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset("message");
                emit("close");

                ElMessage({
                    type: "success",
                    message: "Comentario creado correctamente.",
                });
            },
            onError: () => {
                ElMessage.error("Error al crear el comentario.");
            },
        });
    }
    if (props.mode === "update") {
        form.put(
            route("tickets.comments.update", {
                ticket: props.ticketId,
                comment: props.id,
            }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset("message");
                    emit("close");
                    ElMessage({
                        type: "success",
                        message: "Comentario actualizado correctamente.",
                    });
                },
                onError: () => {
                    ElMessage.error("Error al actualizar comentario.");
                },
            }
        );
    }
};
let title = ref("");
if (props.mode === "create") {
    title.value = "Nuevo comentario";
} else if (props.mode === "update") {
    title.value = "Actualizar comentario";
}
</script>

<template>
    <el-dialog v-model="visible" :title="title" width="500px">
        <el-form label-position="top">
            <el-form-item label="Comentario">
                <el-input
                    v-model="form.message"
                    type="textarea"
                    :rows="4"
                    placeholder="Escribe tu comentario..."
                />
                <div
                    v-if="form.errors.message"
                    class="text-red-500 text-xs mt-1"
                >
                    {{ form.errors.message }}
                </div>
            </el-form-item>
        </el-form>

        <template #footer>
            <span class="dialog-footer">
                <el-button @click="visible = false"> Cancelar </el-button>
                <el-button
                    type="primary"
                    :loading="form.processing"
                    @click="submit"
                >
                    Guardar comentario
                </el-button>
            </span>
        </template>
    </el-dialog>
</template>
