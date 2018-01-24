<template>
    <div style="display: inline-block;width: 100%;margin:5px;">
        <Row type="flex" justify="center" align="middle">
            <i-col span="2" style="text-align: center">Servers:</i-col>
            <i-col span="10">
                <i-select v-model="target_servers" filterable multiple>
                    <Option v-for="item in server_list" :value="item.key" :key="item.key">{{ item.label }}</Option>
                </i-select>
            </i-col>
            <i-col span="2" style="text-align: center">Groups:</i-col>
            <i-col span="10">
                <i-select v-model="target_server_groups" filterable multiple>
                    <Option v-for="item in server_group_list" :value="item.key" :key="item.key">{{ item.label }}
                    </Option>
                </i-select>
            </i-col>
        </Row>
    </div>
</template>

<script>
    import {Tools} from '../../assets/js/common';

    export default {
        name: "infura-server-and-group-select",
        props: ['servers', 'server_groups'],
        data: function () {
            return {
                target_servers: [],
                server_list: [],
                target_server_groups: [],
                server_group_list: [],
            }
        },
        watch: {
            servers: function (val) {
                this.target_servers = val;
            },
            server_groups: function (val) {
                this.target_server_groups = val;
            },
            target_servers: function (val) {
                this.$emit('change', this.target_servers, this.target_server_groups);
            },
            target_server_groups: function (val) {
                this.$emit('change', this.target_servers, this.target_server_groups);
            }
        },
        methods: {
            load_server_group_list: function () {
                //vueIndex.$Loading.start();
                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    '/api/ServerWorkController/serverGroups',
                    {},
                    (data) => {
                        let group_data = [];
                        for (let i = 0; i < data.list.length; i++) {
                            let server_item = data.list[i];
                            group_data.push({
                                key: server_item.group_name,
                                label: server_item.group_name,
                                disabled: false
                            });
                        }
                        this.server_group_list = group_data;
                    },
                    (error) => {
                        this.$Message.error(error);
                    }
                );
            },
            load_server_list: function () {
                Tools.callInfuraOfficeJsonAPI(
                    "post",
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
                        this.server_list = servers;
                    },
                    (error) => {
                        this.$Message.error("infura_server_select ajax failed: " + error);
                    }
                );
            },
        },
        mounted: function () {
            this.load_server_group_list();
            this.load_server_list();
            this.target_servers = this.servers;
            this.target_server_groups = this.server_groups;
        },
    }
</script>

<style scoped>

</style>