<template>
    <div>
        <ckeditor :editor="editor" v-model="editorData" :config="editorConfig" :class="form-control" :id="longstory" :name="longstory"></ckeditor>
    </div>
</template>

<script>
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    export default {
        name: 'app',
        data() {
            return {
                editor: ClassicEditor,
                editorData: '<p>Content of the editor.</p>',
                editorConfig: {
                    // The configuration of the editor.
                }
            };
        }
    }
</script>

<style>
    .ck-editor__editable {
        min-height: 500px;
    }
</style>
