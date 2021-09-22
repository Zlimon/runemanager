<template>
    <div>
        <div class="modal fade" ref="eventShowModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-admin-dark">
                    <div class="modal-header">
                        <h5 v-if="event && event.title !== null" class="modal-title">{{ event.title }}</h5>
                        <button @click="eventShowModal.hide()"
                                type="button"
                                class="btn-close">
                        </button>
                    </div>
                    <div v-if="event" class="modal-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <img v-if="event.icon_id !== null" :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + event.icon_id + '.png'" class="pixel icon" alt="Event icon">
                            </div>

                            <div class="col">
                                <p class="fw-bold">{{ event.description ? event.description : '' }}</p>

                                <hr>

                                <div class="d-flex text-left">
                                    <div class="col-3 pl-0">
                                        <span class="fw-bold">Starts:</span>
                                        <br>
                                        <span class="fw-bold">{{ event.end_date !== null ? 'Ends:' : '' }}</span>
                                    </div>
                                    <div class="col">
                                        <span>{{ event.start_date | moment('ddd. Do MMM HH:mm') }}</span>
                                        <br>
                                        <span v-if="event.end_date !== null">{{ event.end_date | moment('ddd. Do MMM HH:mm') }}</span>
                                        <span v-else class="fst-italic">(all-day event)</span>
                                    </div>
                                </div>

                                <hr>
                            </div>
                        </div>
                    </div>
                        <div class="modal-footer">
                            <div @click="showEditModal = true"
                                 class="btn btn-primary">
                                Edit event
                            </div>
                            <div @click="deleteEvent(event.id)"
                                 class="btn btn-danger">
                                Delete event
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <div v-if="showEditModal">
            <admin-calendar-edit :event="loadedEvent" @close="closeEditModal"></admin-calendar-edit>
        </div>
    </div>
</template>

<script>
import {Modal} from 'bootstrap'

import AdminCalendarEdit from "./Edit";

var moment = require('moment');

export default {
    name: "AdminCalendarShow",

    components: {AdminCalendarEdit},

    props: {
        event: {required: true},
    },

    methods: {
        deleteEvent(eventId) {
            axios
                .post('/api/admin/calendar/' + eventId + '/destroy', {
                    _method: 'delete',
                })
                .then(() => {
                    this.errors = null;

                    this.fetchEvents = true;
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

        closeShowModal() {
            this.$emit('close', this.fetchEvents)
        },

        closeEditModal(updateEvents = false) {
            this.showEditModal = false;

            this.fetchEvents = updateEvents;
        },
    },

    data() {
        return {
            eventShowModal: null,

            showEditModal: false,

            loadedEvent: this.event,

            fetchEvents: false,

            errors: null,
        }
    },

    mounted() {
        this.eventShowModal = new Modal(this.$refs.eventShowModal)
        this.eventShowModal.show();

        // Detect if modal is closed non-programmatically
        $(this.$refs.eventShowModal).on("hidden.bs.modal", this.closeShowModal)

        this.loadedEvent.start_date = moment(this.event.start_date, 'YYYY-MM-DD\\THH:mm').format('YYYY-MM-DD\\THH:mm');

        if (this.loadedEvent.end_date !== null) {
            this.loadedEvent.end_date = moment(this.event.end_date, 'YYYY-MM-DD\\THH:mm').format('YYYY-MM-DD\\THH:mm');
        }
    },
}
</script>

<style scoped>

</style>
