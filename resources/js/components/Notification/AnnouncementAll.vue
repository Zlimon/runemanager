<template>
    <div>
        <broadcasts :broadcasts="broadcastsData"></broadcasts>
    </div>
</template>

<script>
import broadcasts from './Broadcast.vue'

export default {
    components: {
        'broadcasts': broadcasts,
    },

    data() {
        return {
            broadcastsData: []
        }
    },

    mounted() {
        axios
            .get('/api/broadcast/announcement')
            .then((response) => {
                this.broadcastsData = response.data;
            })
            .catch(error => (console.log(error)))
    },

    created() {
        window.Echo.channel('all')
            .listen('AnnouncementAll', (e) => {
                this.broadcastsData.unshift(e.broadcast);
            });
    },
}
</script>
