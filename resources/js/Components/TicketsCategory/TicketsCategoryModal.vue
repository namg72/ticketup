<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { TicketCategory } from "@/types/ticketCategory";
import { ElMessage } from "element-plus";

const props = defineProps<{
    category: TicketCategory | null;

    show: boolean;
}>();

const isEditMode = computed(() => !!props.category);
const emit = defineEmits(["close"]);

const form = useForm({
    id: undefined as number | undefined,
    name: "" as string,
    description: "" as string,
    active: true as boolean,
});

watch(
    // 1. Fuente: Observar la prop 'user'
    () => props.category,

    // 2. Handler: Dejamos que TS infiera el tipo User | null,
    // lo cual generalmente funciona si la interfaz 'User' es correcta.
    (newCategory) => {
        if (newCategory) {
            // Modo Edición: Cargamos los valores del usuario actual
            form.defaults({
                id: newCategory.id,
                name: newCategory.name,
                description: newCategory.description,
                active: !!newCategory.active,
            }).reset();
        } else {
            // Modo Creación: Usamos los defaults limpios
            form.defaults({
                id: undefined,
                name: "",
                description: "",

                active: true,
            }).reset();
        }
    },
    { immediate: true }
);

const title = computed(() => {
    return isEditMode.value ? "Editar categoria" : "Nueva Categoria";
});
const close = () => {
    emit("close");
    form.reset();
    form.clearErrors();
};
const submit = () => {
    if (!isEditMode.value) {
        form.post(route("category.store"), {
            onSuccess: () => {
                close();
                ElMessage({
                    type: "success",
                    message: "categoria creada correctamente",
                });
            },
            onError: () => {
                ElMessage({
                    type: "error",
                    message: "Error al crear la categoria",
                });
            },
        });
    } else {
        form.put(route("category.update", form.id), {
            onSuccess: () => {
                close();
                ElMessage({
                    type: "success",
                    message: "categoria editada correctamente",
                });
            },
            onError: () => {
                ElMessage({
                    type: "error",
                    message: "Error al editar la categoria",
                });
            },
        });
    }
};
</script>

<template>
    <el-dialog
        :model-value="props.show"
        :title="title"
        width="500px"
        @close="close"
        :close-on-click-modal="false"
    >
        <el-form label-position="top">
            <el-form-item label="* Nombre" :error="form.errors.name">
                <el-input
                    v-model="form.name"
                    type="input"
                    :rows="4"
                    placeholder="Nombre Categoria ..."
                />
            </el-form-item>
            <el-form-item label="Descripcion">
                <el-input
                    v-model="form.description"
                    type="textarea"
                    :rows="4"
                    placeholder="Descripcion Categoria..."
                />
            </el-form-item>

            <el-form-item label="Activo" v-if="isEditMode">
                <el-switch
                    v-model="form.active"
                    active-text="Sí"
                    inactive-text="No"
                    style="
                        --el-switch-on-color: #13ce66;
                        --el-switch-off-color: #ff4949;
                    "
                />
            </el-form-item>
        </el-form>

        <template #footer>
            <span class="dialog-footer">
                <el-button @click="close"> Cancelar </el-button>
                <el-button
                    type="primary"
                    :loading="form.processing"
                    @click="submit"
                >
                    {{ isEditMode ? "Guardar Cambios" : "Crear Categoria" }}
                </el-button>
            </span>
        </template>
    </el-dialog>
</template>
