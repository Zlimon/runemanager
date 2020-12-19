<template>
	<div>
		<div v-if="skills">
			<div class="btn btn-lg float-right background-world-map" style="width: 7rem; height: 6rem;" v-on:click="toggle">
				<img class="pixel" src="/images/boss/boss.png" width="54" alt="Overall skill icon">
				<br>
				<span>Bosses</span>
			</div>
		</div>
		<div v-else>
			<div class="btn btn-lg float-right background-world-map" style="width: 7rem; height: 6rem;" v-on:click="toggle">
				<img class="pixel" src="/images/skill/overall.png" width="54" alt="Overall skill icon">
				<br>
				<span>Skills</span>
			</div>
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