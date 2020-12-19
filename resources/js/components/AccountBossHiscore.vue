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
				<th>Kill Count</th>
				<th>Hiscore Rank</th>
				<th>Collection Log</th>
				<th>Obtained</th>
			</tr>
			<tr v-for="(hiscore, index) in hiscores">
				<td>
					<a :href="'/hiscore/boss/' + index">
						<img class="align" :src="'/images/boss/' + index + '.png'" width="35px" :alt="index + ' boss icon'"/>
						{{ index | capitalize }}
					</a>
				</td>
				<td>{{ hiscore.kill_count }}</td>
				<td>{{ hiscore.rank }}</td>
				<td>
					<button type="button" class="btn btn-dark" data-toggle="modal" :data-target="$idRef(index.replace(/ /g, '_'))">
						<img src="https://www.osrsbox.com/osrsbox-db/items-icons/22711.png">
					</button>
				</td>
				<td>
					<div v-if="hiscore.obtained === hiscore.total">
						<span class="runescape-success">{{ hiscore.obtained }} / {{ hiscore.total }}</span>
					</div>
					<div v-else-if="hiscore.obtained > 0">
						<span class="runescape-progress">{{ hiscore.obtained }} / {{ hiscore.total }}</span>
					</div>
					<div v-else>
						<span class="runescape-danger">{{ hiscore.obtained }} / {{ hiscore.total }}</span>
					</div>
				</td>
				<div :id="$id(index.replace(/ /g, '_'))" class="modal fade" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content bg-dark">
							<div class="modal-body background-dialog-iron-rivets text-light">
								<button class="btn btn-lg button-window-close float-right" data-dismiss="modal"></button>
								<h1>{{ data.username }}</h1>
								<div class="d-flex flex-row flex-wrap justify-content-center">
									<div v-for="(value, key) in hiscore.log" class="collection-log-item rounded border-secondary bg-dark p-4">
										<div v-if="value === 1">
											<img :src="'/images/boss/' + index.replace(/ /g, '_') + '/' + key + '.png'">
										</div>
										<div v-else-if="value > 0">
											<img :src="'/images/boss/' + index.replace(/ /g, '_') + '/' + key + '.png'">
											<span class="collection-log-item-counter runescape-progress rounded">{{ value }}</span>
										</div>
										<div v-else>
											<img :src="'/images/boss/' + index.replace(/ /g, '_') + '/' + key + '.png'" class="faded">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
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
			.get('/api/account/' + this.account + '/boss')
			.then((response) => {
				this.data = response.data.data;
				this.hiscores = response.data.meta.bossHiscores;
			})
			.catch(error => (console.log(error)))
		},

		filters: {
			capitalize: function (value) {
				if (!value) return ''
				value = value.toString()
				return value.charAt(0).toUpperCase() + value.slice(1)
			},
		}
	}
</script>