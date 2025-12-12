<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { User, UserForm } from "../../types/user";
import { computed } from "vue";
import { ElMessage } from "element-plus";

const props = defineProps<{
    // Si se pasa un usuario, estamos en modo edición. Si es null, es creación.
    user: User | null;

    // Tipo de rol a crear/editar (employee o supervisor), usado para el endpoint
    roleType: "employee" | "supervisor";

    // Booleano para controlar si el modal está visible (lo controla el componente padre)
    show: boolean;

    // Lista de supervisores para el dropdown (si es empleado)
    supervisors: Array<any>;
}>();

// Definimos los eventos que el modal puede emitir al padre
const emit = defineEmits(["close"]);

// Formulario de inertia

const form = useForm({
    id: props.user ? props.user.id : undefined,
    name: props.user ? props.user.name : "",
    email: props.user ? props.user.email : "",
    roleType: props.roleType,
    supervisor_id:
        props.user && props.user.supervisor_id
            ? props.user.supervisor_id
            : null,
    is_active: props.user ? props.user.is_active : true,
});

const isEditMode = computed(() => !!props.user);

const close = () => {
    emit("close");
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (!isEditMode.value) {
        form.post(route("users.store"), {
            onSuccess: () => {
                form.reset();
                emit("close");
                ElMessage({
                    type: "success",
                    message: "Usuario creado correctamente.",
                });
            },
            onError: () => {
                ElMessage.error("Error al crear el usuario");
            },
        });
    }
};
if (isEditMode.value) {
}
</script>

<template>
    <el-dialog
        center
        width="40%"
        height="50%"
        :model-value="props.show"
        :title="
            isEditMode
                ? `Editar ${props.user?.name}`
                : `Crear ${
                      props.roleType === 'employee' ? 'Empleado' : 'Supervisor'
                  }`
        "
        @close="close"
        :close-on-click-modal="false"
    >
        <el-form label-position="top" style="width: 100%">
            <el-form-item label="* Nombre" :error="form.errors.name">
                <el-input v-model="form.name" />
            </el-form-item>
            <el-form-item label="* Email" :error="form.errors.email">
                <el-input v-model="form.email" />
            </el-form-item>
            <el-form-item
                label="* Supervisor"
                :error="form.errors.supervisor_id"
                v-if="props.roleType === 'employee'"
            >
                <el-select
                    placeholder="Selecciona un supervisor"
                    v-model="form.supervisor_id"
                    style="width: 100%"
                >
                    <el-option
                        v-for="sup in props.supervisors"
                        :key="sup.id"
                        :label="sup.name"
                        :value="sup.id"
                    />
                </el-select>
            </el-form-item>

            <el-form-item label="Activo" v-if="isEditMode">
                <el-switch
                    v-model="form.is_active"
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
                    {{ isEditMode ? "Guardar Cambios" : "Crear Usuario" }}
                </el-button>
            </span>
        </template>
    </el-dialog>
</template>
