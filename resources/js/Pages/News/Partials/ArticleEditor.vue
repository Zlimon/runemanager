<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';

/*
 * The article writing surface. TipTap (ProseMirror) rather than a batteries-
 * included editor because it is headless: it renders bare elements we class
 * ourselves, so the writing area picks up the DaisyUI theme and the active
 * resource pack's textures. An editor that ships its own theme CSS would sit on
 * the page looking like a different application.
 *
 * The editor emits HTML, which the server sanitises against an allowlist before
 * storing (see App\Services\News\SanitizeArticleHtml). The toolbar here is
 * deliberately limited to exactly what that allowlist permits — offering a
 * control whose output is stripped on save would just lose the author's work.
 */
const props = defineProps({
    modelValue: { type: String, default: '' },
    error: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

/*
 * Guards the watcher below. Every keystroke emits new HTML, which flows back in
 * through `modelValue`; without this flag the watcher would treat the parent's
 * echo as an external change and reset the document, dropping the cursor to the
 * start of the article mid-sentence.
 */
const emitting = ref(false);

const uploadError = ref(null);
const uploading = ref(false);
const fileInput = ref(null);

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        // StarterKit v3 already bundles link and underline, so they are
        // configured here rather than registered again — registering an
        // extension twice makes TipTap warn and use one at random.
        StarterKit.configure({
            heading: { levels: [2, 3, 4] },
            link: {
                openOnClick: false,
                autolink: true,
                // Mirrors the purifier profile, which drops any other scheme.
                protocols: ['http', 'https', 'mailto'],
            },
        }),
        Image.configure({
            inline: false,
            HTMLAttributes: { class: 'rounded' },
        }),
        Placeholder.configure({
            placeholder: 'Write the article…',
        }),
    ],
    editorProps: {
        attributes: {
            class: 'article-body min-h-[22rem] focus:outline-none',
        },
    },
    onUpdate: ({ editor }) => {
        emitting.value = true;
        // An empty document still serialises as "<p></p>", which would pass the
        // server's `required` check on an article nobody wrote. Send an empty
        // string so validation reports it properly.
        emit('update:modelValue', editor.isEmpty ? '' : editor.getHTML());
        emitting.value = false;
    },
});

// Reset the document when the parent swaps it out for reasons of its own —
// loading a different post, or clearing the form after a save.
watch(
    () => props.modelValue,
    (value) => {
        if (emitting.value || !editor.value) {
            return;
        }
        if (value !== editor.value.getHTML()) {
            editor.value.commands.setContent(value || '', { emitUpdate: false });
        }
    },
);

onBeforeUnmount(() => editor.value?.destroy());

const isActive = (name, attrs = {}) => editor.value?.isActive(name, attrs) ?? false;

