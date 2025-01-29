<template>
    <div>
        <div class="row">
            <div class="col-md-5">
                <div v-if="loading">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>

                <div v-else>
                    <div class="col-md-12">
                        <h1 class="text-center header-chatbox-sword">{{ accountData.username }}</h1>

                        <div class="row">
                            <img
                                :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + accountData.user.icon_id + '.png'"
                                class="pixel icon"
                                alt="Profile icon"
                                style="width: 7.5rem; height: 7.5rem;">

                            <div class="col">
                                <span>Rank: <strong>{{ accountData.rank }}</strong></span>
                                <br>
                                <span>Total XP: <strong>{{ accountData.xp }}</strong></span>
                                <br>
                                <span>Total Level: <strong>{{ accountData.level }}</strong></span>
                                <br>
                                <span>Joined: <strong>{{ accountData.joined }}</strong></span>
                                <br>
                                <span>Online? <strong>{{ online }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div v-if="skills">
                    <div class="row">
                        <h3 class="text-center header-chatbox-sword" style="width: 100%;">Skills</h3>

                        <div class="btn background-world-map mr-3" style="position: absolute; right: 0;"
                             v-on:click="toggle">
                            <img alt="Bosses icon"
                                 class="pixel icon-small"
                                 src="/images/boss/boss.png"
                                 title="Click here to see the boss hiscores">
                        </div>
                    </div>
                </div>
                <div v-else>
                    <div class="row">
                        <h3 class="text-center header-chatbox-sword" style="width: 100%;">Bosses</h3>

                        <div class="btn background-world-map mr-3" style="position: absolute; right: 0;"
                             v-on:click="toggle">
                            <img alt="Skills icon"
                                 class="pixel icon-small"
                                 src="/images/skill/total.png"
                                 title="Click here to see the skill hiscores">
                        </div>
                    </div>
                </div>

                <keep-alive>
                    <component v-bind:is="component" :account='account' @load="onLoadAccount"/>
                </keep-alive>
            </div>
        </div>
    </div>
</template>

<script>
import AccountSkillHiscore from './account/SkillHiscore.vue'
import AccountBossHiscore from './account/BossHiscore.vue'

export default {
    props: {
        account: {type: String, required: true},
    },

    methods: {
        onLoadAccount(account) {
            this.accountData = account;
            this.loading = false;
            this.online = account.online;
        },

        toggle() {
            if (this.component === AccountSkillHiscore) {
                this.component = AccountBossHiscore;
                this.skills = false;
            } else {
                this.component = AccountSkillHiscore;
                this.skills = true;
            }
        }
    },

    components: {
        'skillHiscore': AccountSkillHiscore,
        'bossHiscore': AccountBossHiscore,
    },

    data() {
        return {
            loading: true,
            accountData: {},
            skills: true,
            component: AccountSkillHiscore,
            online: {},
        }
    },

    created() {
        window.Echo.channel('account-online')
            .listen('AccountOnline', (e) => {
                // TODO rework...
                if (this.accountData.id === e.account[0]) {
                    if (e.account[1] === 1) {
                        this.online = "Online"
                    }
                    if (e.account[1] === 0) {
                        this.online = "Offline"
                    }
                }
            });
    },
}
</script>
