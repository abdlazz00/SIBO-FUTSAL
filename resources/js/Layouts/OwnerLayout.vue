<script setup lang="ts">
import AdminLayout from './AdminLayout.vue';
</script>

<template>
    <AdminLayout>
        <!-- Forward default slot to AdminLayout -->
        <slot></slot>
    </AdminLayout>
</template>
