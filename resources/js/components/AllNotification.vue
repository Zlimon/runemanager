<template>
	<div>
		<notifications :notifications="notificationsData"></notifications>
	</div>
</template>

<script>
	import notifications from './Notification.vue'

	export default {
		components: {
			'notifications': notifications,
		},

		data() {
			return {
				notificationsData: []
			}
		},

		mounted() {
			axios
			.get('/api/notification/all')
			.then((response) => {
				this.notificationsData = response.data;
			})
			.catch(error => (console.log(error)))
		},

		created() {
			window.Echo.channel('all')
				.listen('All', (e) => {
					this.notificationsData.unshift(e.notification);
				});
		},
	}
</script>