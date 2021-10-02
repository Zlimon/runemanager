<template>
    <div class="row">
        <div class="col-12 col-md-5 mb-2">
            <div class="bg-admin-dark p-4">
                <h1>Update newspost</h1>

                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <label for="user" class="col-sm-3 col-form-label">Author</label>
                        <div class="col-sm-9">
                            <input v-model="fields.user_id"
                                   type="text"
                                   id="user"
                                   name="user"
                                   class="form-control"
                                   placeholder="Search for an user"
                                   list="users"
                                   required>
                            <datalist id="users">
                                <option v-for="user in users" :value="user.name">
                                    {{ user.email }}
                                </option>
                            </datalist>
                            <div v-if="this.errors && this.errors.user_id !== undefined">
                                <small v-for="error in this.errors.user_id" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="image" class="col-sm-3 col-form-label">Image</label>
                        <div class="col-sm-9">
                            <input type="file"
                                   id="image"
                                   name="image"
                                   class="form-control">
                            <div v-if="this.errors && this.errors.image !== undefined">
                                <small v-for="error in this.errors.image" class="text-danger">{{ error }}<br></small>
                            </div>
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
                            <div v-if="this.errors && this.errors.title !== undefined">
                                <small v-for="error in this.errors.title" class="text-danger">{{ error }}<br></small>
                            </div>
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
                            <div v-if="this.errors && this.errors.news_category_id !== undefined">
                                <small v-for="error in this.errors.news_category_id" class="text-danger">{{ error }}<br></small>
                            </div>
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
                            <div v-if="this.errors && this.errors.shortstory !== undefined">
                                <small v-for="error in this.errors.shortstory" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>
                    </div>

                    <div v-if="this.errors && this.errors.longstory !== undefined">
                        <small v-for="error in this.errors.longstory" class="text-danger">{{ error }}<br></small>
                    </div>
                    <ckeditor v-model="newsPost.longstory"
                              :editor="editor"
                              :config="editorConfig"
                              id="longstory"
                              name="longstory"
                              class="form-control"
                              required></ckeditor>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div @click="updateNewsPost"
                                 class="btn btn-success d-block">
                                Update newspost
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col">
            <div class="bg-admin-dark p-4">
                <div class="text-center pb-3">
                    <img :src="'/storage/default.png'"
                         class="w-50"
                         :alt="fields.title + ' news post image'">
                </div>
                <h1 class="text-center">{{ fields.title }}</h1>
                <p class="text-center"><em>{{ fields.shortstory }}</em></p>
                <div v-html="fields.longstory"></div>
            </div>
        </div>
    </div>
</template>

<script>
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    name: "PageAdminNewsEdit",

    props: {
        users: {required: true},
        newsPost: {required: true},
        categories: {required: true},
    },

    methods: {
        updateNewsPost() {
            axios
                .put('/api/admin/news/' + this.newsPost.id + '/update', this.fields)
                .then((response) => {
                    this.errors = null;

                    this.doSuccess('Successfully updated newspost "' + this.newsPost.title + '".');
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.errors = error.response.data.errors;
                });
        },
    },

    data() {
        return {
            fields: {},

            editor: ClassicEditor,
            editorData: '<p>Content of the editor.</p>',
            editorConfig: {
                // The configuration of the editor.
            },

            errors: null,
        };
    },

    mounted() {
        this.fields = this.newsPost;
    },
}
</script>

<style scoped>
.ck-editor__editable {
    min-height: 500px;
    color: black;
}
</style>
