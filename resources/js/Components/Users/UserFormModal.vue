<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { User, UserForm } from "../../types/user";
import { computed, watch } from "vue";
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
    id: undefined as number | undefined,
    name: "" as string,
    email: "" as string,
    roleType: props.roleType as string,
    supervisor_id: null as number | null,
    is_active: true as boolean,
});

const isEditMode = computed(() => !!props.user);

// Observamos la prop 'user'. Cuando cambia de null a un objeto User,
// reinicializamos el formulario con los datos de edición.
watch(
    // 1. Fuente: Observar la prop 'user'
    () => props.user,

    // 2. Handler: Dejamos que TS infiera el tipo User | null,
    // lo cual generalmente funciona si la interfaz 'User' es correcta.
    (newUser) => {
        if (newUser) {
            // Modo Edición: Cargamos los valores del usuario actual
            form.defaults({
                id: newUser.id,
                name: newUser.name,
                email: newUser.email,
                roleType: props.roleType,
                supervisor_id: newUser.supervisor_id,
                is_active: !!newUser.is_active,
            }).reset();
        } else {
            // Modo Creación: Usamos los defaults limpios
            form.defaults({
                id: undefined,
                name: "",
                email: "",
                roleType: props.roleType,
                supervisor_id: null,
                is_active: true,
            }).reset();
        }
    },
    { immediate: true }
);

const close = () => {
    emit("close");
    form.reset();
    form.clearErrors();
};
const submit = () => {
    // 🚨 CORRECCIÓN 2: Lógica de Edición vs. Creación
    const endpoint = isEditMode.value
        ? route("users.update", form.id)
        : route("users.store");

    const method = isEditMode.value ? "put" : "post";

    // Función para manejar el éxito y la notificación
    const handleSuccess = (message: string) => {
        // Ejecuta el cierre (que contiene el form.reset())
        close();
        ElMessage({
            type: "success",
            message: message,
        });
    };

    form.submit(method, endpoint, {
        onSuccess: () => {
            const successMessage = isEditMode.value
                ? "Usuario actualizado correctamente."
                : "Usuario creado correctamente.";
            handleSuccess(successMessage);
        },
        onError: () => {
            ElMessage.error(
                isEditMode.value
                    ? "Error al actualizar el usuario"
                    : "Error al crear el usuario"
            );
        },
        // Opcional: Asegurar que la contraseña no se envía si está vacía
        preserveState: true,
        preserveScroll: true,
    });
};
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
