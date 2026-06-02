<script setup>
import {ref} from "vue";

const props = defineProps({
    itemProp: Object,
});

let item = ref(props.itemProp);

let activeItem = ref(null);
</script>

<template>
    <div class="box h-14 w-14 hover:bg-base-200"
         @mouseleave="activeItem = null"
         @mouseover="item.item !== null ? activeItem = item.item?._id : null">
        <div v-if="item.item !== null">
            <span v-if="item.quantity > 1"
                  class="absolute p-1 text-xs font-bold">
                {{ item.quantity }}
            </span>
            <div class="flex justify-center items-center h-14">
                <img v-if="item.item.icon"
                     :class="{ 'opacity-50': item.quantity === 0 }"
                     :src="`data:image/jpeg;base64,${item.item.icon}`"
                     class="object-contain"
                     loading="lazy">
                <span v-else>
                    {{ item.item.name }}
                </span>
            </div>
        </div>
    </div>

    <!-- Tooltip -->
    <div v-if="activeItem === item.item?._id"
         class="box-tooltip">
        <p>
            {{ item.item.name }}
        </p>
        <p>
            {{ item.item.examine }}
        </p>
        <p v-if="item.quantity > 0 && item.item.highalch">
            HA: {{ (item.item.highalch * item.quantity).toLocaleString('en-US') }} gp
            <span v-if="item.quantity > 1">({{ item.item.highalch.toLocaleString('en-US') }} ea)</span>
        </p>
        <p v-if="item.quantity > 0 && item.item.lowalch">
            LA: {{ (item.item.lowalch * item.quantity).toLocaleString('en-US') }} gp
            <span v-if="item.quantity > 1">({{ item.item.lowalch.toLocaleString('en-US') }} ea)</span>
        </p>
    </div>
</template>
