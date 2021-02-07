<template>
    <div>
        <div v-if="errored" class="text-center py-5">
            <img src="/images/ignore.png"
                 class="pixel icon"
                 alt="Sad face">
            <h3>Sorry, no quest journals were found</h3>
        </div>

        <div v-else>
            <div v-if="loading">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>

            <div v-else>
                <div v-for="(questCategories, index) in quests">
                    <p class="runescape-normal" style="margin: 0; font-size: 1.25rem;">{{ (index === 0 ? "Free Quests" : index === 1 ? "Members" : index === 2 ? "Miniquests" : "Secret :o") }}</p>
                    <div v-for="(quest, index) in questCategories">
                        <div v-if="quest.status === 'completed'">
                            <p class="runescape-success" style="margin: 0; font-weight: normal;">{{ quest.quest }}</p>
                        </div>
                        <div v-else-if="quest.status === 'in_progress'">
                            <p class="runescape-progress" style="margin: 0; font-weight: normal;">{{ quest.quest }}</p>
                        </div>
                        <div v-else-if="quest.status === 'not_started'">
                            <p class="runescape-danger" style="margin: 0; font-weight: normal;">{{ quest.quest }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        account: {required: true},
    },

    methods: {
        checkAccount(accountId) {
            return this.account.id === accountId;
        },
    },

    data() {
        return {
            loading: true,
            errored: false,
            quests: [],
        }
    },

    mounted() {
        axios
            .get('/api/account/' + this.account.username + '/quests')
            .then((response) => {
                this.quests = response.data.data;
            })
            .catch(error => {
                console.log(error)
                this.errored = true
            })
            .finally(() => this.loading = false)
    },
}
</script>

<style scoped>
::-webkit-scrollbar {
    width: 16px;
}

/* Track */
::-webkit-scrollbar-track {
    background: url('/resource-packs/pack-osrs-dark/scrollbar/thumb_middle_dark.png');
}

/* Handle */
::-webkit-scrollbar-thumb {
    background: url('/resource-packs/pack-osrs-dark/scrollbar/thumb_top.png') no-repeat,
    url('/resource-packs/pack-osrs-dark/scrollbar/thumb_bottom.png') no-repeat,
    url('/resource-packs/pack-osrs-dark/scrollbar/thumb_middle.png');
    background-position: top,
    bottom,
    center;
}
::-webkit-scrollbar-thumb:window-inactive {
    background: rgba(255,0,0,0.4);
}

/* Up */
::-webkit-scrollbar-button:single-button:vertical:decrement {
    background: url('/resource-packs/pack-osrs-dark/scrollbar/arrow_up.png');
}

::-webkit-scrollbar-button:single-button:vertical:decrement:hover {
    border-color: transparent transparent #777777 transparent;
}
/* Down */
::-webkit-scrollbar-button:single-button:vertical:increment {
    background: url('/resource-packs/pack-osrs-dark/scrollbar/arrow_down.png');
}
</style>
