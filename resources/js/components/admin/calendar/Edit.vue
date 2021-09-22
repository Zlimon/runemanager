<template>
    <div class="modal fade" ref="eventEditModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-admin-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Edit event '{{ loadedEvent.title }}'</h5>
                    <button @click="eventEditModal.hide()"
                            type="button"
                            class="btn-close">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col">
                            <input v-model="loadedEvent.title"
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
                            <input v-model="loadedEvent.description"
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
                                    <img :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + (loadedEvent.icon_id ? loadedEvent.icon_id : 0) + '.png'"
                                         class="pixel hiscore-icon"
                                         alt="Event icon">
                                </span>
                                <input v-model="loadedEvent.icon_id"
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
                            <input v-model="loadedEvent.event_color"
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
                            <input v-model="loadedEvent.start_date"
                                   type="datetime-local"
                                   id="start_date"
                                   name="start_date"
                                   class="form-control">
                            <div v-if="this.errors && this.errors.start_date !== undefined">
                                <small v-for="error in this.errors.start_date" class="text-danger">{{ error }}<br></small>
                            </div>
                        </div>
                    </div>

                    <div v-if="!all_day" class="row mb-3">
                        <label for="event_color" class="col-sm-3 col-form-label">End date</label>
                        <div class="col-sm-9">
                            <input v-model="loadedEvent.end_date"
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
</template>

<script>
import {Modal} from 'bootstrap'

require('moment');

export default {
    name: "AdminCalendarEdit",

    props: {
        event: {required: true},
    },

    methods: {
        updateEvent() {
            let payload = {
                'title': this.loadedEvent.title,
                'description': this.loadedEvent.description,
                'start_date': this.loadedEvent.start_date,
                'end_date': this.all_day ? null : this.loadedEvent.end_date,
                'icon_id': this.loadedEvent.icon_id,
                'event_color': this.loadedEvent.event_color,
            }

            axios
                .post('/api/admin/calendar/' +  this.loadedEvent.id + '/update', payload)
                .then(() => {
                    this.errors = null;

                    this.closeEditModal(true);
                    this.eventEditModal.hide()
                    this.toastSuccess();

                    this.loadedEvent = {};
                })
                .catch(error => {
                    console.error(error.response.data);

                    this.toastError(error.response.data.message);
                    this.errors = error.response.data.errors;
                });
        },

        closeEditModal(updateEvents = false) {
            this.$emit('close', updateEvents)
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
            eventEditModal: null,

            loadedEvent: this.event,
            all_day: this.event.end_date === null,

            errors: null,
        }
    },

    mounted() {
        this.eventEditModal = new Modal(this.$refs.eventEditModal)
        this.eventEditModal.show();

        // Detect if modal is closed non-programmatically
        $(this.$refs.eventEditModal).on("hidden.bs.modal", this.closeEditModal)
    },
}
</script>

<style scoped>

</style>
