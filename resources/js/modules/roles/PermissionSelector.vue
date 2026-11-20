<script setup lang="ts">
import { computed, ref } from 'vue';

interface PermissionOption {
    id: number;
    name: string;
    group: string;
}

const props = withDefaults(defineProps<{
    permissions: PermissionOption[];
    modelValue: string[];
    disabled?: boolean;
}>(), {
    disabled: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string[]];
}>();

const search = ref('');

const groups = computed(() => {
    const filtered = props.permissions.filter((permission) =>
        permission.name.toLowerCase().includes(search.value.trim().toLowerCase()),
    );
    const grouped = new Map<string, PermissionOption[]>();

    filtered.forEach((permission) => {
        const group = permission.group || 'General';
        grouped.set(group, [...(grouped.get(group) ?? []), permission]);
    });

    return Array.from(grouped.entries()).map(([name, items]) => ({ name, items }));
});

function updatePermission(name: string, event: Event): void {
    const checked = (event.target as HTMLInputElement).checked;
    const selected = new Set(props.modelValue);

    if (checked) selected.add(name);
    else selected.delete(name);

    emit('update:modelValue', Array.from(selected));
}

function updateGroup(names: string[], event: Event): void {
    const checked = (event.target as HTMLInputElement).checked;
    const selected = new Set(props.modelValue);

    names.forEach((name) => checked ? selected.add(name) : selected.delete(name));
    emit('update:modelValue', Array.from(selected));
}
</script>

<template>
    <div class="permission-selector">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <span class="fw-semibold">Permissions</span>
                <span class="badge text-bg-primary ms-2">{{ modelValue.length }} selected</span>
            </div>
            <input v-model="search" type="search" class="form-control form-control-sm permission-search" placeholder="Find permission...">
        </div>

        <div v-if="groups.length" class="row g-3">
            <div v-for="group in groups" :key="group.name" class="col-12 col-lg-6">
                <section class="border rounded-3 h-100">
                    <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 bg-body-tertiary border-bottom rounded-top-3">
                        <span class="fw-semibold small">{{ group.name }}</span>
                        <label class="form-check form-check-sm mb-0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                :disabled="disabled"
                                :checked="group.items.every((item) => modelValue.includes(item.name))"
                                @change="updateGroup(group.items.map((item) => item.name), $event)"
                            >
                            <span class="form-check-label small">All</span>
                        </label>
                    </div>
                    <div class="p-3 d-flex flex-column gap-2">
                        <label v-for="permission in group.items" :key="permission.id" class="form-check mb-0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                :value="permission.name"
                                :disabled="disabled"
                                :checked="modelValue.includes(permission.name)"
                                @change="updatePermission(permission.name, $event)"
                            >
                            <span class="form-check-label small">{{ permission.name }}</span>
                        </label>
                    </div>
                </section>
            </div>
        </div>
        <div v-else class="text-center text-body-secondary py-4">No matching permissions found.</div>
    </div>
</template>

<style scoped>
.permission-search {
    max-width: 240px;
}
</style>
