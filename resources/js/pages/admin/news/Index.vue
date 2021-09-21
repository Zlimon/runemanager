<template>
    <div>
        <div class="d-flex justify-content-between">
            <div class="col">
                <h1>News</h1>
            </div>

<!--            <form method="POST" action="{{ route('admin-create-newspost-category') }}" class="col-8 col-md-2">-->
<!--                @csrf-->

<!--                <div class="row">-->
<!--                    <div class="input-group">-->
<!--                        <input type="text"-->
<!--                               id="category"-->
<!--                               name="category"-->
<!--                               class="form-control @error('category') is-invalid @enderror"-->
<!--                               placeholder="Create new category"-->
<!--                               required>-->
<!--                        <button class="btn btn-primary">Create</button>-->
<!--                    </div>-->
<!--                </div>-->

<!--                @error('category')-->
<!--                    <span class="invalid-feedback" role="alert">-->
<!--                        <strong>{{ $message }}</strong>-->
<!--                    </span>-->
<!--                @enderror-->
<!--            </form>-->
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>News ID</th>
                        <th>Title</th>
                        <th class="d-none d-md-table-cell">Shortstory</th>
                        <th class="d-none d-md-table-cell">Category</th>
                        <th>Author</th>
                        <th class="d-none d-md-table-cell">Posted</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="news in newsPosts">
                        <th scope="row">{{ news.id }}</th>
                        <td>{{ news.title }}</td>
                        <td class="d-none d-md-table-cell">{{ news.shortstory }}</td>
                        <td class="d-none d-md-table-cell">{{ news.news_category.category }}</td>
                        <td>{{ news.user.name }}</td>
                        <td class="d-none d-md-table-cell">{{ news.created_at | moment('ddd. Do MMM HH:mm') }}</td>
                        <td>
                            <div class="d-flex justify-content-between">
                                <a :href="'/admin/news/' + news.id + '/show'" class="btn btn-success">Show</a>
                                <a :href="'/admin/news/' + news.id + '/edit'" class="btn btn-primary">Edit</a>
                                <a :href="'/admin/news/' + news.id + '/destroy'" class="btn btn-danger">Delete</a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
require('moment');

export default {
    name: "PageAdminNewsIndex",

    props: {
        newsPosts: {required: true},
    },
}
</script>

<style scoped>

</style>
