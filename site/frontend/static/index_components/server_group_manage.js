const handlerOfIndexComponentServerGroupManage = {
    componentDefinition: {
        template: '<div>' +
        '<Row> ' +
        '<i-col span="12"> ' +
        '<h2>Server Group Manage</h2> ' +
        '</i-col> ' +
        '<i-col span="12"> ' +
        '<i-button class="right" icon="android-add" v-on:click="on_add_btn">Add Server Group</i-button> ' +
        '</i-col> ' +
        '</Row> ' +
        '<Row>' +
        '<i-col span="24">' +
        '<Alert type="error" show-icon v-if="has_error">' +
        'ERROR' +
        '<span slot="desc">{{ error_message }}</span>' +
        '</Alert>' +
        '</i-col>' +
        '</Row>' +
        '<i-table :columns="group_fields" :data="group_data"></i-table>' +
        '<Modal v-model="show_edit_group" title="Update Server Group" @on-ok="modal_edit_server_group" @on-cancel="modal_close" :loading="modal_loading">' +
        '<i-input style="margin: 5px auto" v-model="modal_data.group_name"><span slot="prepend">Group Name</span></i-input>' +
        '<Transfer :data="all_servers" :target-keys="modal_data.server_name_list" filterable :filter-method="filter_server_name" @on-change="modal_servers_changed"></Transfer>' +
        '</Modal>' +
        '</div>',
        data: function () {
            return {
                has_error: false,
                error_message: '',
                group_fields: [
                    {
                        key: 'group_name', title: 'Group Name', sortable: true
                    },
                    {
                        key: 'server_name_list_readable', title: 'Server List', sortable: true
                    },
                    {
                        key: 'action', title: 'Action',
                        render: (h, params) => {
                            return h('div', [
                                h('Button', {
                                    props: {
                                        type: 'primary',
                                        size: 'small'
                                    },
                                    style: {
                                        //marginRight: '5px'
                                        margin: '5px'
                                    },
                                    on: {
                                        click: () => {
                                            //this.show(params.index)
                                            this.on_edit_btn(params.row);
                                        }
                                    }
                                }, 'Edit'),
                                h('Button', {
                                    props: {
                                        type: 'error',
                                        size: 'small'
                                    },
                                    style: {
                                        //marginRight: '5px'
                                        margin: '5px'
                                    },
                                    on: {
                                        click: () => {
                                            //this.remove(params.index)
                                            console.log("click remove", params);
                                            this.on_remove_btn(params.row.group_name);
                                        }
                                    }
                                }, 'Delete')
                            ]);
                        }
                    }
                ],
                group_data: [],
                show_edit_group: false,
                modal_loading: true,
                modal_data: {
                    group_name: '',
                    server_name_list: [],
                },
                all_servers: [],
            };
        },
        methods: {
            load_server_list: function () {
                //vueIndex.$Loading.start();

                $.ajax({
                    url: '../api/ServerWorkController/servers',
                    method: 'get',
                    dataType: 'json'
                }).done((response) => {
                    console.log(response);

                    let servers = [];

                    if (response.code !== 'OK') {
                        vueIndex.$Notice.error({
                            title: 'Load Server List Failed',
                            desc: response.data
                        });

                        //this.has_error = true;
                        //this.error_message = response.data;
                    } else {
                        for (let i = 0; i < response.data.list.length; i++) {
                            let server_item = response.data.list[i];
                            servers.push({
                                key: server_item.server_name,
                                label: server_item.server_name,
                                disabled: false
                            });
                        }
                        this.all_servers = servers;
                        this.target_server_list = [];
                    }
                    //vueIndex.$Loading.finish();
                }).fail(() => {
                    //vueIndex.$Loading.error();
                    //this.has_error = true;
                    //this.error_message = "Ajax Failed";
                    vueIndex.$Notice.error({
                        title: 'Load Server List Failed',
                        desc: 'Ajax Failed'
                    });
                }).always(() => {
                    //console.log("guhehe");
                });
            },
            refresh_server_group_list: function () {
                vueIndex.$Loading.start();
                $.ajax({
                    url: '../api/ServerWorkController/serverGroups',
                    method: 'post',
                    dataType: 'json'
                }).done((response) => {
                    if (response.code === 'OK') {
                        vueIndex.$Loading.finish();

                        let group_data = response.data.list;
                        for (let i = 0; i < group_data.length; i++) {
                            group_data[i].server_name_list_readable = group_data[i].server_name_list.join(', ');
                        }
                        this.group_data = group_data;

                        this.show_edit_group = false;
                    } else {
                        this.$Message.error(response.data);
                        vueIndex.$Loading.error();
                    }
                }).fail(() => {
                    this.$Message.error('ajax failed');
                    vueIndex.$Loading.error();
                });
            },
            on_add_btn: function () {
                this.modal_data = {
                    group_name: '',
                    server_name_list: [],
                };
                this.show_edit_group = true;
            },
            on_edit_btn: function (group) {
                this.show_edit_group = true;

                let set2 = new Set();
                for (let key in this.all_servers) {
                    if (!this.all_servers.hasOwnProperty(key)) continue;
                    set2.add(this.all_servers[key].key);
                }
                let intersection = new Set(group.server_name_list.filter(x => set2.has(x)));
                console.log("intersection", intersection);
                this.modal_data = {
                    group_name: group.group_name,
                    server_name_list: [...intersection],
                };
                console.log("on_edit_btn", group, this.modal_data);
            },
            on_remove_btn: function (group_name) {
                // API
                vueIndex.$Loading.start();
                this.modal_loading = true;
                $.ajax({
                    url: '../api/ServerManageController/removeServerGroup',
                    method: 'post',
                    data: {
                        group_name: group_name
                    },
                    dataType: 'json'
                }).done((response) => {
                    if (response.code === 'OK') {
                        vueIndex.$Loading.finish();
                        this.refresh_server_group_list();
                        this.show_edit_group = false;
                    } else {
                        this.$Message.error(response.data);
                        vueIndex.$Loading.error();
                        this.modal_loading = false;
                    }
                }).fail(() => {
                    this.$Message.error('ajax failed');
                    vueIndex.$Loading.error();
                    this.modal_loading = false;
                });
            },
            modal_edit_server_group: function () {
                // API
                vueIndex.$Loading.start();
                this.modal_loading = true;
                $.ajax({
                    url: '../api/ServerManageController/updateServerGroup',
                    method: 'post',
                    data: {
                        group_name: this.modal_data.group_name,
                        server_name_list: this.modal_data.server_name_list,
                    },
                    dataType: 'json'
                }).done((response) => {
                    if (response.code === 'OK') {
                        vueIndex.$Loading.finish();
                        this.refresh_server_group_list();
                        this.show_edit_group = false;
                    } else {
                        this.$Message.error(response.data);
                        vueIndex.$Loading.error();
                        this.modal_loading = false;
                    }
                }).fail(() => {
                    this.$Message.error('ajax failed');
                    vueIndex.$Loading.error();
                    this.modal_loading = false;
                });
            },
            modal_close: function () {
                this.show_edit_group = false;
            },
            filter_server_name(data, query) {
                return data.label.indexOf(query) > -1;
            },
            modal_servers_changed: function (newTargetKeys) {
                this.modal_data.server_name_list = newTargetKeys;
            },
        },
        mounted: function () {
            this.refresh_server_group_list();
            this.load_server_list();
        }
    }
}