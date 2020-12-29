<template>
    <div>
        <div v-if="notifications.length > 0">
            <div v-for="(notification, index) in notifications">
                <div class="background-dialog-iron-rivets p-2 mb-2">
                    <div class="row align-items-center mb-2">
                        <div class="col-3 col-sm-4 icon-border">
                            <img :alt="notification.icon + ' icon'"
                                 :src="'/images/' + notification.category.category + '/' + notification.icon + '.png'"
                                 class="pixel notification-icon">
                        </div>

                        <div class="col">
                            <span>{{ notification.message }}</span>
                        </div>
                    </div>

                    <div v-if="notification.data !== null && Object.keys(notification.data.collection).length > 0">
                        <h3 class="text-center">Received loot:</h3>
                        <div class="d-flex flex-row flex-wrap justify-content-center pb-2">
                            <div v-for="(count, index) in notification.data.collection"
                                 class="background-world-map mx-2 p-1">
                                <img
                                    :alt="index + ' item icon'"
                                    :src="'/images/' + notification.category.category + '/' + notification.icon.replace(/ /g,'_') + '/' + index + '.png'"
                                    class="pixel hiscore-icon">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <div class="text-center">
                <h3>Nothing interesting happens.</h3>
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
