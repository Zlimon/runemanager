<template>
    <div class="row">
        <div v-if="skills" class="d-flex justify-content-between">
            <div class="col">
                <h3 class="text-center header-chatbox-sword">Skills</h3>
            </div>

            <div v-on:click="toggle" class="btn bg-dark text-light background-world-map">
                <img :src="'/images/boss/boss.png'"
                     class="pixel icon-small"
                     alt="Bosses icon"
                     title="Click here to see the boss hiscores">
            </div>
        </div>
        <div v-else class="d-flex justify-content-between">
            <div class="col">
                <h3 class="text-center header-chatbox-sword">Bosses</h3>
            </div>

            <div v-on:click="toggle" class="btn bg-dark text-light background-world-map">
                <img :src="'/images/skill/total.png'"
                     class="pixel icon-small"
                     alt="Skills icon"
                     title="Click here to see the skill hiscores">
            </div>
        </div>

        <keep-alive>
            <component v-bind:is="component" :account="account"/>
        </keep-alive>
    </div>
</template>

<script>
import AccountSkillHiscore from './account/SkillHiscore.vue'
import AccountBossHiscore from './account/BossHiscore.vue'

export default {
    props: {
        account: {required: true},
    },

    methods: {
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
            skills: true,
            component: AccountSkillHiscore,
        }
    },
}
</script>
