<template>
    <div>
        <div v-for="(notification, index) in notifications">
            <div class="background-dialog-iron-rivets p-2 mb-2" style="position:relative;">
                <div class="d-flex flex-row flex-wrap justify-content-center">
                    <div class="col-3 col-sm-4" style="padding: 0; margin: 0;">
                        <div v-if="isNaN(notification.icon)">
                            <img :alt="notification.icon + ' icon'"
                                 :src="'/images/' + notification.log.category.category + '/' + notification.icon.replace(/ /g,'_') + '.png'"
                                 class="pixel notification-icon">
                        </div>
                        <div v-else>
                            <img :alt="'Profile icon'"
                                 :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + notification.icon + '.png'"
                                 class="pixel notification-icon">
                        </div>
                    </div>

                    <div class="col" style="padding: 0; margin: 0;">
                        <span>{{ notification.message }}</span>
                    </div>
                </div>

                <div v-if="notification.log.data !== null && Object.keys(notification.log.data.collection).length > 0">
                    <h3 class="text-center">Received loot:</h3>
                    <div class="d-flex flex-row flex-wrap justify-content-center">
                        <div v-for="(count, index) in notification.log.data.collection"
                             class="background-world-map mx-2 p-1">
                            <img
                                :alt="index.replaceAll('_', ' ') + ' item icon'"
                                :src="!isNaN(notification.icon) ? '/storage/item/' + index + '.png' : '/images/' + notification.log.category.category + '/' + notification.icon.replace(/ /g,'_') + '/' + index + '.png'"
                                :title="index.replaceAll('_', ' ')"
                                class="pixel hiscore-icon">
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <small class="text-muted">{{ notification.log.created_at | moment("from") }}</small>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        notifications: {required: true},
    },
}
</script>
