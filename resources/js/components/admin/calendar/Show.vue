<template>
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
<!--                    <div class="modal-footer">-->
<!--                        <div @click="showEventChangeModal"-->
<!--                             class="btn btn-primary">-->
<!--                            Edit event-->
<!--                        </div>-->
<!--                        <div @click="deleteEvent(event.id)"-->
<!--                             class="btn btn-danger">-->
<!--                            Delete event-->
<!--                        </div>-->
<!--                    </div>-->
            </div>
        </div>
    </div>
</template>

<script>
import {Modal} from 'bootstrap'

require('moment');

export default {
    name: "AdminCalendarShow",

    props: {
        event: {required: true},
    },

    methods: {
        closeShowModal() {
            this.$emit('close')
        },
    },

    data() {
        return {
            eventShowModal: null,

            errors: null,
        }
    },

    mounted() {
        this.eventShowModal = new Modal(this.$refs.eventShowModal)
        this.eventShowModal.show();

        // Detect if modal is closed non-programmatically
        $(this.$refs.eventShowModal).on("hidden.bs.modal", this.closeShowModal)
    },
}
</script>

<style scoped>

</style>
