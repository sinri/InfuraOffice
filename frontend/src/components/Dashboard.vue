<template>
    <section>
        <div>
            <h1>Welcome to Infura Office!</h1>
            <div>
                <Carousel v-model="carousel_index" loop v-if="stat_loaded">
                    <CarouselItem>
                        <div class="dashboard_carouse_item"><span
                                class="dashboard_carouse_item_bigger">{{server_count}}</span> Servers
                        </div>
                    </CarouselItem>
                    <CarouselItem>
                        <div class="dashboard_carouse_item"><span class="dashboard_carouse_item_bigger">{{server_group_count}}</span>
                            Server Groups
                        </div>
                    </CarouselItem>
                    <CarouselItem>
                        <div class="dashboard_carouse_item"><span class="dashboard_carouse_item_bigger">{{database_count}}</span>
                            Databases
                        </div>
                    </CarouselItem>
                    <CarouselItem>
                        <div class="dashboard_carouse_item"><span
                                class="dashboard_carouse_item_bigger">{{job_count}}</span> Jobs
                        </div>
                    </CarouselItem>
                </Carousel>
            </div>
        </div>
    </section>
</template>

<script>
    import {Tools} from '../assets/js/common';

    export default {
        name: "dashboard",
        data: function () {
            return {
                stat_loaded: false,
                server_group_count: '?',
                server_count: '?',
                database_count: '?',
                job_count: '?',
                carousel_index: 0,
            }
        },
        methods: {
            load_stat_data: function () {
                Tools.callInfuraOfficeJsonAPI(
                    'post',
                    '/api/DashboardController/stat',
                    {
                        InfuraOfficeToken: Tools.CookieHelper.getToken()
                    },
                    (data) => {
                        this.server_group_count = data.server_group_count;
                        this.server_count = data.server_count;
                        this.database_count = data.database_count;
                        this.job_count = data.job_count;

                        this.stat_loaded = true;
                    },
                    (error) => {
                        this.$Message.error("Failed to load stat data. " + error);
                    },
                    () => {

                    }
                );
                // $.ajax({
                //     url: '../api/DashboardController/stat',
                //     method: 'get',
                //     dataType: 'json'
                // }).done((response) => {
                //     if (response.code !== 'OK') {
                //         this.$Message.error("Failed to load stat data. " + response.data);
                //         return;
                //     }
                //     this.server_group_count = response.data.server_group_count;
                //     this.server_count = response.data.server_count;
                //     this.database_count = response.data.database_count;
                //     this.job_count = response.data.job_count;
                //
                //     this.stat_loaded = true;
                // }).fail(() => {
                //     this.$Message.error('Ajax Failed');
                // })
            }
        },
        mounted: function () {
            this.load_stat_data();
        }
    }
</script>

<style scoped>
    div.dashboard_carouse_item {
        height: 200px;
        line-height: 200px;
        text-align: center;
        color: #fff;
        font-size: 25px;
        background: #506b9e;
    }

    span.dashboard_carouse_item_bigger {
        font-size: 40px;
    }
</style>