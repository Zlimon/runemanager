<template>
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
</template>

<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

export default {
    components: {FullCalendar},

    methods: {
        handleEventClick: function (arg) {
            var moment = require('moment');

            axios
                .get('/api/calendar/' + arg.event.id + '/show')
                .then((response) => {
                    this.$swal.fire({
                        title: response.data.title,
                        html:
                            (response.data.icon_id !== null ? `<img src="https://www.osrsbox.com/osrsbox-db/items-icons/${response.data.icon_id}.png" class="pixel icon mb-2" alt=""><br>` : '') +
                            '<p class="font-weight-bold">' + (response.data.description ? response.data.description : '') + '</p>' +
                            (response.data.end_date === null ? `<span class="font-weight-bold">When?</span> ${moment(response.data.start_date).format('ddd. Do MMMM')}` : `<span class="font-weight-bold">From:</span> ${moment(response.data.start_date).format('ddd. Do MMMM HH:mm')}`) +
                            (response.data.end_date !== null ? `<br> <span class="font-weight-bold">To:</span> ${moment(response.data.end_date).format('ddd. Do MMMM HH:mm')}` : ''),
                        showCloseButton: true,
                        showCancelButton: true,
                        confirmButtonText: `Delete`,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            axios
                                .post('/api/admin/calendar/' + arg.event.id + '/destroy', {
                                    _method: 'delete',
                                })
                                .then(() => {
                                    this.getEvents();

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
                                })
                                .catch(error => {
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

                                    console.error(error.response.data)
                                });
                        }
                    })
                })
                .catch(error => {
                    console.error(error)
                })
                .finally(() => this.loading = false)
        },

        handleDateClick: async function (arg) {
            var moment = require('moment');

            const {value: formValues} = await this.$swal({
                title: `Create new event for <br>${moment(arg.dateStr).format('ddd. Do MMMM')}`,
                html:
                    '<div class="text-left">'+
                        '<div class="form-group">' +
                            '<input type="text" class="form-control" id="title" placeholder="Title" required>' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<input type="text" class="form-control" id="description" aria-describedby="descriptionHelp" placeholder="Description (optional)">' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<input type="text" class="form-control" id="icon_id" aria-describedby="icon_idHelp" placeholder="Icon ID (optional)">' +
                            '<div class="form-text">' +
                                '<small id="icon_idHelp" class="text-muted">Type in the ID of an icon you wish to display as an event icon</small><br>' +
                                '<small id="icon_idHelp" class="text-muted">Search icons <a target="_blank" rel="noopener noreferrer" href="https://www.osrsbox.com/tools/item-search/">here</a> </small>' +
                            '</div>' +
                        '</div>' +
                        '<div class="d-flex form-group">' +
                            '<label class="col-3 pl-0" for="event_color">Event color</label>' +
                            '<input type="color" class="form-control mx-sm-3 w-25" name="event_color" id="event_color" value="#3490dc">' +
                        '</div>' +
                        '<div class="d-flex form-group">' +
                            '<label class="col-3 pl-0" for="start_date">Start time</label>' +
                            `<input type="time" class="form-control mx-sm-3" id="start_date" value="${moment(new Date()).format('HH:mm')}" placeholder="Start time">` +
                        '</div>' +
                        '<div class="d-flex form-group">' +
                            '<label class="col-3 pl-0" for="end_date">End date</label>' +
                            `<input type="datetime-local" class="form-control mx-sm-3" id="end_date" value="${moment(arg.dateStr).format('YYYY-MM-DD\\THH:mm')}" placeholder="End date">` +
                        '</div>' +
                        '<div class="form-check">' +
                            '<input class="form-check-input" type="checkbox" id="all_day">' +
                            '<label class="form-check-label" for="all_day">All-day event</label>' +
                        '</div>' +
                    '</div>',
                focusConfirm: false,
                showCancelButton: true,
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !this.$swal.isLoading(),
                preConfirm: () => {
                    return [
                        document.getElementById('title').value, // 0
                        document.getElementById('description').value, // 1
                        document.getElementById('icon_id').value, // 2
                        document.getElementById('event_color').value, // 3
                        document.getElementById('start_date').value, // 4
                        document.getElementById('end_date').value, // 5
                        document.getElementById('all_day').checked, // 6
                    ]
                }
            })

            if (formValues) {
                const start = moment(arg.dateStr, 'YYYY-MM-DD\\THH:mm');

                const hour = formValues[4].match(/(.*):/g).pop().replace(":", "");
                const minute = formValues[4].match(/:(.*)/g).pop().replace(":", "");

                start.set({h: hour, m: minute});

                let payload = {
                    'title': formValues[0],
                    'description': formValues[1],
                    'start_date': start.format('YYYY-MM-DD\\THH:mm'),
                    'end_date': formValues[6] === false ? formValues[5] : null,
                    'icon_id': formValues[2],
                    'event_color': formValues[3],
                }

                axios
                    .post('/api/admin/calendar/create', payload)
                    .then(() => {
                        this.getEvents();

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
                    })
                    .catch(error => {
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

                        console.error(error.response.data)
                    });
            }
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

        scheduleEvent(eventId, payload) {
            axios
                .post('/api/admin/calendar/' + eventId + '/schedule', payload)
                .then(() => {
                    this.getEvents();

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
                })
                .catch(error => {
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

                    console.error(error.response.data)
                });
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
