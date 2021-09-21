<template>
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="p-4 bg-admin-dark">
                <div class="text-center pb-3">
                    <img :src="'/storage/default.png'"
                         class="w-50"
                         :alt="newsPost.title + ' news post image'">
                </div>
                <h1 class="text-center">{{ newsPost.title }}</h1>
                <p class="text-center"><em>{{ newsPost.shortstory }}</em></p>
                <div v-html="newsPost.longstory"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "PageAdminNewsShow",

    props: {
        newsPost: {required: true},
    },
}
</script>

<style scoped>

</style>
