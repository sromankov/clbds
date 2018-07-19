<template>
    <div class="">
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form>

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">
                                <template v-if="submitAsNew">Create interval</template>
                                <template v-else>Edit interval</template>
                            </h4>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                    <div class="input-daterange input-group" id="datepicker">

                                        <span class="input-group-addon">Date range</span>
                                        <input class="input-group-lg form-control" type="text" name="daterange" value="" />

                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                    <div class="input-daterange input-group" :class="{invalid: $v.formData.price.$invalid}">

                                        <span class="input-group-addon">Price</span>
                                        <input class="input-group-lg form-control" name="price" type="number"
                                               @input="$v.formData.price.$touch()"
                                               v-model="formData.price" />

                                    </div>
                                </div>
                            </div>
                            <div class="row" >
                                <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                    <input type="checkbox" id="mon" v-model="formData.mon" v-bind:true-value="1" v-bind:false-value="0">
                                    <label for="mon">Mon</label>
                                    <input type="checkbox" id="tue" v-model="formData.tue" v-bind:true-value="1" v-bind:false-value="0">
                                    <label for="tue">Tue</label>
                                    <input type="checkbox" id="wed" v-model="formData.wed" v-bind:true-value="1" v-bind:false-value="0">
                                    <label for="wed">Wed</label>
                                    <input type="checkbox" id="thu" v-model="formData.thu" v-bind:true-value="1" v-bind:false-value="0">
                                    <label for="thu">Thu</label>
                                    <input type="checkbox" id="fri" v-model="formData.fri" v-bind:true-value="1" v-bind:false-value="0">
                                    <label for="fri">Fri</label>
                                    <input type="checkbox" id="sat" v-model="formData.sat" v-bind:true-value="1" v-bind:false-value="0">
                                    <label for="sat">Sat</label>
                                    <input type="checkbox" id="sun" v-model="formData.sun" v-bind:true-value="1" v-bind:false-value="0">
                                    <label for="sun">Sun</label>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button :disabled="disableControls || $v.$error" class="btn btn-primary" @click.prevent="submitForm">Save</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped="">
    .input-group.invalid input {
        border: 1px solid red;
    }
    .input-group.invalid span {
        color: red;
    }
</style>

<script>

    import moment from 'moment';
    import {eventBus} from '../../app';
    import { required, minValue, between, numeric, decimal } from 'vuelidate/lib/validators';

    export default {
        props: [
            'formData',
            'requestWasExecuted'
        ],

        data(){
            return {
                disableControls: false,
                rangeText: '',
                submitAsNew: false,
                outvar : ''
            }
        },

        validations: {

            formData: {
                price : {
                    required,
                    decimal,
//                    minValue: minValue(0.01)
                }
            },
        },

        created() {

            var vm = this;

            $(function() {
                $('input[name="daterange"]').daterangepicker({
                    opens: 'left'
                }, function(start, end, label) {
                    eventBus.$emit('rangeWasChanged', start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'))
                });

            });

            eventBus.$on('rangeWasChanged', function(start, end){
                vm.formData.date_start = start;
                vm.formData.date_end = end;

            });
        },

        watch : {
            requestWasExecuted() {
                if (this.requestWasExecuted === false) {
                    this.disableControls = true;
                } else {
                    this.disableControls = false;
                }
            },
            formData() {

                if (this.formData.date_start != '' && this.formData.date_end != '') {

                    var vm = this

                    this.rangeText = moment(this.formData.date_start, "YYYY-MM-DD").format("MM/DD/YYYY")
                        + ' - ' + moment(this.formData.date_end, "YYYY-MM-DD").format("MM/DD/YYYY");

                    $(function() {
                        $('input[name="daterange"]').data('daterangepicker').setStartDate(moment(vm.formData.date_start, "YYYY-MM-DD").format("MM/DD/YYYY"));
                        $('input[name="daterange"]').data('daterangepicker').setEndDate(moment(vm.formData.date_end, "YYYY-MM-DD").format("MM/DD/YYYY"));
                    });

                } else {
                    this.rangeText = '';
                }

                this.submitAsNew = (this.formData.id == null);
            }
        },

        methods: {
            submitForm()
            {
                eventBus.$emit('formWasSubmitted', this.formData)
            }
        }
    }
</script>
