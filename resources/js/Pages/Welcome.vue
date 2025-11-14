<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Head, Link } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Welcome" />

    <!-- Contenedor general -->
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Barra superior -->
        <div class="flex justify-end items-center px-6 py-4 bg-white shadow-sm">
            <nav class="space-x-6 text-gray-600 text-L font-large mr-6">
                <Link
                    :href="route('login')"
                    class="hover:text-gray-900 transition-colors"
                >
                    Ingresa
                </Link>
                <Link
                    :href="route('register')"
                    class="hover:text-gray-900 transition-colors"
                >
                    Registrate
                </Link>
            </nav>
        </div>

        <!-- Contenido centrado -->
        <div
            class="flex flex-1 flex-col items-center justify-center text-center"
        >
            <ApplicationLogo class="w-48 h-48 mb-8" />
            <h1 class="text-5xl font-extrabold text-gray-900 mb-4">
                Bienvenido a <span class="text-teal-600">TicketUp</span>
            </h1>
            <p class="text-lg text-gray-600 max-w-xl">
                Gestiona tus tickets, eventos y clientes fácilmente desde un
                solo lugar. Inicia sesión o regístrate para comenzar.
            </p>
        </div>

        <!-- Pie de página -->
        <footer class="py-6 text-center text-gray-500 text-sm">
            Laravel v{{ $page.props.laravelVersion }} (PHP v{{
                $page.props.phpVersion
            }})
        </footer>
    </div>
</template>
