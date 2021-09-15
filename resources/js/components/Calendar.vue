<template>
    <div>
        <FullCalendar :options="calendarOptions"/>

        <b-modal ref="event" hide-footer :title="eventTitle">
            <div v-if="selectedEvent">
                <div class="d-flex">
                    <div class="col-3 text-center">
                        <img v-if="selectedEvent.icon_id !== null" :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + selectedEvent.icon_id + '.png'" class="pixel icon mb-2" alt="Event icon">
                    </div>

                    <div class="col">
                        <p class="font-weight-bold text-left">{{ selectedEvent.description ? selectedEvent.description : '' }}</p>

                        <hr>

                        <div class="d-flex text-left">
                            <div class="col-3 pl-0">
                                <span class="font-weight-bold">Starts:</span>
                                <br>
                                <span class="font-weight-bold">{{ selectedEvent.end_date !== null ? 'Ends:' : '' }}</span>
                            </div>
                            <div class="col">
                                <span>{{ selectedEvent.start_date | moment('ddd. Do MMMM HH:mm') }}</span>
                                <br>
                                <span v-if="selectedEvent.end_date !== null">{{ selectedEvent.end_date | moment('ddd. Do MMMM HH:mm') }}</span>
                            </div>
                        </div>

                        <hr>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

export default {
    components: {FullCalendar},

    methods: {
        handleEventClick: function (arg) {
            axios
                .get('/api/calendar/' + arg.event.id + '/show')
                .then((response) => {
                    this.selectedEvent = response.data;
                    this.eventTitle = response.data.title;
                    this.showModal();
                })
                .catch(error => {
                    console.error(error)
                })
                .finally(() => this.loading = false)
        },

        showModal() {
            this.$refs['event'].show()
        },
    },

    data() {
        return {
            calendarOptions: {
                plugins: [dayGridPlugin, interactionPlugin],
                initialView: 'dayGridMonth',
                displayEventEnd: true,
                editable: false,
                eventClick: this.handleEventClick,
                events: [],
            },

            selectedEvent: null,
            eventTitle: '',
        }
    },

    mounted() {
        axios
            .get('/api/calendar')
            .then((response) => {
                this.calendarOptions.events = response.data;
            })
            .catch(error => {
                console.error(error)
            })
            .finally(() => this.loading = false)
    },
}
</script>

<style>

</style>
