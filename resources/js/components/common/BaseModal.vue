<script setup lang="ts">
withDefaults(defineProps<{
    modelValue: boolean;
    title: string;
    size?: 'lg' | 'xl';
}>(), {
    size: 'lg',
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

function close(): void {
    emit('update:modelValue', false);
}
</script>

<template>
    <Teleport to="body">
        <div v-if="modelValue" class="modal-backdrop fade show" @click="close"></div>
        <div
            v-if="modelValue"
            class="modal fade show d-block"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            @keydown.esc="close"
            @click.self="close"
        >
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" :class="`modal-${size}`">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-semibold">{{ title }}</h5>
                        <button type="button" class="btn-close" aria-label="Close" @click="close"></button>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </Teleport>
</template>
