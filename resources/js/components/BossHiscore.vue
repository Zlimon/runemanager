<template>
	<div>
		<table>
			<tr>
				<th>Rank</th>
				<th>Account</th>
				<th>Kill count</th>
				<th>Hiscore Rank</th>
			</tr>
			<tr v-for="(hiscore, index) in hiscores">
				<td>{{ index + 1 }}</td>
				<td><a :href="'/account/' + hiscore.account.id">{{ hiscore.account.username }}</a></td>
				<td>{{ hiscore.kill_count }}</td>
				<td>rank</td>
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
			}
		},

		mounted() {
			axios
			.get('/api/hiscore/boss/' + this.boss)
			.then((response) => {
				this.hiscores = response.data.data;
			})
			.catch(error => (console.log(error)))
		},
	}
</script>