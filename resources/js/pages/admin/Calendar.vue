<template>
    <div class="row">
        <div class="col-12 col-md-6 mb-2">
            <div class="p-2 p-md-4 bg-admin-dark">
                <FullCalendar :options="calendarOptions"/>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="bg-admin-dark p-4">
                <h2>Settings</h2>

                <div class="p-4 bg-admin-info">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input type="checkbox" id="edit_mode" name="edit_mode" class="form-check-input">
                                <label for="edit_mode" class="form-check-label">
                                    Edit mode
                                </label>
                                <br>
                                <small class="form-text text-dark">
                                    Allows dragging and resizing events in the calender. Also enables editing events.
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input type="checkbox" id="broadcast_changes" name="broadcast_changes" class="form-check-input">
                                <label for="broadcast_changes" class="form-check-label">
                                    Broadcast changes
                                </label>
                                <br>
                                <small class="form-text text-dark">
                                    This will broadcast changes in-game!
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showCreateModal">
            <admin-calendar-create :fields="fields" @close="closeCreateModal"></admin-calendar-create>
        </div>

        <div v-if="showShowModal">
            <admin-calendar-show :event="event" @close="closeShowModal"></admin-calendar-show>
        </div>
    </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

import AdminCalendarCreate from "../../components/admin/calendar/Create";
import AdminCalendarShow from "../../components/admin/calendar/Show";

var moment = require('moment');

export default {
    name: "PageAdminCalendar",

    components: {AdminCalendarCreate, AdminCalendarShow, FullCalendar},

    methods: {
        // Show event
        handleEventClick(arg) {
            axios
                .get('/api/calendar/' + arg.event.id + '/show')
                .then((response) => {
                    this.errors = null;

                    this.event = response.data;

                    this.showShowModal = true;
                })
                .catch(error => {
                    console.error(error)
                })
                .finally(() => this.loading = false)
        },

        // Create event
        handleDateClick(arg) {
            // Reset fields
            this.fields.event_color = '#3490dc';
            this.fields.start_date = moment(arg.dateStr).format('YYYY-MM-DD\\THH:mm');
            this.fields.start_time = moment(new Date()).format('HH:mm');
            this.fields.end_date = moment(arg.dateStr).format('YYYY-MM-DD\\THH:mm');

            this.showCreateModal = true;
        },

        // Reschedule event
        handleEventDropOrResize(arg) {
            var moment = require('moment');

            let payload = {
                _method: 'patch',
                'start_date': moment(arg.event.startStr).format('YYYY-MM-DD HH:mm:ss'),
                'end_date': (arg.event.endStr ? moment(arg.event.endStr).format('YYYY-MM-DD HH:mm:ss') : null),
            }

            this.scheduleEvent(arg.event, payload);
        },

        getEvents() {
            axios
                .get('/api/calendar')
                .then((response) => {
                    this.errors = null;

                    this.calendarOptions.events = response.data;
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.errors = error.response.data.errors;
                    this.doError(error.response.data.message, error.response.data.errors);
                })
                .finally(() => this.loading = false)
        },

        scheduleEvent(event, payload) {
            axios
                .post('/api/admin/calendar/' + event.id + '/schedule', payload)
                .then(() => {
                    this.errors = null;

                    this.getEvents();
                    this.doSuccess('Successfully rescheduled event "' + event.title + '".');
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.getEvents();

                    this.errors = error.response.data.errors;
                    this.doError(error.response.data.message, error.response.data.errors);
                });
        },

        closeCreateModal(updateEvents) {
            if (updateEvents === true) this.getEvents();
            this.showCreateModal = false;
        },

        closeShowModal(updateEvents) {
            if (updateEvents === true) this.getEvents();
            this.showShowModal = false;
        },
    },

    data() {
        return {
            showCreateModal: false,
            showShowModal: false,

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

            fields: {},

            event: null,

            errors: null,
        }
    },

    mounted() {
        this.getEvents();
    },
}
</script>

<!--Don't change to scoped. This prevents sluggish movements in calendar-->
<style>
    a.fc-event {
        -webkit-transition: 0s;
        transition: 0s;
    }
</style>
