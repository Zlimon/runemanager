<template>
    <div>
        <div class="col-12 col-md-8">
            <FullCalendar :options="calendarOptions"/>

            <h2>Settings</h2>

            <div class="row">
                <div class="col-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="edit_mode">
                        <label class="form-check-label" for="edit_mode">
                            Edit mode
                        </label>
                        <p class="text-muted">Allows dragging and resizing events in the calender. Also enables editing events.</p>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="broadcast_changes">
                        <label class="form-check-label" for="broadcast_changes">
                            Broadcast changes
                        </label>
                        <p class="text-muted">This will broadcast changes in-game!</p>
                    </div>
                </div>
            </div>
        </div>

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

                <b-button class="mt-3" variant="outline-danger" block @click="deleteEvent(selectedEvent.id)">Delete event</b-button>
            </div>
        </b-modal>

        <b-modal ref="eventCreate" hide-footer title="Create new event">
            <div class="text-left">
                <div class="form-group">
                    <input v-model="fields.title" type="text" class="form-control" id="title" placeholder="Title" required>
                </div>
                <div class="form-group">
                    <input v-model="fields.description" type="text" class="form-control" id="description" placeholder="Description (optional)">
                </div>
                <div class="form-group">
                    <input v-model="fields.icon_id" type="text" class="form-control" id="icon_id" aria-describedby="icon_idHelp" placeholder="Icon ID (optional)">
                    <div class="form-text">
                        <small id="icon_idHelp" class="text-muted">Type in the ID of an icon you wish to display as an event icon</small><br>
                        <small id="icon_idHelp" class="text-muted">Search icons <a target="_blank" rel="noopener noreferrer" href="https://www.osrsbox.com/tools/item-search/">here</a> </small>
                    </div>
                </div>
                <div class="d-flex form-group">
                    <label class="col-3 col-form-label pl-0" for="event_color">Event color</label>
                    <input v-model="fields.event_color" type="color" class="form-control mx-sm-3 w-25" name="event_color" id="event_color">
                </div>
                <div class="d-flex form-group">
                    <label class="col-3 col-form-label pl-0" for="start_time">Start date</label>
                    <div class="d-flex">
                        <div class="col col-form-label">
                            <span class="text-muted">{{ this.fields.start_date | moment("DD.MM.YYYY") }}</span>
                        </div>
                        <div class="">
                            <input v-model="fields.start_time" type="time" class="form-control" id="start_time" placeholder="Start time" required>
                        </div>
                    </div>
                </div>
                <div class="d-flex form-group">
                    <label class="col-3 col-form-label pl-0" for="end_date">End date</label>
                    <input v-model="fields.end_date" type="datetime-local" class="form-control mx-sm-3 w-50" id="end_date" placeholder="End date">
                </div>
                <div class="form-check">
                    <input v-model="fields.all_day" type="checkbox" class="form-check-input" id="all_day" true-value="yes" false-value="no">
                    <label class="form-check-label" for="all_day">All-day event</label>
                </div>
                <input v-model="fields.start_date" type="hidden" id="start_date">
            </div>

            <b-button class="mt-3" variant="outline-success" block @click="createEvent()">Create event</b-button>
        </b-modal>
    </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
var moment = require('moment');

