<template>
    <div>
        <div v-for="(broadcast, index) in broadcasts">
            <div class="background-dialog-iron-rivets p-2 mb-2" style="position:relative;">
                <div class="d-flex flex-row flex-wrap justify-content-center">
                    <div class="col-3 col-sm-4" style="padding: 0; margin: 0;">
                        <div v-if="isNaN(broadcast.icon)">
                            <img alt="Notification icon'"
                                 :src="'/images/' + broadcast.log.category.category + '/' + broadcast.icon.replace(/ /g,'_') + '.png'"
                                 class="pixel broadcast-icon">
                        </div>
                        <div v-else>
                            <img alt="Notification icon"
                                 :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + broadcast.icon + '.png'"
                                 class="pixel broadcast-icon">
                        </div>
                    </div>

                    <div class="col" style="padding: 0; margin: 0;">
                        <span>{{ broadcast.message }}</span>
                    </div>
                </div>

                <div v-if="broadcast.log !== null">
                    <div v-if="broadcast.log.action === 'account-loot-update'">
                        <div v-if="broadcast.log.data.type === 'NPC'">
                            <div v-if="broadcast.log.data.updatedCollection !== null && Object.keys(broadcast.log.data.updatedCollection).length > 0">
                                <h3 class="text-center">Received loot:</h3>
                                <div class="d-flex flex-row flex-wrap justify-content-center">
                                    <div v-for="(count, index) in broadcast.log.data.updatedCollection"
                                         class="background-world-map mx-2 p-1">
                                        <img
                                            :alt="index.replaceAll('_', ' ') + ' item icon'"
                                            :src="!isNaN(broadcast.icon) ? '/storage/items/' + index + '.png' : '/images/' + broadcast.log.category.category + '/' + broadcast.icon.replace(/ /g,'_') + '/' + index + '.png'"
                                            :title="index.replaceAll('_', ' ') + ' x ' + count | capitalize"
                                            class="pixel hiscore-icon">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="broadcast.log.data.type === 'EVENT'">
                            <div v-if="broadcast.log.data.metadata !== null && Object.keys(broadcast.log.data.metadata).length > 0">
                                <h3 class="text-center">Received loot:</h3>
                                <div class="d-flex flex-row flex-wrap justify-content-center">
                                    <div v-for="loot in broadcast.log.data.metadata"
                                         class="background-world-map mx-2 p-1">
                                        <img
                                            :alt="loot.name.replaceAll('_', ' ') + ' item icon'"
                                            :src="'https://www.osrsbox.com/osrsbox-db/items-icons/' + loot.id + '.png'"
                                            :title="loot.name.replaceAll('_', ' ') + ' x ' + loot.quantity | capitalize"
                                            class="pixel hiscore-icon">
                                    </div>
                                </div>

                                <div class="text-center">
                                    <small>Total value: {{ broadcast.log.total}} gp</small>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="broadcast.log.data.type === 'UNIQUE'">
                            <div v-if="broadcast.log.data.metadata !== null && Object.keys(broadcast.log.data.metadata).length > 0">
                                <div class="d-flex flex-row flex-wrap justify-content-center">
                                    <div v-for="loot in broadcast.log.data.metadata"
                                         class="background-world-map mx-2 p-1">
                                        <img
                                            :alt="loot.name.replaceAll('_', ' ') + ' item icon'"
                                            :src="'/images/' + broadcast.log.category.category + '/' + broadcast.icon.replace(/ /g,'_') + '/' + loot.name + '.png'"
                                            :title="loot.name.replaceAll('_', ' ') + ' x ' + loot.quantity | capitalize"
                                            class="pixel hiscore-icon">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <small class="text-muted" :title="broadcast.log.created_at | moment('dddd, MMMM Do YYYY, hh:mm:ss')">{{ broadcast.log.created_at | moment("from") | capitalize }}</small>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        broadcasts: {required: true},
    },

    filters: {
        capitalize: function (value) {
            if (!value) return ''
            value = value.toString()
            return value.charAt(0).toUpperCase() + value.slice(1)
        },
    }
}
</script>
