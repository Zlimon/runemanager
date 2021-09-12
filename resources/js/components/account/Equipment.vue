<template>
    <div>
        <div v-if="loading">
            <div class="d-flex justify-content-center">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>

        <div v-else>
            <div v-if="typeof user.user !== 'undefined' && account.user_id === user.user.id" class="text-center">
                <form>
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="equipmentDisplayToggle"
                               @change="updateDisplayEquipment()"
                               :checked="display">
                        <label class="custom-control-label" for="equipmentDisplayToggle">Display equipment</label>
                    </div>
                </form>
            </div>

            <div
                style="background-image: url('/images/equipment_slots.png'); background-repeat: no-repeat; background-position: center;  width: 168px; margin: 0 auto;">
                <div class="row justify-content-center">
                    <img
                        :src="(typeof equipment[0] !== 'undefined' ? equipment[0].id : 0) > 0 ? 'https://www.osrsbox.com/osrsbox-db/items-icons/' + equipment[0].id  + '.png' : '/storage/resource-pack/equipment/slot_head.png'"
                        class="slot-tile pixel" alt="Helmet"
                        :title="typeof equipment[0] !== 'undefined' && equipment[0].name">
                </div>

                <div class="row justify-content-center" style="margin-top: 3px;">
                    <img
                        :src="(typeof equipment[1] !== 'undefined' ? equipment[1].id : 0) > 0 ? 'https://www.osrsbox.com/osrsbox-db/items-icons/' + equipment[1].id  + '.png' : '/storage/resource-pack/equipment/slot_cape.png'"
                        class="slot-tile pixel" alt="Cape"
                        :title="typeof equipment[1] !== 'undefined' && equipment[1].name"
                        style="margin-right: 5px;">
                    <img
                        :src="(typeof equipment[2] !== 'undefined' ? equipment[2].id : 0) > 0 ? 'https://www.osrsbox.com/osrsbox-db/items-icons/' + equipment[2].id  + '.png' : '/storage/resource-pack/equipment/slot_neck.png'"
                        class="slot-tile pixel" alt="Amulet"
                        :title="typeof equipment[2] !== 'undefined' && equipment[2].name">
                    <img
                        :src="(typeof equipment[10] !== 'undefined' ? equipment[10].id : 0) > 0 ? 'https://www.osrsbox.com/osrsbox-db/items-icons/' + equipment[10].id  + '.png' : '/storage/resource-pack/equipment/slot_ammunition.png'"
                        class="slot-tile pixel" alt="Ammunition"
                        :title="typeof equipment[10] !== 'undefined' && equipment[10].name"
                        style="margin-left: 5px;">
                </div>

                <div class="row justify-content-center" style="margin-top: 3px;">
                    <img
                        :src="(typeof equipment[3] !== 'undefined' ? equipment[3].id : 0) > 0 ? 'https://www.osrsbox.com/osrsbox-db/items-icons/' + equipment[3].id  + '.png' : '/storage/resource-pack/equipment/slot_weapon.png'"
                        class="slot-tile pixel" alt="Weapon"
                        :title="typeof equipment[3] !== 'undefined' && equipment[3].name"
                        style="margin-right: 20px;">
                    <img
                        :src="(typeof equipment[4] !== 'undefined' ? equipment[4].id : 0) > 0 ? 'https://www.osrsbox.com/osrsbox-db/items-icons/' + equipment[4].id  + '.png' : '/storage/resource-pack/equipment/slot_torso.png'"
                        class="slot-tile pixel" alt="Platebody"
                        :title="typeof equipment[4] !== 'undefined' && equipment[4].name">
                    <img
                        :src="(typeof equipment[5] !== 'undefined' ? equipment[5].id : 0) > 0 ? 'https://www.osrsbox.com/osrsbox-db/items-icons/' + equipment[5].id  + '.png' : '/storage/resource-pack/equipment/slot_shield.png'"
                        class="slot-tile pixel" alt="Shield"
                        :title="typeof equipment[5] !== 'undefined' && equipment[5].name"
                        style="margin-left: 20px;">
                </div>

                <div class="row justify-content-center" style="margin-top: 4px;">
                    <img
                        :src="(typeof equipment[6] !== 'undefined' ? equipment[6].id : 0) > 0 ? 'https://www.osrsbox.com/osrsbox-db/items-icons/' + equipment[6].id  + '.png' : '/storage/resource-pack/equipment/slot_legs.png'"
                        class="slot-tile pixel" alt="Platelegs"
                        :title="typeof equipment[6] !== 'undefined' && equipment[6].name">
                </div>

                <div class="row justify-content-center" style="margin-top: 4px;">
                    <img
                        :src="(typeof equipment[7] !== 'undefined' ? equipment[7].id : 0) > 0 ? 'https://www.osrsbox.com/osrsbox-db/items-icons/' + equipment[7].id  + '.png' : '/storage/resource-pack/equipment/slot_hands.png'"
                        class="slot-tile pixel" alt="Gloves"
                        :title="typeof equipment[7] !== 'undefined' && equipment[7].name"
                        style="margin-right: 20px;">
                    <img
                        :src="(typeof equipment[8] !== 'undefined' ? equipment[8].id : 0) > 0 ? 'https://www.osrsbox.com/osrsbox-db/items-icons/' + equipment[8].id  + '.png' : '/storage/resource-pack/equipment/slot_feet.png'"
                        class="slot-tile pixel" alt="Footwear"
                        :title="typeof equipment[8] !== 'undefined' && equipment[8].name">
                    <img
                        :src="(typeof equipment[9] !== 'undefined' ? equipment[9].id : 0) > 0 ? 'https://www.osrsbox.com/osrsbox-db/items-icons/' + equipment[9].id  + '.png' : '/storage/resource-pack/equipment/slot_ring.png'"
                        class="slot-tile pixel" alt="Ring"
                        :title="typeof equipment[9] !== 'undefined' && equipment[9].name"
                        style="margin-left: 20px;">
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

        fetchAccountEquipment() {
            axios
                .get('/api/account/' + this.account.username + '/equipment')
                .then((response) => {
                    this.equipment = response.data.data;
                    this.display = response.data.display !== 0;
                })
                .catch(error => {
                    console.log(error)
                })
                .finally(() => this.loading = false);
        },

        updateDisplayEquipment() {
            axios
                .post('/api/account/' + this.account.username + '/equipment', {
                    _method: 'patch',
                })
                .then((response) => {
                    console.log(response.data); // TODO local notification
                })
                .catch(error => {
                    console.log(error)
                });
        },
    },

    watch: {
        account(account) {
            this.account = account;
            this.fetchAccountEquipment();
        }
    },

    data() {
        return {
            loading: true,
            user: {},
            equipment: [],
            display: false,
        }
    },

    mounted() {
        axios
            .get('/api/user')
            .then(response => {
                this.user = response.data;
            })
            .catch(error => {
                console.log(error)
            });

        this.fetchAccountEquipment();
    },

    created() {
        window.Echo.channel('account-equipment')
            .listen('AccountEquipment', (e) => {
                if (this.checkAccount(e.equipment.account_id)) {
                    if (e.equipment.display === 1) {
                        this.equipment = e.equipment.data;
                    } else {
                        this.equipment = [];
                    }
                }
            });
    },
}
</script>

<style scoped>
.slot-tile {
    background-image: url('/storage/resource-pack/equipment/slot_tile.png');
    width: 36px;
    height: 36px;
    object-fit: contain;
}
</style>