export default {
    components: {FullCalendar},

    methods: {
        handleEventClick(arg) {
            axios
                .get('/api/calendar/' + arg.event.id + '/show')
                .then((response) => {
                    this.selectedEvent = response.data;
                    this.eventTitle = response.data.title;
                    this.showModal('event');
                })
                .catch(error => {
                    console.error(error)
                })
                .finally(() => this.loading = false)
        },

        handleDateClick(arg) {
            // TODO better handling of updating fields before showModal
            this.fields.event_color = '#3490dc';
            this.fields.start_date = moment(arg.dateStr).format('YYYY-MM-DD\\THH:mm');
            this.fields.start_time = moment(new Date()).format('HH:mm');
            this.fields.end_date = moment(arg.dateStr).format('YYYY-MM-DD\\THH:mm');

            this.showModal('eventCreate')
        },

        handleEventDropOrResize(arg) {
            var moment = require('moment');

            let payload = {
                _method: 'patch',
                'start_date': moment(arg.event.startStr).format('YYYY-MM-DD HH:mm:ss'),
                'end_date': (arg.event.endStr ? moment(arg.event.endStr).format('YYYY-MM-DD HH:mm:ss') : null),
            }

            this.scheduleEvent(arg.event.id, payload);
        },

        getEvents() {
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

        createEvent() {
            const start = moment(this.fields.start_date, 'YYYY-MM-DD\\THH:mm');

            const hour = this.fields.start_time.match(/(.*):/g).pop().replace(":", "");
            const minute = this.fields.start_time.match(/:(.*)/g).pop().replace(":", "");

            start.set({h: hour, m: minute});

            let payload = {
                'title': this.fields.title,
                'description': this.fields.description,
                'start_date': start.format('YYYY-MM-DD\\THH:mm'),
                'end_date': this.fields.all_day === 'yes' ? null : this.fields.end_date,
                'icon_id': this.fields.icon_id,
                'event_color': this.fields.event_color,
            }

            axios
                .post('/api/admin/calendar/create', payload)
                .then(() => {
                    this.getEvents();
                    this.hideModal('eventCreate');
                    this.toastSuccess();

                    this.fields = {};
                })
                .catch(error => {
                    console.error(error.response.data);
                    this.toastError();
                });
        },

        scheduleEvent(eventId, payload) {
            axios
                .post('/api/admin/calendar/' + eventId + '/schedule', payload)
                .then(() => {
                    this.getEvents();
                    this.toastSuccess();
                })
                .catch(error => {
                    console.error(error.response.data);
                    this.toastError();
                });
        },

        deleteEvent(eventId) {
            axios
                .post('/api/admin/calendar/' + eventId + '/destroy', {
                    _method: 'delete',
                })
                .then(() => {
                    this.getEvents();
                    this.hideModal('event');

                    this.selectedEvent = null;
                    this.eventTitle = '';

                    this.toastSuccess();
                })
                .catch(error => {
                    console.error(error.response.data);
                    this.toastError();
                });
        },

        showModal(modal) {
            this.$refs[modal].show()
        },

        hideModal(modal) {
            this.$refs[modal].hide()
        },

        toastSuccess() {
            this.$swal.fire({
                toast: true,
                icon: 'success',
                title: 'Success',
                position: 'top-right',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            })
        },

        toastError() {
            this.$swal.fire({
                toast: true,
                icon: 'error',
                title: 'Error',
                position: 'top-right',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            })
        },
    },

    data() {
        return {
            calendarOptions: {
                plugins: [dayGridPlugin, interactionPlugin],
                initialView: 'dayGridMonth',
                displayEventEnd: true,
                editable: true,
                dateClick: this.handleDateClick,
                eventClick: this.handleEventClick,
                eventDrop: this.handleEventDropOrResize,
                eventResize: this.handleEventDropOrResize,
                events: [],
            },

            fields: {
                // event_color: '#3490dc',
                // start_date: moment(new Date()).format('HH:mm'),
                // end_date: moment(new Date()).format('YYYY-MM-DD\\THH:mm'),
            },

            selectedEvent: null,
            eventTitle: '',
        }
    },

    mounted() {
        this.getEvents();
    },
}
</script>

<style>
a.fc-event {
    -webkit-transition: 0s;
    transition: 0s;
}

.colored-toast.swal2-icon-success {
    background-color: #a5dc86 !important;
}

.colored-toast.swal2-icon-error {
    background-color: #f27474 !important;
}

.colored-toast.swal2-icon-warning {
    background-color: #f8bb86 !important;
}

.colored-toast.swal2-icon-info {
    background-color: #3fc3ee !important;
}

.colored-toast.swal2-icon-question {
    background-color: #87adbd !important;
}

.colored-toast .swal2-title {
    color: white;
}

.colored-toast .swal2-close {
    color: white;
}

.colored-toast .swal2-content {
    color: white;
}
</style>
