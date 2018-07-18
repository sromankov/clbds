<template>
        <tr>
            <td><strong>{{ intervalData.date_start }}</strong> - <strong>{{ intervalData.date_end }}</strong></td>
            <td><strong>{{ intervalData.price }}</strong></td>
            <td><input type="checkbox" id="mon-state" v-model="intervalData.mon==1" disabled></td>
            <td><input type="checkbox" id="tue-state" v-model="intervalData.tue==1" disabled></td>
            <td><input type="checkbox" id="wed-state" v-model="intervalData.wed==1" disabled></td>
            <td><input type="checkbox" id="thu-state" v-model="intervalData.thu==1" disabled></td>
            <td><input type="checkbox" id="fri-state" v-model="intervalData.fri==1" disabled></td>
            <td><input type="checkbox" id="sat-state" v-model="intervalData.sat==1" disabled></td>
            <td><input type="checkbox" id="sun-state" v-model="intervalData.sun==1" disabled></td>
            <td>
                <button class="btn btn-default btn-sm" @click="clickEdit(intervalData.id)">Edit</button>
                <button class="btn btn-danger btn-sm" @click="clickDelete(intervalData.id)">Delete</button>
            </td>
        </tr>
</template>

<script>
    import {eventBus} from '../../app';

    export default {

        props: [
            'intervalData'
        ],
        methods: {
            clickDelete(id) {
                eventBus.$emit('intervalDeleteClicked', id);
            },
            clickEdit(id) {
                eventBus.$emit('intervalEditClicked', id)
            },
        }
    }
</script>
