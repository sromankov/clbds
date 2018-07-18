<template>
    <div class="container">
        <div class="">
            <h3>Intervals</h3>

            <intervals-list :intervalsList="list"></intervals-list>

            <div class="page-header">
                <interval-form :formData="requestData"
                                @formWasSubmitted="sendRequest()"
                                :requestWasExecuted="requestWasExecuted"></interval-form>
            </div>

            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal"
                    @click.prevent="openNewIntervalForm">Add new interval</button>
        </div>
    </div>
</template>

<script>

    import moment from 'moment';
    import IntervalsList from './IntervalsList.vue';
    import IntervalForm from './IntervalForm.vue';

    import {eventBus} from '../../app';

    export default {

        components: {
            intervalsList: IntervalsList,
            intervalForm: IntervalForm,
        },
        data(){
            return {
                requestData : {},
                list: null,
                requestWasExecuted: false
            }
        },
        created() {

            this.clearRequest();

            var vm = this;

            eventBus.$on('intervalDeleteClicked', function(id){
                vm.deleteInterval(id).then(
                    response =>  {
                        vm.getList();
                    },
                    error => {
                        // TODO errors notifications
                        alert(`Rejected: ${error}`)
                    }
                )
            });

            eventBus.$on('intervalEditClicked', function(id){

                vm.getInterval(id).then(
                    response =>  {
                        vm.requestData = response.range;
                        $('#myModal').modal('show');
                    },
                    error => {
                        alert(`Rejected: ${error}`)
                    }
                );
            });

            eventBus.$on('formWasSubmitted', function(formData){

                vm.saveInterval(formData).then(
                    response =>  {
                        $('#myModal').modal('hide');
                        vm.getList();
                    },
                    error => {
                        // TODO errors notifications
                        alert(`Rejected: ${error}`)
                    }
                );
            });

            this.getList();
        },
        methods: {

            getList()
            {
                axios.get('/api/list', this.requestData)
                    .then( res => {

                        if (res.data.status) {
                            this.list = res.data.data.ranges;

                        } else {
                            this.list = [];
                        }

                    })
                    .catch(error =>   {
                        console.log(error);
                        this.requestWasExecuted = true;
                        this.list = [];
                        this.meta = null;

                        console.log(error.response);
                    });
            },

            getInterval(id)
            {

                var vm = this;

                return new Promise(function(resolve, reject) {

                    axios.get('/api/read/'+id, vm.requestData)
                        .then( res => {
                            if (res.data.status) {
                                resolve(res.data.data);
                            } else {
                                reject(new Error('Wrong status'));
                            }
                        })
                        .catch(error =>   {
                            reject(error);
                        });
                });
            },

            saveInterval(data)
            {
                var vm = this;

                return new Promise(function(resolve, reject) {

                    var url = '/api/create';

                    if (!(data.id === null)) {
                        url = '/api/update/' + data.id;
                    }

                    axios.post(url, data)
                        .then( res => {

                            if (res.data.status) {
                                resolve(res.data.data);
                            } else {
                                reject(new Error('Wrong status'));
                            }
                        })
                        .catch(error =>   {
                            reject(error);
                        });
                });
            },

            deleteInterval(id) {

                var vm = this;

                return new Promise(function(resolve, reject) {

                    axios.get('/api/delete/'+id, vm.requestData)
                        .then( res => {
                            if (res.data.status) {
                                resolve(res.data.data);
                            } else {
                                reject(new Error('Wrong status'));
                            }
                        })
                        .catch(error =>   {
                            reject(error);
                        });
                });
            },

            openNewIntervalForm()
            {
               this.clearRequest();
            },

            clearRequest()
            {
                this.requestData = {
                    id: null,
                    date_start : moment().format('YYYY-MM-DD'),
                    date_end : moment().format('YYYY-MM-DD'),
                    price : 0,
                    mon: 0,
                    tue: 0,
                    wed: 0,
                    thu: 0,
                    fri: 0,
                    sat: 0,
                    sun: 0,
                };
            }
        }
    }
</script>
