<template>
    <div>
        <div v-if="success" class="alert alert-success mt-3">
            Newspost posted!
        </div>

        <form @submit.prevent="submit" enctype="multipart/form-data">
            <div class="row mb-3">
                <label for="image" class="col-sm-3 col-form-label">Image</label>
                <div class="col-sm-9">
                    <input type="file"
                           id="image"
                           name="image"
                           class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <label for="title" class="col-sm-3 col-form-label">Title</label>
                <div class="col-sm-9">
                    <input v-model="fields.title"
                           type="text"
                           id="title"
                           name="title"
                           class="form-control"
                           required autofocus>
                </div>
            </div>

            <div class="row mb-3">
                <label for="news_category_id" class="col-sm-3 col-form-label">Category</label>
                <div class="col-sm-9">
                    <select v-model="fields.news_category_id"
                            id="news_category_id"
                            name="news_category_id"
                            class="form-select">
                        <option v-for="category in categories" :value="category.id">
                            {{ category.category }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label for="shortstory" class="col-sm-3 col-form-label">Short story</label>
                <div class="col-sm-9">
                    <input v-model="fields.shortstory"
                           type="text"
                           id="shortstory"
                           name="shortstory"
                           class="form-control"
                           required>
                </div>
            </div>

            <div v-if="errors && errors.longstory" class="text-danger">
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ errors.longstory[0] }}</strong>
                </span>
            </div>

            <ckeditor v-model="fields.longstory"
                      :editor="editor"
                      :config="editorConfig"
                      id="longstory"
                      name="longstory"
                      class="form-control"
                      required></ckeditor>

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
