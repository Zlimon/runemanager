<template>
	<div>
		<div class="float-left mt-3 ml-3">
			<h1 class="text-left">{{ meta.skill }}</h1>

			<span>Total XP: <strong>{{ meta.total_xp }}</strong></span>
			<br>
			<span>Average Level: <strong>{{ meta.average_total_level }}</strong></span>
			<br>
			<span>Maxed: <strong>{{ meta.total_max_level }}</strong></span>
		</div>

		<table>
			<tr>
				<th>Rank</th>
				<th>Account</th>
				<th>Level</th>
				<th>XP</th>
				<th>Hiscore Rank</th>
			</tr>
			<tr v-for="(hiscore, index) in hiscores">
				<td>{{ index + 1 }}</td>
				<td><a :href="'/account/' + hiscore.username">{{ hiscore.username }}</a></td>
				<td>{{ hiscore.level }}</td>
				<td>{{ hiscore.xp }}</td>
				<td>{{ hiscore.rank }}</td>
			</tr>
		</table>
	</div>
</template>

<script>
	export default {
		props: {
			skill: { type: String, required: true }
		},

		data () {
			return {
				hiscores: {},
				meta: {}
			}
		},

		mounted() {
			axios
			.get('/api/hiscore/skill/' + this.skill)
			.then((response) => {
				this.hiscores = response.data.data;
				this.meta = response.data.meta;
			})
			.catch(error => (console.log(error)))
		},
	}
</script>