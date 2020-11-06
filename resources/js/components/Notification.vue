<template>
	<div>
		<div v-if="notifications.length > 0">
			<div v-for="(notification, index) in notifications">
				<div class="card text-white bg-secondary mb-3">
					<div class="card-body">
						<div class="row align-items-center">
							<div class="notification-icon border-dark p-2">
								<img class="align" :src="'/images/' + notification.category + '/' + notification.name + '.png'" width="50px" :alt="notification.boss + ' ' + notification.category + ' icon'"/>
							</div>

							<div class="col-md-9">
								<h2>{{ notification.message }}</h2>
							</div>
						</div>

						<div v-if="Object.keys(notification.loot).length > 0">
							<h3 class="card-text text-center">Received loot:</h3>
							<div class="item-container">
								<div v-for="(loot, index) in notification.loot" class="item rounded border-secondary bg-dark p-4">
									<img :src="'/images/' + notification.category + '/' + notification.name.replace(/ /g,'_') + '/' + index + '.png'">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div v-else>
			<div class="text-center">
				<h3 class="text-center">Nothing interesting happens.</h3>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				notifications: []
			}
		},

		created() {
			window.Echo.channel('account')
				.listen('AccountKill', (e) => {
					this.notifications.unshift(e.notification);
				})
				.listen('AccountNewUnique', (e) => {
					this.notifications.unshift(e.notification);
				})
				.listen('AccountLevelUp', (e) => {
					this.notifications.unshift(e.notification);
				});
		},
	}
</script>