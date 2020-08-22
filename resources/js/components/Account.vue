<template>
	<div>
		<div class="row">
			<div class="col-md-3 justify-content-center align-self-center">
				<h1 class="text-center">{{ account.username }}</h1>
			</div>

			<div class="col-md-6">
				<span>Rank: <strong>{{ account.rank }}</strong></span>
				<br>
				<span>Total XP: <strong>{{ account.xp }}</strong></span>
				<br>
				<span>Total Level: <strong>{{ account.level }}</strong></span>
				<br>
				<span>Joined: <strong>{{ account.joined }}</strong></span>
			</div>
		</div>

		<table class="table table-hover">
			<thead>
				<tr>
					<th scope="col"></th>
					<th scope="col">Level</th>
					<th scope="col">XP</th>
					<th scope="col">Rank</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(hiscore, key) in hiscores">
					<th scope="row">
						<a :href="'/hiscore/' + key">
							<img class="align" :src="'/images/skills/' + key + '.png'" width="35px" :alt="key + ' skill icon'"/>
							<!-- {{ key | capitalize }} -->
						</a>
					</th>
					<td>{{ hiscore.level }}</td>
					<td>{{ hiscore.xp }}</td>
					<td>{{ hiscore.rank }}</td>
				</tr>
			</tbody>
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