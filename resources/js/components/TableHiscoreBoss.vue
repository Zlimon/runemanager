<template>
    <table class="table-responsive">
        <tr>
            <th class="d-none d-md-table-cell">Name</th>
            <th>Rank</th>
            <th>Kill Count</th>
            <th>Collection Log</th>
            <th>Obtained</th>
        </tr>

        <tr v-for="(hiscore, index) in account.hiscore.boss">
            <td>{{ index }}</td>
            <td>{{ hiscore.rank }}</td>
            <td>{{ hiscore.kill_count }}</td>
            <td>
                <a :data-target="$idRef(index)" class="btn background-world-map" data-toggle="modal">
                    <img :title="'Click here to see collection log for ' + index"
                         alt="Collection log item icon"
                         src="https://www.osrsbox.com/osrsbox-db/items-icons/22711.png">
                </a>
            </td>
            <td class="d-none d-sm-table-cell">
<!--                <div v-if="(hiscore.obtained !== null ? hiscore.obtained : 0) === total">-->
<!--                                <span class="runescape-success">-->
<!--                                    {{ (hiscore.obtained !== null ? hiscore.obtained : 0) }} / {{ total }}-->
<!--                                </span>-->
<!--                </div>-->
<!--                <div v-else-if="hiscore.obtained > 0">-->
<!--                    <span class="runescape-progress">{{ hiscore.obtained }} / {{ total }}</span>-->
<!--                </div>-->
<!--                <div v-else>-->
<!--                                <span class="runescape-danger">-->
<!--                                    {{ (hiscore.obtained !== null ? hiscore.obtained : 0) }} / {{ total }}-->
<!--                                </span>-->
<!--                </div>-->
            </td>
            <div :id="$id(index)" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark">
                        <div class="modal-body background-dialog-iron-rivets text-light">
                            <button class="btn btn-lg button-window-close float-right"
                                    data-dismiss="modal"></button>
                            <h1>{{ hiscore.username }}</h1>
                            <div class="d-flex flex-row flex-wrap justify-content-center">
                                <div v-for="(count, item) in hiscore.log"
                                     class="collection-log-item rounded background-world-map bg-dark p-4">
                                    <div v-if="count === 1">
                                        <img :alt="item + ' item icon'"
                                             :src="'/images/' + category + '/' + meta.slug + '/' + item + '.png'"
                                             :title="item.replaceAll('_', ' ') | capitalize"
                                             class="pixel hiscore-icon">
                                    </div>
                                    <div v-else-if="count > 0">
                                        <img :alt="item + ' item icon'"
                                             :src="'/images/' + category + '/' + meta.slug + '/' + item + '.png'"
                                             :title="item.replaceAll('_', ' ') | capitalize"
                                             class="pixel hiscore-icon">
                                        <span class="collection-log-item-counter runescape-progress">
                                                {{ count }}
                                            </span>
                                    </div>
                                    <div v-else>
                                        <img :alt="item + ' item icon'"
                                             :src="'/images/' + category + '/' + meta.slug + '/' + item + '.png'"
                                             :title="item.replaceAll('_', ' ') | capitalize"
                                             class="pixel hiscore-icon opacity-25">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </tr>
    </table>
</template>

<script>
export default {
    name: "TableHiscoreBoss",

    props: {
        account: {required: true},
    },

    mounted() {
        console.log(this.account)
    }
}
</script>

<style scoped>

</style>
