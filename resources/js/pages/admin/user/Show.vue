<template>
    <div class="row">
        <div class="col-12 col-md-3 mb-2">
            <div class="p-4 bg-admin-dark">
                <div class="text-center">
                    <img :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + user.icon_id + '.png'"
                         class="pixel icon"
                         alt="Profile icon"
                         style="width: 7.5rem; height: 7.5rem;">
                    <h1>{{ user.name }}</h1>
                </div>

<!--                <p><strong>rank</strong></p>-->

                <p><strong>Email:</strong> {{ user.email }}</p>
                <p><strong>Icon ID:</strong> {{ user.icon_id }}</p>
                <p><strong>Private:</strong> {{ (user.private === 1 ? 'Yes' : 'No') }}</p>
                <p><strong>User ID:</strong> {{ user.id }}</p>
                <p><strong>Joined:</strong> {{ user.created_at | moment('d. M Y H:i') }}</p>
                <p><strong>Updated:</strong> {{ user.updated_at | moment('d. M Y H:i')  }}</p>
            </div>
        </div>

        <div class="col-12 col-md-7">
            <div class="px-4 pt-4 pb-2 bg-admin-dark">
                <h1>Accounts</h1>

                <div v-for="account in user.account" class="row mb-4">
                    <div class="col-md-5 mb-4">
                        <div class="p-4 bg-admin-info">
                            <h2>
                                <img v-if="account.account_type !== 'normal'"
                                     :src="'/images/' + account.account_type + '.png'"
                                     class="pixel"
                                     :alt="account.account_type + ' icon'"
                                     style="height: 1em; width: 1em;">
                                {{ account.username }}
                            </h2>
                            <p>
                                <span><strong>Rank: </strong>{{ account.rank }}</span>
                                <br>
                                <span><strong>Level: </strong>{{ account.level }}</span>
                                <br>
                                <span><strong>Total XP:</strong> {{ account.xp }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="p-4 bg-admin-info">
                            <accounthiscore :account="account"></accounthiscore>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
require('moment');

export default {
    name: "PageAdminUserShow",

    props: {
        user: {required: true},
    },
}
</script>

<style scoped>

</style>
