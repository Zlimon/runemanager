<template>
	<div>
		<!-- <div class="account-box">
			<p>Skills</p>
			<img class="pixel" src="/images/skill/overall.png" width="54" alt="Overall skill icon">
		</div> -->

		<div v-if="skills">
			<button type="button" class="btn float-right account-box" v-on:click="toggle"><img class="pixel" src="/images/boss/boss.png" width="54" alt="Overall skill icon"></button>
		</div>
		<div v-else>
			<button type="button" class="btn float-right account-box" v-on:click="toggle"><img class="pixel" src="/images/skill/overall.png" width="54" alt="Overall skill icon"></button>
		</div>

		<keep-alive>
			<component :account='account' v-bind:is="component" /></component>
		</keep-alive>
	</div>
</template>

<script>
	import AccountSkillHiscore from './AccountSkillHiscore.vue'
	import AccountBossHiscore from './AccountBossHiscore.vue'

	export default {
		props: {
			account: { type: String, required: true },
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

		data () {
			return {
				skills: true,
				component: AccountSkillHiscore,
			}
		},
	}
</script>