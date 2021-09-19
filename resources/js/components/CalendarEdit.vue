<template>
    <div class="row">
        <div class="col-12 col-md-6 mb-2">
            <div class="bg-admin-dark p-4">
                <div class="p-4 bg-admin-info">
                    <FullCalendar :options="calendarOptions"/>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="bg-admin-dark p-4">
                <h2>Settings</h2>

                <div class="p-4 mb-4 bg-admin-info">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input type="checkbox" id="edit_mode" name="edit_mode" class="form-check-input">
                                <label for="edit_mode" class="form-check-label">
                                    Edit mode
                                </label>
                                <p class="text-muted">
                                    Allows dragging and resizing events in the calender. Also enables editing events.
                                </p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input type="checkbox" id="broadcast_changes" name="broadcast_changes" class="form-check-input">
                                <label for="broadcast_changes" class="form-check-label">
                                    Broadcast changes
                                </label>
                                <p class="text-muted">
                                    This will broadcast changes in-game!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" ref="eventCreateModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-admin-dark">
                    <div class="modal-header">
                        <h5 class="modal-title">Create new event</h5>
                        <button @click="eventCreateModal.hide()"
                                type="button"
                                class="btn-close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col">
                                <input v-model="fields.title"
                                       type="text"
                                       id="title"
                                       name="title"
                                       class="form-control"
                                       placeholder="Title"
                                       required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <input v-model="fields.description"
                                       type="text"
                                       id="description"
                                       name="description"
                                       class="form-control"
                                       placeholder="Description (optional)">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <input v-model="fields.icon_id"
                                       type="number"
                                       id="icon_id"
                                       name="icon_id"
                                       class="form-control"
                                       placeholder="Icon ID (optional)"
                                       required>
                                <div class="form-text">
                                    <small>
                                        Type in the ID of an in-game item you wish to display as the icon for this event.
                                        <br>
                                        Search icons
                                        <a href="https://www.osrsbox.com/tools/item-search/"
                                           class="link-primary"
                                           target="_blank"
                                           rel="noopener noreferrer">
                                            here
                                        </a>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="event_color" class="col-sm-3 col-form-label">Event color</label>
                            <div class="col-sm-9">
                                <input v-model="fields.event_color"
                                       type="color"
                                       id="event_color"
                                       name="event_color"
                                       class="form-control form-control-color"
                                       title="Choose your color">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="event_color" class="col-sm-3 col-form-label">Start date</label>
                            <div class="col-sm-9">
                                <input v-model="fields.start_time"
                                       type="time"
                                       id="start_time"
                                       name="start_time"
                                       class="form-control"
                                       required>
                                <div class="form-text">
                                    <small>
                                        {{ this.fields.start_date | moment("DD.MM.YYYY") }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div v-if="fields && fields.all_day === 'no'" class="row mb-3">
                            <label for="event_color" class="col-sm-3 col-form-label">End date</label>
                            <div class="col-sm-9">
                                <input v-model="fields.end_date"
                                       type="datetime-local"
                                       id="end_date"
                                       name="end_date"
                                       class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-9">
                                <div class="form-check">
                                    <input v-model="fields.all_day"
                                           type="checkbox"
                                           id="all_day"
                                           name="all_day"
                                           class="form-check-input"
                                           true-value="yes"
                                           false-value="no">
                                    <label for="all_day" class="form-check-label">
                                        All-day event?
                                    </label>
                                </div>
                            </div>
                        </div>

                        <input v-model="fields.start_date" type="hidden" id="start_date">
                    </div>
                    <div class="modal-footer">
                        <div @click="createEvent()"
                             class="btn btn-success">
                            Create event
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" ref="eventShowModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-admin-dark">
                    <div class="modal-header">
                        <h5 v-if="selectedEvent && selectedEvent.title !== null" class="modal-title">{{ selectedEvent.title }}</h5>
                        <button type="button" class="btn-close" @click="eventShowModal.hide()" aria-label="Close"></button>
                    </div>
                    <div v-if="selectedEvent" class="modal-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <img v-if="selectedEvent.icon_id !== null" :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + selectedEvent.icon_id + '.png'" class="pixel icon" alt="Event icon">
                            </div>

                            <div class="col">
                                <p class="fw-bold">{{ selectedEvent.description ? selectedEvent.description : '' }}</p>

                                <hr>

                                <div class="d-flex text-left">
                                    <div class="col-3 pl-0">
                                        <span class="fw-bold">Starts:</span>
                                        <br>
                                        <span class="fw-bold">{{ selectedEvent.end_date !== null ? 'Ends:' : '' }}</span>
                                    </div>
                                    <div class="col">
                                        <span>{{ selectedEvent.start_date | moment('ddd. Do MMMM HH:mm') }}</span>
                                        <br>
                                        <span v-if="selectedEvent.end_date !== null">{{ selectedEvent.end_date | moment('ddd. Do MMMM HH:mm') }}</span>
                                        <span v-else class="fst-italic">(all day event)</span>
                                    </div>
                                </div>

                                <hr>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div @click="deleteEvent(selectedEvent.id)"
                             class="btn btn-danger">
                            Delete event
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {Modal} from 'bootstrap'
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

var moment = require('moment');

export default {
    components: {FullCalendar},

    methods: {
        // Show event
        handleEventClick(arg) {
            axios
                .get('/api/calendar/' + arg.event.id + '/show')
                .then((response) => {
                    this.selectedEvent = response.data;

                    if (this.selectedEvent) {
                        this.eventShowModal.show();
                    }
                })
                .catch(error => {
                    console.error(error)
                })
                .finally(() => this.loading = false)
        },

        // Create event
        handleDateClick(arg) {
            // TODO better handling of updating fields before showModal
            this.fields.event_color = '#3490dc';
            this.fields.start_date = moment(arg.dateStr).format('YYYY-MM-DD\\THH:mm');
            this.fields.start_time = moment(new Date()).format('HH:mm');
            this.fields.end_date = moment(arg.dateStr).format('YYYY-MM-DD\\THH:mm');

            this.eventCreateModal.show();
        },

        // Reschedule event
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
                    this.eventCreateModal.hide()
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
                    this.eventShowModal.hide()
                    this.selectedEvent = null;
                    this.toastSuccess();
                })
                .catch(error => {
                    console.error(error.response.data);
                    this.toastError();
                });
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
            eventCreateModal: null,
            eventShowModal: null,

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
                event_color: '#3490dc',
                start_date: moment(new Date()).format('HH:mm'),
                end_date: moment(new Date()).format('YYYY-MM-DD\\THH:mm'),
                all_day: 'no',
            },

            selectedEvent: null,
        }
    },

    mounted() {
        this.getEvents();

        this.eventCreateModal = new Modal(this.$refs.eventCreateModal)
        this.eventShowModal = new Modal(this.$refs.eventShowModal)
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
