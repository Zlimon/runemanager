<template>
	<div>
		<div class="float-left mt-3 ml-3">
			<h1 class="text-left">{{ meta.boss | capitalize }}</h1>

			<span>Total Kills: <strong>{{ meta.total_kills }}</strong></span>
			<br>
			<span>Average Kills: <strong>{{ meta.average_total_kills }}</strong></span>
		</div>

		<table>
			<tr>
				<th>Rank</th>
				<th>Account</th>
				<th>Kill Count</th>
				<th>Hiscore Rank</th>
				<th>Collection Log</th>
				<th>Obtained</th>
			</tr>
			<tr v-for="(hiscore, index) in hiscores">
				<td>{{ index + 1 }}</td>
				<td><a :href="'/account/' + hiscore.account.username">{{ hiscore.account.username }}</a></td>
				<td>{{ hiscore.kill_count }}</td>
				<td>{{ hiscore.rank }}</td>
				<td>
					<button type="button" class="btn btn-dark background-world-map" data-toggle="modal" :data-target="$idRef(index)">
						<img src="https://www.osrsbox.com/osrsbox-db/items-icons/22711.png">
					</button>
				</td>
				<td>
					<div v-if="hiscore.obtained === total">
						<span class="runescape-success">{{ hiscore.obtained }} / {{ total }}</span>
					</div>
					<div v-else-if="hiscore.obtained > 0">
						<span class="runescape-progress">{{ hiscore.obtained }} / {{ total }}</span>
					</div>
					<div v-else>
						<span class="runescape-danger">{{ hiscore.obtained }} / {{ total }}</span>
					</div>
				</td>
				<div :id="$id(index)" class="modal fade" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content bg-dark">
							<div class="modal-body background-dialog-iron-rivets text-light">
								<button class="btn btn-lg button-window-close float-right" data-dismiss="modal"></button>
								<h1>{{ hiscore.account.username }}</h1>
								<div class="d-flex flex-row flex-wrap justify-content-center">
									<div v-for="(value, key) in hiscore.log" class="collection-log-item rounded border-secondary bg-dark p-4">
										<div v-if="value === 1">
											<img :src="'/images/boss/' + meta.boss + '/' + key + '.png'">
										</div>
										<div v-else-if="value > 0">
											<img :src="'/images/boss/' + meta.boss + '/' + key + '.png'">
											<span class="collection-log-item-counter runescape-progress rounded">{{ value }}</span>
										</div>
										<div v-else>
											<img :src="'/images/boss/' + meta.boss + '/' + key + '.png'" class="faded">
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
			boss: { type: String, required: true }
		},

		data () {
			return {
				hiscores: {},
				meta: {},
				total: 0
			}
		},

		mounted() {
			axios
			.get('/api/hiscore/boss/' + this.boss)
			.then((response) => {
				this.hiscores = response.data.data;
				this.meta = response.data.meta;
				this.total = Object.keys(response.data.data[0].log).length
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