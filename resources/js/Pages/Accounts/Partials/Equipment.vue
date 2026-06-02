<script setup>
import {ref} from "vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);
</script>

<template>
    <div class="mx-auto p-6 w-[168px]"
         style="background-image: url('/images/equipment_slots.png'); background-repeat: no-repeat; background-position: center;">
        <div v-if="account.equipment !== null">
            <div class="ml-1 flex justify-center mt-[8px]">
                <img v-if="account.equipment.head_item !== null"
                     :src="`data:image/jpeg;base64,${account.equipment.head_item.icon}`"
                     :title="account.equipment.head_item.name">
            </div>

            <div class="ml-1 flex justify-center mt-[8px]">
                <img v-if="account.equipment.cape_item !== null"
                     :src="`data:image/jpeg;base64,${account.equipment.cape_item.icon}`"
                     :title="account.equipment.cape_item.name"
                     style="margin-right: 5px;">
                <img v-if="account.equipment.neck_item !== null"
                     :src="`data:image/jpeg;base64,${account.equipment.neck_item.icon}`"
                     :title="account.equipment.neck_item.name">
                <img v-if="account.equipment.ammo_item !== null"
                     :src="`data:image/jpeg;base64,${account.equipment.ammo_item.icon}`"
                     :title="account.equipment.ammo_item.name"
                     style="margin-left: 5px;">
            </div>

            <div class="ml-1 flex justify-center mt-[8px]">
                <img v-if="account.equipment.weapon_item !== null"
                     :src="`data:image/jpeg;base64,${account.equipment.weapon_item.icon}`"
                     :title="account.equipment.weapon_item.name"
                     style="margin-right: 20px;">
                <img v-if="account.equipment.body_item !== null"
                     :src="`data:image/jpeg;base64,${account.equipment.body_item.icon}`"
                     :title="account.equipment.body_item.name">
                <img v-if="account.equipment.shield_item !== null"
                     :src="`data:image/jpeg;base64,${account.equipment.shield_item.icon}`"
                     :title="account.equipment.shield_item.name"
                     style="margin-left: 20px;">
            </div>

            <div class="ml-1 flex justify-center mt-[8px]">
                <img v-if="account.equipment.legs_item !== null"
                     :src="`data:image/jpeg;base64,${account.equipment.legs_item.icon}`"
                     :title="account.equipment.legs_item.name">
            </div>

            <div class="ml-1 flex justify-center mt-[8px]">
                <img v-if="account.equipment.hands_item !== null"
                     :src="`data:image/jpeg;base64,${account.equipment.hands_item.icon}`"
                     :title="account.equipment.hands_item.name"
                     style="margin-right: 20px;">
                <img v-if="account.equipment.feet_item !== null"
                     :src="`data:image/jpeg;base64,${account.equipment.feet_item.icon}`"
                     :title="account.equipment.feet_item.name">
                <img v-if="account.equipment.ring_item !== null"
                     :src="`data:image/jpeg;base64,${account.equipment.ring_item.icon}`"
                     :title="account.equipment.ring_item.name"
                     style="margin-left: 20px;">
            </div>
        </div>
    </div>
</template>
