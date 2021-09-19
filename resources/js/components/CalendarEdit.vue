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

                <div class="p-4 mb-4 bg-admin-info">
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
                            <div v-if="this.errors && this.errors.title !== undefined">
                                <small v-for="error in this.errors.title" class="text-danger">{{ error }}<br></small>
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
                            <div v-if="this.errors && this.errors.description !== undefined">
                                <small v-for="error in this.errors.description" class="text-danger">{{ error }}<br></small>
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
                            </div>
                            <div v-if="this.errors && this.errors.icon_id !== undefined">
                                <small v-for="error in this.errors.icon_id" class="text-danger">{{ error }}<br></small>
                            </div>
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
                            <div v-if="this.errors && this.errors.event_color !== undefined">
                                <small v-for="error in this.errors.event_color" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="event_color" class="col-sm-3 col-form-label">Start date</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        {{ this.fields.start_date | moment("ddd. Do MMM") }}
                                    </span>
                                    <input v-model="fields.start_time"
                                           type="time"
                                           id="start_time"
                                           name="start_time"
                                           class="form-control"
                                           required>
                                </div>
                            </div>
                            <div v-if="this.errors && this.errors.start_date !== undefined">
                                <small v-for="error in this.errors.start_date" class="text-danger">{{ error }}<br></small>
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
                            <div v-if="this.errors && this.errors.end_date !== undefined">
                                <small v-for="error in this.errors.end_date" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>

                        <div class="row">
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
                        <button @click="eventShowModal.hide()"
                                type="button"
                                class="btn-close">
                        </button>
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
                                        <span>{{ selectedEvent.start_date | moment('ddd. Do MMM HH:mm') }}</span>
                                        <br>
                                        <span v-if="selectedEvent.end_date !== null">{{ selectedEvent.end_date | moment('ddd. Do MMM HH:mm') }}</span>
                                        <span v-else class="fst-italic">(all-day event)</span>
                                    </div>
                                </div>

                                <hr>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div @click="showEventChangeModal"
                             class="btn btn-primary">
                            Edit event
                        </div>
                        <div @click="deleteEvent(selectedEvent.id)"
                             class="btn btn-danger">
                            Delete event
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" ref="eventEditModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-admin-dark">
                    <div class="modal-header">
                        <h5 v-if="selectedEvent && selectedEvent.title !== null" class="modal-title">Edit event '{{ selectedEvent.title }}'</h5>
                        <button @click="eventEditModal.hide()"
                                type="button"
                                class="btn-close">
                        </button>
                    </div>
                    <div v-if="selectedEvent" class="modal-body">
                        <div class="row mb-3">
                            <div class="col">
                                <input v-model="selectedEvent.title"
                                       type="text"
                                       id="title"
                                       name="title"
                                       class="form-control"
                                       placeholder="Title"
                                       required>
                            </div>
                            <div v-if="this.errors && this.errors.title !== undefined">
                                <small v-for="error in this.errors.title" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <input v-model="selectedEvent.description"
                                       type="text"
                                       id="description"
                                       name="description"
                                       class="form-control"
                                       placeholder="Description (optional)">
                            </div>
                            <div v-if="this.errors && this.errors.description !== undefined">
                                <small v-for="error in this.errors.description" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <input v-model="selectedEvent.icon_id"
                                       type="number"
                                       id="icon_id"
                                       name="icon_id"
                                       class="form-control"
                                       placeholder="Icon ID (optional)"
                                       required>
                            </div>
                            <div v-if="this.errors && this.errors.icon_id !== undefined">
                                <small v-for="error in this.errors.icon_id" class="text-danger">{{ error }}<br></small>
                            </div>
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

                        <div class="row mb-3">
                            <label for="event_color" class="col-sm-3 col-form-label">Event color</label>
                            <div class="col-sm-9">
                                <input v-model="selectedEvent.event_color"
                                       type="color"
                                       id="event_color"
                                       name="event_color"
                                       class="form-control form-control-color"
                                       title="Choose your color">
                            </div>
                            <div v-if="this.errors && this.errors.event_color !== undefined">
                                <small v-for="error in this.errors.event_color" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="event_color" class="col-sm-3 col-form-label">Start date</label>
                            <div class="col-sm-9">
                                <input v-model="selectedEvent.start_date"
                                       type="datetime-local"
                                       id="start_date"
                                       name="start_date"
                                       class="form-control">
                            </div>
                            <div v-if="this.errors && this.errors.start_date !== undefined">
                                <small v-for="error in this.errors.start_date" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>

                        <div v-if="selectedEvent && selectedEvent.all_day === 'no'" class="row mb-3">
                            <label for="event_color" class="col-sm-3 col-form-label">End date</label>
                            <div class="col-sm-9">
                                <input v-model="selectedEvent.end_date"
                                       type="datetime-local"
                                       id="end_date"
                                       name="end_date"
                                       class="form-control">
                            </div>
                            <div v-if="this.errors && this.errors.end_date !== undefined">
                                <small v-for="error in this.errors.end_date" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-9">
                                <div class="form-check">
                                    <input v-model="selectedEvent.all_day"
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
                    </div>
                    <div class="modal-footer">
                        <div @click="updateEvent()"
                             class="btn btn-success">
                            Update event
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
                    this.errors = null;
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
                    this.errors = null;
                    this.calendarOptions.events = response.data;
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.toastError(error.response.data.message);
                    this.errors = error.response.data.errors;
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
                    this.errors = null;
                    this.getEvents();
                    this.eventCreateModal.hide()
                    this.toastSuccess();
                    this.fields = {};
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.toastError(error.response.data.message);
                    this.errors = error.response.data.errors;
                });
        },

        scheduleEvent(eventId, payload) {
            axios
                .post('/api/admin/calendar/' + eventId + '/schedule', payload)
                .then(() => {
                    this.errors = null;
                    this.getEvents();
                    this.toastSuccess();
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.getEvents();

                    this.toastError(error.response.data.message);
                    this.errors = error.response.data.errors;
                });
        },

        showEventChangeModal() {
            this.eventShowModal.hide()

            this.selectedEvent.start_date = moment(this.selectedEvent.start_date, 'YYYY-MM-DD\\THH:mm').format('YYYY-MM-DD\\THH:mm');

            if (this.selectedEvent.end_date === null) {
                this.selectedEvent.all_day = 'yes';
            } else {
                this.selectedEvent.end_date = moment(this.selectedEvent.end_date, 'YYYY-MM-DD\\THH:mm').format('YYYY-MM-DD\\THH:mm');
                this.selectedEvent.all_day = 'no';
            }

            this.eventEditModal.show()
        },

        updateEvent() {
            let payload = {
                'title': this.selectedEvent.title,
                'description': this.selectedEvent.description,
                'start_date': this.selectedEvent.start_date,
                'end_date': this.selectedEvent.all_day === 'yes' ? null : this.selectedEvent.end_date,
                'icon_id': this.selectedEvent.icon_id,
                'event_color': this.selectedEvent.event_color,
            }

            axios
                .post('/api/admin/calendar/' +  this.selectedEvent.id + '/update', payload)
                .then(() => {
                    this.errors = null;
                    this.getEvents();
                    this.eventCreateModal.hide()
                    this.toastSuccess();
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.toastError(error.response.data.message);
                    this.errors = error.response.data.errors;
                });
        },

        deleteEvent(eventId) {
            axios
                .post('/api/admin/calendar/' + eventId + '/destroy', {
                    _method: 'delete',
                })
                .then(() => {
                    this.errors = null;
                    this.getEvents();
                    this.eventShowModal.hide()
                    this.selectedEvent = null;
                    this.toastSuccess();
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.toastError(error.response.data.message);
                    this.errors = error.response.data.errors;
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

        toastError(errorMessage) {
            this.$swal.fire({
                toast: true,
                icon: 'error',
                title: 'Error',
                text: errorMessage,
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
            eventEditModal: null,

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

            errored: false,
            errors: null,
        }
    },

    mounted() {
        this.getEvents();

        this.eventCreateModal = new Modal(this.$refs.eventCreateModal)
        this.eventShowModal = new Modal(this.$refs.eventShowModal)
        this.eventEditModal = new Modal(this.$refs.eventEditModal)
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