const setLink = () => {
    const previous = editor.value?.getAttributes('link').href ?? '';
    // eslint-disable-next-line no-alert
    const url = window.prompt('Link URL', previous);

    // Cancelled — leave the document alone. An empty string is a deliberate
    // "remove this link", which is different.
    if (url === null) {
        return;
    }

    if (url === '') {
        editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();

        return;
    }

    editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

/*
 * Inline images are uploaded the moment they are picked and inserted by URL,
 * rather than embedded as base64. A base64 body would bloat the stored article
 * and be stripped by the sanitiser's scheme allowlist anyway.
 */
const insertImage = async (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    uploading.value = true;
    uploadError.value = null;

    const body = new FormData();
    body.append('image', file);

    try {
        // window.axios, like the rest of the app's JSON calls: it sends the
        // XSRF-TOKEN cookie for us, which a bare fetch() would not.
        const { data } = await window.axios.post(route('news.images.store'), body);
        editor.value?.chain().focus().setImage({ src: data.data.url }).run();
    } catch (error) {
        uploadError.value = error.response?.data?.errors?.image?.[0]
            ?? 'The image could not be uploaded.';
    } finally {
        uploading.value = false;
        // Clear the input so picking the same file twice fires `change` again.
        event.target.value = '';
    }
};
</script>

<template>
    <div>
        <div class="rounded border" :class="error ? 'border-error' : 'border-base-300'">
            <!-- Toolbar. Every control maps to a tag the server's allowlist keeps. -->
            <div v-if="editor" class="flex flex-wrap items-center gap-1 border-b border-base-300 p-2">
                <button type="button" class="btn btn-ghost btn-xs font-bold"
                        :class="{ 'btn-active': isActive('bold') }"
                        title="Bold" @click="editor.chain().focus().toggleBold().run()">
                    B
                </button>
                <button type="button" class="btn btn-ghost btn-xs italic"
                        :class="{ 'btn-active': isActive('italic') }"
                        title="Italic" @click="editor.chain().focus().toggleItalic().run()">
                    I
                </button>
                <button type="button" class="btn btn-ghost btn-xs underline"
                        :class="{ 'btn-active': isActive('underline') }"
                        title="Underline" @click="editor.chain().focus().toggleUnderline().run()">
                    U
                </button>
                <button type="button" class="btn btn-ghost btn-xs line-through"
                        :class="{ 'btn-active': isActive('strike') }"
                        title="Strikethrough" @click="editor.chain().focus().toggleStrike().run()">
                    S
                </button>

                <span class="mx-1 h-5 w-px bg-base-300" aria-hidden="true" />

                <button v-for="level in [2, 3, 4]" :key="level"
                        type="button" class="btn btn-ghost btn-xs"
                        :class="{ 'btn-active': isActive('heading', { level }) }"
                        :title="`Heading ${level}`"
                        @click="editor.chain().focus().toggleHeading({ level }).run()">
                    H{{ level }}
                </button>

                <span class="mx-1 h-5 w-px bg-base-300" aria-hidden="true" />

                <button type="button" class="btn btn-ghost btn-xs"
                        :class="{ 'btn-active': isActive('bulletList') }"
                        title="Bullet list" @click="editor.chain().focus().toggleBulletList().run()">
                    &bull; List
                </button>
                <button type="button" class="btn btn-ghost btn-xs"
                        :class="{ 'btn-active': isActive('orderedList') }"
                        title="Numbered list" @click="editor.chain().focus().toggleOrderedList().run()">
                    1. List
                </button>
                <button type="button" class="btn btn-ghost btn-xs"
                        :class="{ 'btn-active': isActive('blockquote') }"
                        title="Quote" @click="editor.chain().focus().toggleBlockquote().run()">
                    &ldquo;
                </button>
                <button type="button" class="btn btn-ghost btn-xs"
                        title="Divider" @click="editor.chain().focus().setHorizontalRule().run()">
                    &mdash;
                </button>

                <span class="mx-1 h-5 w-px bg-base-300" aria-hidden="true" />

                <button type="button" class="btn btn-ghost btn-xs"
                        :class="{ 'btn-active': isActive('link') }"
                        title="Link" @click="setLink">
                    Link
                </button>
                <button type="button" class="btn btn-ghost btn-xs"
                        :disabled="uploading"
                        title="Insert image" @click="fileInput.click()">
                    <span v-if="uploading" class="loading loading-spinner loading-xs" />
                    <span v-else>Image</span>
                </button>

                <span class="ms-auto flex items-center gap-1">
                    <button type="button" class="btn btn-ghost btn-xs"
                            title="Undo" @click="editor.chain().focus().undo().run()">
                        Undo
                    </button>
                    <button type="button" class="btn btn-ghost btn-xs"
                            title="Redo" @click="editor.chain().focus().redo().run()">
                        Redo
                    </button>
                </span>
            </div>

            <EditorContent :editor="editor" class="p-4" />
        </div>

        <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="insertImage">

        <p v-if="uploadError" class="mt-2 text-sm text-error">{{ uploadError }}</p>
    </div>
</template>
