<template>
    <div>
        <div v-if="skills">
            <div class="btn button-square float-right background-world-map" v-on:click="toggle">
                <img alt="Bosses icon"
                     class="pixel icon"
                     src="/images/boss/boss.png"
                     title="Click here to see the boss hiscores">
                <br>
                <span>Bosses</span>
            </div>
        </div>
        <div v-else>
            <div class="btn button-square float-right background-world-map" v-on:click="toggle">
                <img alt="Skills icon"
                     class="pixel icon"
                     src="/images/skill/overall.png"
                     title="Click here to see the skills hiscores">
                <br>
                <span>Skills</span>
            </div>
        </div>

        <keep-alive>
            <component v-bind:is="component" :account='account'/>
            </component>
        </keep-alive>
    </div>
</template>

<script>
import AccountSkillHiscore from './AccountSkillHiscore.vue'
import AccountBossHiscore from './AccountBossHiscore.vue'

export default {
    props: {
        account: {type: String, required: true},
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
