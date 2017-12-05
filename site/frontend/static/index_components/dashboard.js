const handlerOfIndexComponentDashboard = {
    componentDefinition: {
        template: '<div>\
            <h1>Welcome to Infura Office!</h1>\
            <div>\
                <Carousel loop v-if="stat_loaded">\
                    <CarouselItem><div class="dashboard_carouse_item"><span class="dashboard_carouse_item_bigger">{{server_count}}</span> Servers</div></CarouselItem>\
                    <CarouselItem><div class="dashboard_carouse_item"><span class="dashboard_carouse_item_bigger">{{server_group_count}}</span> Server Groups</div></CarouselItem>\
                    <CarouselItem><div class="dashboard_carouse_item"><span class="dashboard_carouse_item_bigger">{{database_count}}</span> Databases</div></CarouselItem>\
                    <CarouselItem><div class="dashboard_carouse_item"><span class="dashboard_carouse_item_bigger">{{job_count}}</span> Jobs</div></CarouselItem>\
                </Carousel>\
            </div>\
        </div>',
        data: function () {
            return {
                stat_loaded: false,
                server_group_count: '?',
                server_count: '?',
                database_count: '?',
                job_count: '?',
            }
        },
        methods: {
            load_stat_data: function () {
                $.ajax({
                    url: '../api/DashboardController/stat',
                    method: 'get',
                    dataType: 'json'
                }).done((response) => {
                    if (response.code !== 'OK') {
                        this.$Message.error("Failed to load stat data. " + response.data);
                        return;
                    }
                    this.server_group_count = response.data.server_group_count;
                    this.server_count = response.data.server_count;
                    this.database_count = response.data.database_count;
                    this.job_count = response.data.job_count;

                    this.stat_loaded = true;
                }).fail(() => {
                    this.$Message.error('Ajax Failed');
                })
            }
        },
        mounted: function () {
            this.load_stat_data();
        }
    }
};