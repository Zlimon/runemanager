<template>
	<div>
		<div class="float-left mt-3 ml-3">
			<h1 class="text-left">{{ meta.boss }}</h1>

			<span>Total Kills: <strong>{{ meta.total_kills }}</strong></span>
			<br>
			<span>Average Kills: <strong>{{ meta.average_total_kills }}</strong></span>
		</div>
		<table>
			<tr>
				<th>Rank</th>
				<th>Account</th>
				<th>Kill count</th>
				<th>Hiscore Rank</th>
			</tr>
			<tr v-for="(hiscore, index) in hiscores">
				<td>{{ index + 1 }}</td>
				<td><a :href="'/account/' + hiscore.account.username">{{ hiscore.account.username }}</a></td>
				<td>{{ hiscore.kill_count }}</td>
				<td>{{ hiscore.rank }}</td>
			</tr>
		</table>
	</div>
</template>

<script>
	export default {
		props: {
			boss: { type: String, required: true }
		},

		data () {
			return {
				hiscores: {},
				meta: {}
			}
		},

		mounted() {
			axios
			.get('/api/hiscore/boss/' + this.boss)
			.then((response) => {
				this.hiscores = response.data.data;
				this.meta = response.data.meta;
			})
			.catch(error => (console.log(error)))
		},
	}
</script>