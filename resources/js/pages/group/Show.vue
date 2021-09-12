<template>
    <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
        <h1 class="text-center">{{ group.name }}</h1>

        <groupaccount :group="group" @clicked="loadAccount"></groupaccount>

        <div class="row">
            <div class="col-md-5">
                <div v-if="account !== null">
                    <h1 class="text-center">{{ account.username }}</h1>

                    <div class="mt-4">
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
                            <component v-bind:is="component" :account="account"/>
                        </keep-alive>
                    </div>

                    <h3 class="text-center header-chatbox-sword">Equipment</h3>

                    <equipment :account="account"></equipment>

                    <h3 class="text-center header-chatbox-sword">Quests</h3>

                    <quests :account="account"></quests>
                </div>
            </div>

            <div class="col-md-7">
                <h3 class="text-center header-chatbox-sword">The Bank of Gielinor</h3>

                <groupbank :group="group"></groupbank>
            </div>
        </div>
    </div>
</template>

<script>
import AccountSkillHiscore from '../../components/account/SkillHiscore'
import AccountBossHiscore from '../../components/account/BossHiscore.vue'

export default {
    name: "PageGroupShow",

    props: {
        group: {required: true},
    },

    methods: {
        loadAccount(account) {
            this.account = account;
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
            account: null,
            skills: true,
            component: AccountSkillHiscore,
        }
    },

    mounted() {
        this.account = this.group.account[0];
    }
}
</script>

<style scoped>

</style>
