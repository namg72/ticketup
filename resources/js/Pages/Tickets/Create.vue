<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { formatDate } from "@/Helpers/dateFormatter";
import FormTicket from "@/Components/Tickets/FormTicket.vue";
import { User } from "@/types";

const props = defineProps<{
    user: User;
    categories: {
        id: number;
        name: string;
    }[];
}>();
</script>

<template>
    <AuthenticatedLayout>
        <div class="w-full max-w-xl mx-auto p-6">
            <h1 class="text-2xl font-bold mb-4">Subir nuevo Ticket</h1>

            <!-- Formulario Element Plus -->
            <FormTicket :categories="categories" mode="create"> </FormTicket>
        </div>
    </AuthenticatedLayout>
</template>
