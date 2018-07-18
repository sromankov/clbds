<template>
    <table class="table table-striped">
        <thead>
        <td>Range</td>
        <td>Price</td>
        <td>mon</td>
        <td>tue</td>
        <td>wed</td>
        <td>thu</td>
        <td>fri</td>
        <td>sat</td>
        <td>sun</td>
        <td>actions</td>
        </thead>
        <tbody>
        <interval-row v-for="intervalData in intervalsList"
                      :intervalData="intervalData"
                      :key="intervalData.id"></interval-row>
        </tbody>
    </table>
</template>

<script>

    import IntervalRow from './IntervalRow.vue';

    export default {

        components: {
            IntervalRow: IntervalRow,
        },

        created() {

        },

        props: [
            'intervalsList',
        ],

        methods: {

        }
    }
</script>
