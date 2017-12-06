const handlerOfIndexComponentServerWork = {
    componentDefinition: {
        template: '<div>' +
        '<div>' +
        '<Row>' +
        '<i-col span="12"><h2>Server Work</h2></i-col>' +
        '</Row>' +
        '<Row>' +
        '<i-col span="24">' +
        '<Alert type="error" show-icon v-if="has_error">' +
        'ERROR' +
        '<span slot="desc">{{ error_message }}</span>' +
        '</Alert>' +
        '</i-col>' +
        '</Row>' +
        '</div>' +
        '<h3>Select Servers ...</h3>' +
        '<div>' +
        '<Select v-model="target_server_list" multiple style="width:260px">' +
        '<Option v-for="item in full_server_list" :value="item.key" :key="item.key">{{ item.label }}</Option>' +
        '</Select>' +
        '</div>' +
        '<div>' +
        '<Dropdown @on-click="on_server_group_dropdown_item_click">' +
        '   <a href="javascript:void(0)">' +
        '       Use Server Group ' +
        '       <Icon type="arrow-down-b"></Icon>' +
        '   </a>' +
        '   <DropdownMenu slot="list">' +
        '       <Dropdown placement="right-start" v-for="group in full_server_group_list" :key="group.group_name">' +
        '           <DropdownItem>' +
        '               {{group.group_name}} ' +
        '               <Icon type="ios-arrow-right"></Icon>' +
        '           </DropdownItem>' +
        '           <DropdownMenu slot="list">' +
        '               <DropdownItem :name="\'append-\'+group.group_name">Append {{group.group_name}}</DropdownItem>' +
        '               <DropdownItem :name="\'remove-\'+group.group_name">Remove {{group.group_name}}</DropdownItem>' +
        '           </DropdownMenu>' +
        '       </Dropdown>' +
        '   </DropdownMenu>' +
        '</Dropdown>' +
        '</div>' +
        '<div>' +
        '<h3>Select a Task Type ...</h3>' +
        '<Tabs type="card" @on-click="tab_clicked">' +
        '<TabPane label="Disk Usage">' +
        '<div>' +
        '<Button type="primary" v-on:click="click_df_btn">Check Server Disk Usage with df</Button>' +
        '</div>' +
        '<div>' +
        '<div class="shell_output_box" v-for="df_of_server in df_list">' +
        '<h4>{{df_of_server.server_name}}</h4>' +
        '<pre>{{df_of_server.output}}</pre>' +
        '<Alert type="error" v-if="df_of_server.error">{{df_of_server.error}}</Alert>' +
        '</div>' +
        '</div>' +
        '</TabPane>' +
        '<TabPane label="Folder Space">' +
        '<div>' +
        '<Input type="text" v-model="du_dir" >' +
        '<span slot="prepend">Folder Path: </span>' +
        '<Button slot="append" icon="pie-graph" v-on:click="click_du_btn">du</Button>' +
        '</Input>' +
        '</div>' +
        '<div>' +
        '<div class="shell_output_box" v-for="du_of_server in du_list">' +
        '<h4>{{du_of_server.server_name}}:{{du_of_server.dir}}</h4>' +
        '<pre>{{du_of_server.output}}</pre>' +
        '<Alert type="error" v-if="du_of_server.error">{{du_of_server.error}}</Alert>' +
        '</div>' +
        '</div>' +
        '</TabPane>' +
        '<TabPane label="File System">' +
        '<div>' +
        '<Input type="text" v-model="ls_dir">' +
        '<span slot="prepend">Folder Path:</span>' +
        '<Button slot="append" icon="ios-folder" v-on:click="click_ls_btn">ls</Button>' +
        '</Input>' +
        '</div>' +
        '<div class="shell_output_box" v-for="ls_of_server in ls_list">' +
        '<h4>{{ls_of_server.server_name}}:{{ls_of_server.dir}}</h4>' +
        '<pre>{{ls_of_server.output}}</pre>' +
        '<Alert type="error" v-if="ls_of_server.error">{{ls_of_server.error}}</Alert>' +
        '</div>' +
        '</TabPane>' +
        '</Tabs>' +
        '</div>' +
        '</div>',
        data: function () {
            return {
                full_server_group_list: [],
                full_server_list: [],
                target_server_list: [],
                has_error: false,
                error_message: '',
                //
                df_list: [],
                du_dir: '/',
                du_list: [],
                ls_dir: '/',
                ls_list: [],
            }
        },
        methods: {
            load_server_group_list: function () {
                //vueIndex.$Loading.start();
                $.ajax({
                    url: '../api/ServerWorkController/serverGroups',
                    method: 'post',
                    dataType: 'json'
                }).done((response) => {
                    if (response.code === 'OK') {
                        //vueIndex.$Loading.finish();

                        let group_data = response.data.list;
                        for (let i = 0; i < group_data.length; i++) {
                            //group_data[i].key=group_data[i].group_name;
                            group_data[i].server_name_list_readable = group_data[i].server_name_list.join(', ');
                        }
                        this.full_server_group_list = group_data;

                        this.show_edit_group = false;
                    } else {
                        this.$Message.error(response.data);
                        //vueIndex.$Loading.error();
                    }
                }).fail(() => {
                    this.$Message.error('ajax failed for server group loading');
                    //vueIndex.$Loading.error();
                });
            },
            load_server_list: function () {
                vueIndex.$Loading.start();

                $.ajax({
                    url: '../api/ServerWorkController/servers',
                    method: 'get',
                    dataType: 'json'
                }).done((response) => {
                    console.log(response);

                    let servers = [];

                    if (response.code !== 'OK') {
                        // vueIndex.$Notice.error({
                        //     title: 'Load Failed',
                        //     desc: response.data
                        // });

                        this.has_error = true;
                        this.error_message = response.data;
                    } else {
                        for (let i = 0; i < response.data.list.length; i++) {
                            let server_item = response.data.list[i];
                            servers.push({
                                key: server_item.server_name,
                                label: server_item.server_name,
                                disabled: false
                            });
                        }
                        this.full_server_list = servers;
                        this.target_server_list = [];
                    }
                    vueIndex.$Loading.finish();
                }).fail(() => {
                    vueIndex.$Loading.error();
                    this.has_error = true;
                    this.error_message = "Ajax Failed";
                }).always(() => {
                    //console.log("guhehe");
                });
            },
            server_transfer_changed: function (newTargetKeys) {
                this.target_server_list = newTargetKeys;
            },
            server_filter: function (data, query) {
                return data.label.indexOf(query) > -1;
            },
            tab_clicked: function () {
                console.log('tab clicked', arguments);
            },
            click_df_btn: function () {
                if (this.target_server_list.length <= 0) {
                    this.$Message.warning({
                        content: "Select one or more servers first!",
                        duration: 2,
                    });
                    return;
                }


                vueIndex.$Loading.start();

                $.ajax({
                    url: '../api/ServerWorkController/checkDF',
                    method: 'post',
                    data: {
                        server_name_list: this.target_server_list
                    },
                    dataType: 'json'
                }).done((response) => {
                    console.log(response);
                    if (response.code !== 'OK') {
                        vueIndex.$Notice.error({
                            title: 'Load df Failed',
                            desc: response.data
                        });
                        this.df_list = [];
                        vueIndex.$Loading.error();
                    } else {
                        this.df_list = response.data.df_list;
                        vueIndex.$Loading.finish();
                    }
                }).fail(() => {
                    vueIndex.$Loading.error();
                    vueIndex.$Notice.error({
                        title: 'Ajax Failed',
                        desc: 'when calling df'
                    });
                });
            },
            click_du_btn: function () {
                if (this.target_server_list.length <= 0) {
                    this.$Message.warning({
                        content: "Select one or more servers first!",
                        duration: 2,
                    });
                    return;
                }

                vueIndex.$Loading.start();

                $.ajax({
                    url: '../api/ServerWorkController/checkDU',
                    method: 'post',
                    data: {
                        server_name_list: this.target_server_list,
                        dir: this.du_dir
                    },
                    dataType: 'json'
                }).done((response) => {
                    console.log(response);
                    if (response.code !== 'OK') {
                        vueIndex.$Notice.error({
                            title: 'Load df Failed',
                            desc: response.data
                        });
                        this.du_list = [];
                        vueIndex.$Loading.error();
                    } else {
                        this.du_list = response.data.du_list;
                        vueIndex.$Loading.finish();
                    }
                }).fail(() => {
                    vueIndex.$Loading.error();
                    vueIndex.$Notice.error({
                        title: 'Ajax Failed',
                        desc: 'when calling df'
                    });
                });
            },
            click_ls_btn: function () {
                if (this.target_server_list.length <= 0) {
                    this.$Message.warning({
                        content: "Select one or more servers first!",
                        duration: 2,
                    });
                    return;
                }

                vueIndex.$Loading.start();

                $.ajax({
                    url: '../api/ServerWorkController/checkLS',
                    method: 'post',
                    data: {
                        server_name_list: this.target_server_list,
                        dir: this.ls_dir
                    },
                    dataType: 'json'
                }).done((response) => {
                    console.log(response);
                    if (response.code !== 'OK') {
                        vueIndex.$Notice.error({
                            title: 'Load df Failed',
                            desc: response.data
                        });
                        this.ls_list = [];
                        vueIndex.$Loading.error();
                    } else {
                        this.ls_list = response.data.ls_list;
                        vueIndex.$Loading.finish();
                    }
                }).fail(() => {
                    vueIndex.$Loading.error();
                    vueIndex.$Notice.error({
                        title: 'Ajax Failed',
                        desc: 'when calling df'
                    });
                });
            },
            on_server_group_dropdown_item_click: function (action_group_name) {
                console.log('on_server_group_dropdown_item_click', action_group_name);
                let group_name = action_group_name.substring(7);
                let servers = [];
                for (let i = 0; i < this.full_server_group_list.length; i++) {
                    if (this.full_server_group_list[i].group_name === group_name) {
                        servers = this.full_server_group_list[i].server_name_list;
                        break;
                    }
                }
                console.log('found servers in group ' + group_name, servers);
                if (servers.length <= 0) {
                    return;
                }
                let action = '';
                if (action_group_name.startsWith("append-")) {
                    //append
                    for (let i = 0; i < servers.length; i++) {
                        if (this.target_server_list.indexOf(servers[i]) < 0) {
                            this.target_server_list.push(servers[i]);
                            console.log("pushed", servers[i]);
                        }
                    }
                } else if (action_group_name.startsWith("remove-")) {
                    //remove
                    for (let i = servers.length - 1; i >= 0; i--) {
                        if (this.target_server_list.indexOf(servers[i]) >= 0) {
                            this.target_server_list.splice(i, 1);
                            console.log("removed", servers[i]);
                        }
                    }
                } else {
                    //do nothing
                }
            }
        },
        mounted: function () {
            //console.log(handlerOfIndexComponentServerWork.componentDefinition.template);
            this.load_server_group_list();
            this.load_server_list();
        }
    }
};