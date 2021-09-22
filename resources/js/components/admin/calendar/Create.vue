<template>
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
                            <input v-model="loadedFields.title"
                                   v-bind:class="{ 'is-invalid' : this.errors && this.errors.title !== undefined }"
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
                            <input v-model="loadedFields.description"
                                   v-bind:class="{ 'is-invalid' : this.errors && this.errors.description !== undefined }"
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
                            <div class="input-group">
                                <span class="input-group-text">
                                    <img :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + (loadedFields.icon_id ? loadedFields.icon_id : 0) + '.png'"
                                         class="pixel hiscore-icon"
                                         alt="Event icon">
                                </span>
                                <input v-model="loadedFields.icon_id"
                                       v-bind:class="{ 'is-invalid' : this.errors && this.errors.icon_id !== undefined }"
                                       type="number"
                                       id="icon_id"
                                       name="icon_id"
                                       class="form-control"
                                       placeholder="Icon ID (optional)">
                            </div>
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
                            <input v-model="loadedFields.event_color"
                                   v-bind:class="{ 'is-invalid' : this.errors && this.errors.event_color !== undefined }"
                                   type="color"
                                   id="event_color"
                                   name="event_color"
                                   class="form-control form-control-color"
                                   title="Choose your color">
                            <div v-if="this.errors && this.errors.event_color !== undefined">
                                <small v-for="error in this.errors.event_color" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="event_color" class="col-sm-3 col-form-label">Start date</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <span class="input-group-text">
                                    {{ this.loadedFields.start_date | moment("ddd. Do MMM") }}
                                </span>
                                <input v-model="loadedFields.start_time"
                                       v-bind:class="{ 'is-invalid' : this.errors && this.errors.start_date !== undefined }"
                                       type="time"
                                       id="start_time"
                                       name="start_time"
                                       class="form-control"
                                       required>
                                <div v-if="this.errors && this.errors.start_date !== undefined">
                                    <small v-for="error in this.errors.start_date" class="text-danger">{{ error }}<br></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="!all_day" class="row mb-3">
                        <label for="event_color" class="col-sm-3 col-form-label">End date</label>
                        <div class="col-sm-9">
                            <input v-model="loadedFields.end_date"
                                   v-bind:class="{ 'is-invalid' : this.errors && this.errors.end_date !== undefined }"
                                   type="datetime-local"
                                   id="end_date"
                                   name="end_date"
                                   class="form-control">
                            <div v-if="this.errors && this.errors.end_date !== undefined">
                                <small v-for="error in this.errors.end_date" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-9">
                            <div class="form-check">
                                <input @click="all_day = !all_day" v-model="all_day"
                                       type="checkbox"
                                       id="all_day"
                                       name="all_day"
                                       class="form-check-input"
                                       >
                                <label for="all_day" class="form-check-label">
                                    All-day event?
                                </label>
                            </div>
                        </div>
                    </div>

                    <input v-model="loadedFields.start_date" type="hidden" id="start_date">
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
</template>

<script>
import {Modal} from 'bootstrap'

var moment = require('moment');

export default {
    name: "AdminCalendarCreate",

    props: {
        fields: {required: true},
    },

    methods: {
        createEvent() {
            const start = moment(this.loadedFields.start_date, 'YYYY-MM-DD\\THH:mm');

            const hour = this.loadedFields.start_time.match(/(.*):/g).pop().replace(":", "");
            const minute = this.loadedFields.start_time.match(/:(.*)/g).pop().replace(":", "");

            start.set({h: hour, m: minute});

            let payload = {
                'title': this.loadedFields.title,
                'description': this.loadedFields.description,
                'start_date': start.format('YYYY-MM-DD\\THH:mm'),
                'end_date': this.all_day ? null : this.loadedFields.end_date,
                'icon_id': this.loadedFields.icon_id,
                'event_color': this.loadedFields.event_color,
            }

            axios
                .post('/api/admin/calendar/create', payload)
                .then(() => {
                    this.errors = null;

                    this.closeCreateModal(true);
                    this.eventCreateModal.hide()
                    this.doSuccess('Successfully created event "' + this.loadedFields.title + '".');

                    this.loadedFields = {};
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.errors = error.response.data.errors;
                    this.toastError(error.response.data.message);
                });
        },

        closeCreateModal(updateEvents = false) {
            this.$emit('close', updateEvents)
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

            loadedFields: this.fields,
            all_day: false,

            errors: null,
        }
    },

    mounted() {
        this.eventCreateModal = new Modal(this.$refs.eventCreateModal)
        this.eventCreateModal.show();

        // Detect if modal is closed non-programmatically
        $(this.$refs.eventCreateModal).on("hidden.bs.modal", this.closeCreateModal)
    },
}
</script>

<style scoped>

</style>
