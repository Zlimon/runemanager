<template>
    <div>
        <link :href="'css/index.css'" rel="stylesheet">
        <link :href="'css/news.css'" rel="stylesheet">

        <div class="row">
            <div class="jumbotron d-flex flex-column flex-md-row justify-content-center justify-content-md-between align-items-center mb-3"
                 id="jumbotron">
                <div class="m-5">
                    <p class="display-1" style="font-family: 'runescape-smooth', sans-serif;">
                        Welcome to
                        <br>
    <!--                    {{ config('app.name', 'RuneManager') }}-->
                    </p>

                    <div class="d-flex">
                        <a :href="'/login'" class="btn btn-block button-combat-style-thin">
                            Log in
                        </a>

                        <a :href="'/register'" class="btn btn-block button-combat-style-thin">
                            Register
                        </a>
                    </div>
                </div>

                <div class="d-none d-md-block mx-4" style="margin-top: -4rem; width: 25rem; height: 27rem;">
                    <div class="bg-dark background-dialog-panel p-3">
                        <ul class="nav nav-tabs list-inline mx-auto justify-content-center" id="firstTab" role="tablist" style="border: none;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="discord-tab" data-bs-toggle="tab" data-bs-target="#discord" type="button" role="tab" aria-controls="discord" aria-selected="true">Discord</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab" aria-controls="members" aria-selected="false">Members</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="firstTabContent">
                            <div class="tab-pane fade show active" id="discord" role="tabpanel" aria-labelledby="discord-tab">
                                <div class="h-100 w-100">
    <!--                                TODO settings-->
                                    <fieldset>
                                        <iframe src="https://discordapp.com/widget?id=351850127209660416&theme=dark"
                                                title="Preview of RuneManager">
                                        </iframe>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="members-tab">
                                <div v-for="account in accounts">
                                    <p>{{ account.username }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col mb-3">
                <div class="row">
                    <div class="d-flex align-items-center">
                        <div class="col-6" style="background: url('images/scenery_1.png'); background-size: cover; background-position: center; height: 15rem;">
                            <span></span>
                        </div>
                        <div class="col px-3">
                            <h3 class="text-center header-chatbox-sword">About</h3>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="d-flex align-items-center">
                        <div class="col px-3">
                            <h3 class="text-center header-chatbox-sword">Features</h3>
                            <p class="text-end">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                        </div>
                        <div class="col-6" style="background: url('images/scenery_4.png'); background-size: cover; background-position: center; height: 15rem;">
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4 mb-3">
                <div class="bg-dark background-dialog-panel p-3">
                    <ul class="nav nav-tabs list-inline mx-auto justify-content-center" id="secondTab" role="tablist" style="border: none;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab" aria-controls="calendar" aria-selected="true">Calendar</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">Notifications</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="secondTabContent">
                        <div class="tab-pane fade show active" id="calendar" role="tabpanel" aria-labelledby="calendar-tab">
                            <calendar></calendar>
                        </div>
                        <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                            <h2>Notifications</h2>
                            <announcementall></announcementall>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 col-md-6">
                <div class="bg-dark background-dialog-panel p-3">
                    <h2 class="text-center header-chatbox-sword">Latest news and updates</h2>

                    <div v-if="newsPosts.length > 0">
                        <article v-for="newsPost in newsPosts" class="row">
                            <div class="col-4 d-none d-md-block">
                                <a :href="'/news/' + newsPost.id">
                                    <img :src="'/storage/newspost/' + newsPost.image.image_file_name + '.' + newsPost.image.image_file_extension"
                                         class="img-fluid w-100"
                                         :alt="newsPost.title + ' news post image'"
                                         :title="'Click here to read about ' + newsPost.title">
                                </a>
                            </div>
                            <div class="col-12 col-md-8 d-flex justify-content-between">
                                <div class="col">
                                    <h5 style="margin-bottom: 0;">
                                        <a :href="'/news/' + newsPost.id">{{ newsPost.title }}</a>
                                    </h5>
                                    <p>{{ newsPost.user.name }} | {{ newsPost.news_category.category }}</p>
                                    <p>{{ newsPost.shortstory }}</p>
                                    <p>
                                        <strong>
                                            <a :href="'/news/' + newsPost.id">
                                                Read more <i class="fas fa-long-arrow-alt-right"></i>
                                            </a>
                                        </strong>
                                    </p>
                                </div>
                                <div class="col-2">
                                    <div class="date text-center">
                                        <span class="month">{{ newsPost.created_at | moment('MMM') }}</span>
                                        <br>
                                        <span class="day">{{ newsPost.created_at | moment('Do') }}</span>
                                        <br>
                                        <div @click="previewNewsPost(newsPost)" class="btn button-combat-style-narrow">
                                            Preview
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                        </article>

                        <div class="text-end">
                            <a :href="'/news'" class="btn button-combat-style-narrow">
                                Browse newsposts <i class="fas fa-long-arrow-alt-right"></i>
                            </a>
                        </div>
                    </div>
                    <div v-else class="text-center py-5">
                        <img :src="'images/ignore.png'"
                             class="pixel icon"
                             alt="Sad face">
                        <h1>Nothing interesting is happening</h1>
                    </div>
                </div>
            </div>

            <div v-if="previewPost" class="col d-none d-md-block">
                <div class="card bg-dark background-dialog-panel">
                    <div class="category">{{ previewPost.news_category.category }}</div>
                    <img :src="'/storage/newspost/' + previewPost.image.image_file_name + '.' + previewPost.image.image_file_extension"
                         class="card-img-top"
                         :alt="previewPost.title + ' news post image'"
                         :title="'Click here to read about ' + previewPost.title"
                         style="max-height: 15rem; object-fit: cover;">

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">{{ previewPost.title }}</h5>
                                <p class="card-text"><em>{{ previewPost.shortstory }}</em></p>
                            </div>

                            <div class="date text-center">
                                <span class="month">{{ previewPost.created_at | moment('MMM') }}</span>
                                <br>
                                <span class="day">{{ previewPost.created_at | moment('Do') }}</span>
                            </div>
                        </div>
                        <div class="text-break">{{ previewPost.longstory }}</div>
                    </div>
                    <div class="card-footer text-muted text-end">
                        <a :href="'/news/' + previewPost.id" class="btn button-combat-style-narrow">
                            Read more <i class="fas fa-long-arrow-alt-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
require('moment');

export default {
    name: "PageIndex",

    props: {
        accounts: {required: true},
        newsPosts: {required: true},
    },

    methods: {
        previewNewsPost(newsPost) {
            this.previewPost = newsPost;
            console.log(newsPost)
        }
    },

    data() {
        return {
            previewPost: this.newsPosts[0],
        };
    },
}
</script>

<style scoped>
    fieldset {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
    }

    iframe {
        width: 100%;
        height: 100%;
    }
</style>
