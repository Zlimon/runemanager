<template>
	<div>
		<notifications :notifications="notificationsData"></notifications>
	</div>
</template>

<script>
	import notifications from './Notification.vue'

	export default {
		props: {
			account: { required: true },
		},

		methods: {
			checkAccount (accountUsername) {
				return this.account.id === accountUsername;
			},
		},

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
			.get('/api/notification/account/' + this.account.username)
			.then((response) => {
				this.notificationsData = response.data.data;
			})
			.catch(error => (console.log(error)))
		},

		created() {
			window.Echo.channel('account-all')
				.listen('AccountAll', (e) => {
					if (this.checkAccount(e.notification.account_id)) {
						this.notificationsData.unshift(e.notification);
					}
				});
		},
	}
</script>