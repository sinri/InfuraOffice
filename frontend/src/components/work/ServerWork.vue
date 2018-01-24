<template>
    <div>
        <div>
            <Row>
                <i-col span="12"><h2>Server Work</h2></i-col>
            </Row>
            <Row>
                <i-col span="24">
                    <Alert type="error" show-icon v-if="has_error">
                        ERROR
                        <span slot="desc">{{ error_message }}</span>
                    </Alert>
                </i-col>
            </Row>
        </div>
        <h3>Select Servers ...</h3>
        <div>
            <Select v-model="target_server_list" multiple filterable>
                <Option v-for="item in full_server_list" :value="item.key" :key="item.key">{{ item.label }}</Option>
            </Select>
        </div>
        <div>
            <Dropdown @on-click="on_server_group_dropdown_item_click">
                <a href="javascript:void(0)">
                    Use Server Group
                    <Icon type="arrow-down-b"></Icon>
                </a>
                <DropdownMenu slot="list">
                    <Dropdown placement="right-start" v-for="group in full_server_group_list" :key="group.group_name">
                        <DropdownItem>
                            {{group.group_name}}
                            <Icon type="ios-arrow-right"></Icon>
                        </DropdownItem>
                        <DropdownMenu slot="list">
                            <DropdownItem :name="'append-'+group.group_name">Append {{group.group_name}}
                            </DropdownItem>
                            <DropdownItem :name="'remove-'+group.group_name">Remove {{group.group_name}}
                            </DropdownItem>
                        </DropdownMenu>
                    </Dropdown>
                </DropdownMenu>
            </Dropdown>
        </div>
        <div>
            <h3>Select a Task Type ...</h3>
            <Tabs type="card" @on-click="tab_clicked">
                <TabPane label="Disk Usage">
                    <div>
                        <i-button type="primary" v-on:click="click_df_btn">Check Server Disk Usage with df</i-button>
                    </div>
                    <div>
                        <div class="shell_output_box" v-for="df_of_server in df_list">
                            <h4>{{df_of_server.server_name}}</h4>
                            <pre>{{df_of_server.output}}</pre>
                            <Alert type="error" v-if="df_of_server.error">{{df_of_server.error}}</Alert>
                        </div>
                    </div>
                </TabPane>
                <TabPane label="Folder Space">
                    <div>
                        <i-input type="text" v-model="du_dir">
                            <span slot="prepend">Folder Path: </span>
                            <Button slot="append" icon="pie-graph" v-on:click="click_du_btn">du</Button>
                        </i-input>
                    </div>
                    <div>
                        <div class="shell_output_box" v-for="du_of_server in du_list">
                            <h4>{{du_of_server.server_name}} : {{du_of_server.dir}}</h4>
                            <pre>{{du_of_server.output}}</pre>
                            <Alert type="error" v-if="du_of_server.error">{{du_of_server.error}}</Alert>
                        </div>
                    </div>
                </TabPane>
                <TabPane label="File System">
                    <div>
                        <i-input type="text" v-model="ls_dir">
                            <span slot="prepend">Folder Path:</span>
                            <Button slot="append" icon="ios-folder" v-on:click="click_ls_btn">ls</Button>
                        </i-input>
                    </div>
                    <div class="shell_output_box" v-for="ls_of_server in ls_list">
                        <h4>{{ls_of_server.server_name}} : {{ls_of_server.dir}}</h4>
                        <pre>{{ls_of_server.output}}</pre>
                        <Alert type="error" v-if="ls_of_server.error">{{ls_of_server.error}}</Alert>
                    </div>
                </TabPane>
                <TabPane label="Shell Command">
                    <div>
                        <i-input type="text" v-model="shell_command">
                            <span slot="prepend">shell $</span>
                            <i-button slot="append" icon="nuclear" v-on:click="click_shell_command_btn"></i-button>
                        </i-input>
                        <div class="shell_output_box" v-for="command_of_server in shell_command_list">
                            <h4>{{command_of_server.server_name}} : {{command_of_server.shell_command}}</h4>
                            <pre>{{command_of_server.output}}</pre>
                            <Alert type="error" v-if="command_of_server.error">{{command_of_server.error}}</Alert>
                        </div>
                    </div>
                </TabPane>
            </Tabs>
        </div>
    </div>
</template>

