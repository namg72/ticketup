<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { formatDate } from "@/Helpers/dateFormatter";
import FormTicket from "@/Components/Tickets/FormTicket.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps<{
    ticket: {
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
    };
    categories: {
        id: number;
        name: string;
    }[];
    role: string;
}>();

const form = useForm({
    id: props.ticket.id,
    title: props.ticket.title ?? "",
    description: props.ticket.description ?? "",
    category: props.ticket.category!.name ?? null,
    supervisor: props.ticket.supervisor?.name,
    total_amount: props.ticket.total_amount ?? "",
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="w-full max-w-xl mx-auto p-6">
            <h1 class="text-2xl font-bold mb-4">
                Editar ticket Nº {{ form.id }}
            </h1>

            <!-- Formulario Element Plus -->
            <FormTicket
                :ticket="ticket"
                :categories="categories"
                :role="role"
                mode="edit"
            >
            </FormTicket>
        </div>
    </AuthenticatedLayout>
</template>
