<template>
	<div>
		<div class="float-left ml-3">
			<h1 class="text-left">{{ account.username }}</h1>

			<span>Rank: <strong>{{ account.rank }}</strong></span>
			<br>
			<span>Total XP: <strong>{{ account.xp }}</strong></span>
			<br>
			<span>Total Level: <strong>{{ account.level }}</strong></span>
			<br>
			<span>Joined: <strong>{{ account.joined }}</strong></span>
		</div>

		<table>
			<tr>
				<th></th>
				<th>Level</th>
				<th>XP</th>
				<th>Rank</th>
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
			accountId: { type: Number, required: true },
		},

		data () {
			return {
				account: {},
				hiscores: {}
			}
		},

		mounted() {
			axios
			.get('/api/account/' + this.accountId)
			.then((response) => {
				this.account = response.data.data;
				this.hiscores = response.data.meta.hiscores;
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