<script>
    import {Tools} from '../../assets/js/common';

    export default {
        name: "server-work",
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
                shell_command: '',
                shell_command_list: [],
            }
        },
        methods: {
            load_server_group_list: function () {
                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    '/api/ServerWorkController/serverGroups',
                    {},
                    (data) => {
                        let group_data = data.list;
                        for (let i = 0; i < group_data.length; i++) {
                            group_data[i].server_name_list_readable = group_data[i].server_name_list.join(', ');
                        }
                        this.full_server_group_list = group_data;

                        this.show_edit_group = false;
                    },
                    (error) => {
                        this.$Message.error(error);
                    }
                )
            },
            load_server_list: function () {
                this.$Loading.start();

                Tools.callInfuraOfficeJsonAPI(
                    "get",
                    "/api/ServerWorkController/servers",
                    {},
                    (data) => {
                        let servers = [];
                        for (let i = 0; i < data.list.length; i++) {
                            let server_item = data.list[i];
                            servers.push({
                                key: server_item.server_name,
                                label: server_item.server_name,
                                disabled: false
                            });
                        }
                        this.full_server_list = servers;
                        this.target_server_list = [];
                        this.$Loading.finish();
                    },
                    (error) => {
                        this.has_error = true;
                        this.error_message = error;
                        this.$Loading.error();
                    },
                    () => {
                    }
                );
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

                this.$Loading.start();

                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    "/api/ServerWorkController/checkDF",
                    {
                        server_name_list: this.target_server_list
                    },
                    (data) => {
                        this.df_list = data.df_list;
                        this.$Loading.finish();
                    },
                    (error) => {
                        this.$Notice.error({
                            title: 'Load df Failed',
                            desc: error
                        });
                        this.df_list = [];
                        this.$Loading.error();
                    }
                );
            },
            click_du_btn: function () {
                if (this.target_server_list.length <= 0) {
                    this.$Message.warning({
                        content: "Select one or more servers first!",
                        duration: 2,
                    });
                    return;
                }

                this.$Loading.start();

                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    "/api/ServerWorkController/checkDU",
                    {
                        server_name_list: this.target_server_list,
                        dir: this.du_dir
                    },
                    (data) => {
                        this.du_list = data.du_list;
                        this.$Loading.finish();
                    },
                    (error) => {
                        this.$Notice.error({
                            title: 'Load du Failed',
                            desc: error
                        });
                        this.du_list = [];
                        this.$Loading.error();
                    }
                );
            },
            click_ls_btn: function () {
                if (this.target_server_list.length <= 0) {
                    this.$Message.warning({
                        content: "Select one or more servers first!",
                        duration: 2,
                    });
                    return;
                }

                this.$Loading.start();

                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    "/api/ServerWorkController/checkLS",
                    {
                        server_name_list: this.target_server_list,
                        dir: this.ls_dir
                    },
                    (data) => {
                        this.ls_list = data.ls_list;
                        this.$Loading.finish();
                    },
                    (error) => {
                        this.$Notice.error({
                            title: 'Load ls Failed',
                            desc: error
                        });
                        this.ls_list = [];
                        this.$Loading.error();
                    }
                );
            },
            click_shell_command_btn: function () {
                if (this.target_server_list.length <= 0) {
                    this.$Message.warning({
                        content: "Select one or more servers first!",
                        duration: 2,
                    });
                    return;
                }

                this.$Loading.start();

                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    "/api/ServerWorkController/runShellCommand",
                    {
                        server_name_list: this.target_server_list,
                        command: this.shell_command
                    },
                    (data) => {
                        this.shell_command_list = data.result_list;
                        this.$Loading.finish();
                    },
                    (error) => {
                        this.$Notice.error({
                            title: 'Load shell Failed',
                            desc: error
                        });
                        this.ls_list = [];
                        this.$Loading.error();
                    }
                );
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
            },
        },
        mounted: function () {
            //console.log(handlerOfIndexComponentServerWork.componentDefinition.template);
            this.load_server_group_list();
            this.load_server_list();
        }
    }
</script>

<style scoped>
    h2 {
        margin: 10px 0;
    }

    h3 {
        margin: 5px 0;
    }

    h4 {
        margin: 5px 0;
    }

    div.ivu-tabs-tabpane > div {
        margin: 5px;
    }

    div.shell_output_box {
        margin: 10px;
        padding: 10px;
        border: 1px solid lightblue;
        overflow: scroll;
    }
</style>