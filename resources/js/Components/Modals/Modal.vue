<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);
const dialog = ref(null);

watch(() => props.show, (show) => {
    if (show) {
        dialog.value?.showModal();
    } else {
        dialog.value?.close();
    }
});

onMounted(() => {
    if (props.show) {
        dialog.value?.showModal();
    }
});

// Native <dialog> emits `close` when dismissed by the backdrop button or Esc;
// keep the parent's `show` in sync (no-op when we closed it ourselves).
const onClose = () => {
    if (props.show) {
        emit('close');
    }
};

// Esc triggers `cancel`; block it for non-closeable modals.
const onCancel = (e) => {
    if (!props.closeable) {
        e.preventDefault();
    }
};

const maxWidthClass = computed(() => ({
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
}[props.maxWidth]));
</script>

<template>
    <dialog ref="dialog" class="modal" @close="onClose" @cancel="onCancel">
        <div class="modal-box w-full p-0" :class="maxWidthClass">
            <slot />
        </div>
        <form v-if="closeable" method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</template>
