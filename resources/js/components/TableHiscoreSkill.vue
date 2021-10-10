<template>
    <table class="table-responsive">
        <tr>
            <th class="d-none d-md-table-cell">Name</th>
            <th>Rank</th>
            <th>Level</th>
            <th>XP</th>
            <th></th>
        </tr>

        <tr class="bg-dark text-light background-dialog-panel">
            <td class="d-none d-md-table-cell ps-2">Overall</td>
            <td>{{ account.account.rank }}</td>
            <td>{{ account.account.level }}</td>
            <td>{{ account.account.xp }}</td>
            <td>
                <img :src="'/storage/resource-pack/skill/total.png'"
                     :alt="'Total level icon'"
                     :title="'Click here to visit total level hiscores'">
            </td>
        </tr>

        <tr v-for="(hiscore, name) in account.hiscore.skill"
            class="bg-dark text-light background-dialog-panel">
            <td class="d-none d-md-table-cell text-capitalize ps-2">{{ name }}</td>
            <td class="ps-2 ps-md-0">{{ hiscore.rank }}</td>
            <td>{{ hiscore.level }}</td>
            <td>{{ hiscore.xp }}</td>
            <td>
                <img :src="'/storage/resource-pack/skill/' + name + '.png'"
                     :alt="name + ' skill icon'"
                     :title="'Click here to visit ' + name + ' hiscores'">
            </td>
        </tr>
    </table>
</template>

<script>
export default {
    name: "TableHiscoreSkill",

    props: {
        account: {required: true},
    },
}
</script>

<style scoped>

</style>
