<template>
    <div>
        <div v-if="success" class="alert alert-success mt-3">
            Newspost posted!
        </div>

        <form @submit.prevent="submit" enctype="multipart/form-data">
            <div class="form-group row">
                <label for="image" class="col-md-4 col-form-label text-md-right">Image file</label>

                <div class="col-md-6">
                    <input id="image" type="file"
                           class="form-control-file border rounded bg-white p-1 "
                           name="image" style="color: black;">
                </div>
            </div>

            <div class="form-group row">
                <label for="title" class="col-md-4 col-form-label text-md-right">Title</label>

                <div class="col-md-6">
                    <input id="title" type="text" class="form-control" name="title"
                           value="" v-model="fields.title" required autofocus>

                    <div v-if="errors && errors.title" class="text-danger">
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ errors.title[0] }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label for="news_category_id" class="col-md-4 col-form-label text-md-right">Category</label>

                <div class="col-md-6">
                    <select id="news_category_id" class="form-control" name="news_category_id"
                            v-model="fields.news_category_id">
                        <option :value="category.id" v-for="category in categories">
                            {{ category.category }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="shortstory" class="col-md-4 col-form-label text-md-right">Shortstory</label>

                <div class="col-md-6">
                    <input id="shortstory" type="text" class="form-control"
                           name="shortstory" value="" v-model="fields.shortstory" required>

                    <div v-if="errors && errors.shortstory" class="text-danger">
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ errors.shortstory[0] }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <div v-if="errors && errors.longstory" class="text-danger">
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ errors.longstory[0] }}</strong>
                </span>
            </div>

            <ckeditor id="longstory" class="form-control" name="longstory" :editor="editor" v-model="fields.longstory" :config="editorConfig"></ckeditor>

            <div class="form-group row mb-0">
                <button type="submit" class="btn btn-primary btn-lg btn-block mt-3">Post news</button>
            </div>
        </form>
    </div>
</template>

<script>
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props: {
        categories: {required: true},
    },

    data() {
        return {
            fields: {},
            errors: {},
            success: false,
            loaded: true,

            editor: ClassicEditor,
            editorData: '<p>Content of the editor.</p>',
            editorConfig: {
                // The configuration of the editor.
            }
        };
    },

    methods: {
        submit() {
            if (this.loaded) {
                this.loaded = false;
                this.success = false;
                this.errors = {};
                axios
                    .post('/api/admin/news/create/', this.fields)
                    .then((response) => {
                        console.log(response.data); // TODO local notification
                        this.fields = {};
                        this.loaded = true;
                        this.success = true;
                    })
                    .catch(error => {
                        this.loaded = true;
                        if (error.response.status === 422) {
                            this.errors = error.response.data.errors || {};
                        }
                    });
            }
        },
    },
}
</script>

<style>
.ck-editor__editable {
    min-height: 500px;
    color: black;
}
</style>
