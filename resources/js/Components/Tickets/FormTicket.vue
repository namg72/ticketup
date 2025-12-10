<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import { formatDate } from "@/Helpers/dateFormatter";
import type { UploadFile } from "element-plus";
import { computed, watch } from "vue";
import { normalizeAmount } from "@/Helpers/normalizeAmount";

const props = defineProps<{
    ticket?: {
        id: number;
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
    role?: string;
    mode: string;
}>();
const existingImageName = computed(() => {
    if (!props.ticket || !props.ticket.uri) return "";
    // si es "tickets/1-xxxx.jpg", nos quedamos con lo de después de la última barra
    return props.ticket.uri.split("/").pop() ?? "";
});
const form = useForm({
    id: props.ticket?.id ?? "",
    title: props.ticket?.title ?? "",
    description: props.ticket?.description ?? "",
    category_id: props.ticket?.category!.id ?? null,
    supervisor: props.ticket?.supervisor?.name,
    total_amount: props.ticket?.total_amount ?? "",
    image: null as File | null,
});

const handleUploadFile = (uploadFile: UploadFile) => {
    if (!uploadFile.raw) {
        // por si acaso, limpiamos el campo
        form.image = null;
        return;
    }

    form.image = uploadFile.raw as File;
};

const submit = () => {
    if (props.mode === "create") {
        // Crear: siempre envías image (es obligatoria)
        form.post(route("tickets.store"));
    } else {
        // Editar: si no hay imagen nueva, quitamos el campo antes de enviar
        form.transform((data) => {
            // data es una copia de form.data()
            if (!data.image) {
                const { image, ...rest } = data; // eliminamos image
                return rest;
            }
            return data;
        }).put(route("tickets.update", props.ticket!.id), {
            onFinish: () => {
                // volvemos a dejar el form "normal", sin transformaciones raras
                form.transform((data) => data);
            },
        });
    }
};

const titleError = computed(() => {
    if (!form.title) {
        return "El título es obligatorio";
    }

    return form.errors.title;
});
const categoryError = computed(() => {
    if (!form.category_id) {
        return " La categoría es obligatoria";
    }

    return form.errors.category_id;
});
const totalAmuntError = computed(() => {
    if (!form.total_amount) {
        return "El Importe es obligatorio";
    }

    return form.errors.total_amount;
});
const imageError = computed(() => {
    // Si el backend ya mandó un error concreto, enseñamos ese
    if (form.errors.image) {
        return form.errors.image;
    }

    // CREATE: siempre obligatorio subir imagen
    if (props.mode === "create" && !form.image) {
        return "El documento es obligatorio";
    }

    // EDIT: solo obligatorio si NO hay imagen nueva y NO hay imagen antigua
    if (
        props.mode === "edit" &&
        !form.image &&
        (!props.ticket || !props.ticket.uri)
    ) {
        return "El documento es obligatorio";
    }

    // sin error
    return "";
});

const onTotalAmountInput = (value: string | number) => {
    normalizeAmount(value);
};
</script>

<template>
    <!-- Formulario Element Plus -->
    <div class="w-full">
        <h2>Comentarios</h2>
    </div>
    <el-form :model="form" label-position="top" @submit.prevent="submit">
        <template v-if="mode === 'edit'">
            <!-- Fecha craacion-->
            <el-form-item label="Fecha de creación">
                <el-input
                    :model-value="formatDate(ticket!.created_at) ?? ''"
                    readonly
                />
            </el-form-item>
            <!-- Fecha acutalizacion-->
            <el-form-item
                label="Fecha de acutalizacion"
                v-if="(ticket!.created_at !== ticket!.updated_at && mode=='edit')"
            >
                <el-input
                    :model-value="formatDate(ticket!.updated_at) ?? ''"
                    readonly
                />
            </el-form-item>
        </template>
        <!-- Empleado-->
        <el-form-item label="Empleado" v-if="mode === 'edit'">
            <el-input :model-value="ticket!.user?.name ?? ''" readonly />
        </el-form-item>

        <!-- Título -->
        <div>
            <el-form-item label=" * Título" :error="titleError">
                <el-input v-model="form.title" />
            </el-form-item>
        </div>

        <!-- Descripción -->
        <el-form-item label="Descripción">
            <el-input v-model="form.description" type="textarea" :rows="4" />
        </el-form-item>

        <!-- Categoría -->
        <el-form-item label="* Categoría" :error="categoryError">
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

        <!-- Supervisor (solo lectura) -->
        <el-form-item label="Supervisor" v-if="role === 'admin'">
            <el-input :model-value="form.supervisor ?? ''" readonly />
        </el-form-item>

        <!-- Total de gasto -->
        <el-form-item label="* Total gasto" :error="totalAmuntError">
            <el-input
                v-model="form.total_amount"
                type="text"
                @input="onTotalAmountInput"
            />
        </el-form-item>

        <!-- Subir imagen -->
        <el-form-item label="* Documento" :error="imageError">
            <el-upload
                drag
                :auto-upload="false"
                :show-file-list="false"
                :on-change="handleUploadFile"
            >
                <div class="el-upload__text">
                    Arrastra el archivo aquí o
                    <em>haz clic para seleccionar</em>
                </div>

                <div
                    class="el-upload__text mt-2"
                    v-if="mode === 'edit' && existingImageName && !form.image"
                >
                    <p>Archivo actual: {{ existingImageName }}</p>
                </div>
                <div class="el-upload__text" v-if="form.image !== null">
                    <p>{{ form.image.name }}</p>
                </div>
                <template #tip>
                    <div class="el-upload__tip">
                        Formatos permitidos: PDF, JPG, PNG. Tamaño máximo 2MB.
                    </div>
                </template>
            </el-upload>
        </el-form-item>

        <div class="mt-6">
            <el-button type="success" native-type="submit"> Enviar </el-button>

            <span class="mr-6"></span>
            <Link :href="route('dashboard')">
                <el-button type="danger"> Cancelar </el-button>
            </Link>
        </div>
        <!-- De momento sin botón de guardar -->
    </el-form>
</template>
