<template>
	<div>
		<div class="float-left ml-3">
			<h1 class="text-left">{{ data.username }}</h1>

			<span>Rank: <strong>{{ data.rank }}</strong></span>
			<br>
			<span>Total XP: <strong>{{ data.xp }}</strong></span>
			<br>
			<span>Total Level: <strong>{{ data.level }}</strong></span>
			<br>
			<span>Joined: <strong>{{ data.joined }}</strong></span>
		</div>

		<table>
			<tr>
				<th></th>
				<th>Level</th>
				<th>XP</th>
				<th>Hiscore Rank</th>
			</tr>
			<tr v-for="(hiscore, key) in hiscores">
				<td>
					<a :href="'/hiscore/' + key">
						<img class="align" :src="'/images/skill/' + key + '.png'" width="35px" :alt="key + ' skill icon'"/>
						{{ key | capitalize }}
					</a>
				</td>
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
			account: { type: String, required: true },
		},

		data () {
			return {
				data: {},
				hiscores: {}
			}
		},

		mounted() {
			axios
			.get('/api/account/' + this.account + '/skill')
			.then((response) => {
				this.data = response.data.data;
				this.hiscores = response.data.meta.skillHiscores;
			})
			.catch(error => (console.log(error)))
		},

		filters: {
			capitalize: function (value) {
				if (!value) return ''
				value = value.toString()
				return value.charAt(0).toUpperCase() + value.slice(1)
			}
		}
	}
</script>