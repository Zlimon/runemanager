<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';

/*
 * A clickable thumbnail that opens the full image in a fullscreen overlay.
 * Closes on backdrop click, the close button, or Esc. Used for feed-event
 * screenshots, but generic enough for any image.
 */
const props = defineProps({
    src: { type: String, required: true },
    alt: { type: String, default: 'Screenshot' },
    thumbClass: { type: String, default: 'max-h-48 w-auto object-cover' },
});

const open = ref(false);

const show = () => { open.value = true; };
const close = () => { open.value = false; };

const onKeydown = (e) => {
    if (e.key === 'Escape') {
        close();
    }
};

// Lock body scroll + listen for Esc only while open.
watch(open, (isOpen) => {
    document.documentElement.classList.toggle('overflow-hidden', isOpen);
    if (isOpen) {
        window.addEventListener('keydown', onKeydown);
    } else {
        window.removeEventListener('keydown', onKeydown);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown);
    document.documentElement.classList.remove('overflow-hidden');
});
</script>

<template>
    <div class="w-fit">
        <button type="button" @click="show"
                class="block w-fit overflow-hidden rounded border pack-accent-border transition hover:opacity-90">
            <img :src="src" :alt="alt" loading="lazy" :class="thumbClass">
        </button>

    <Teleport to="body">
        <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0"
                    leave-active-class="transition ease-in duration-100" leave-to-class="opacity-0">
            <div v-if="open" class="fixed inset-0 z-[2000] flex items-center justify-center bg-black/80 p-4"
                 @click="close">
                <button type="button" @click.stop="close" aria-label="Close"
                        class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-black/50 text-2xl text-white hover:bg-black/70">
                    &times;
                </button>
                <img :src="src" :alt="alt" @click.stop
                     class="max-h-[90vh] max-w-[90vw] rounded object-contain shadow-2xl">
            </div>
        </Transition>
    </Teleport>
    </div>
</template>
