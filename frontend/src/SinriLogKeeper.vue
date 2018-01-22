<template>
    <section>
        <Row type="flex" justify="center" align="middle">
            <i-col span="20">
                <Row type="flex" justify="center" align="middle" style="border-bottom: 1px solid lightgrey">
                    <i-col span="20">
                        <h1>SinriLogKeeper
                            <small>Embedded Version in InfuraOffice</small>
                        </h1>
                    </i-col>
                    <i-col span="4">
                        <i-button style="display: inline-block;float: right" icon="log-out" shape="circle" size="small"
                                  v-on:click="logout">Logout
                        </i-button>
                    </i-col>
                </Row>
                <Row type="flex" justify="space-between" align="middle">
                    <i-col span="1"><span>Server:</span></i-col>
                    <i-col span="4">
                        <i-select v-model="target_server" @on-change="on_server_changed" transfer filterable>
                            <i-option v-for="item in server_list" :value="item.key">{{item.label}}</i-option>
                        </i-select>
                    </i-col>
                    <i-col span="1"><span>File:</span></i-col>
                    <i-col span="16">
                        <i-select v-model="target_file" :loading="file_select_loading" transfer filterable>
                            <i-option v-for="item in file_list" :value="item.key">{{item.label}}</i-option>
                        </i-select>
                    </i-col>
                </Row>
                <Row type="flex" justify="space-between" align="middle">
                    <i-col span="5">
                        <i-input v-model="range_start">
                            <span slot="prepend">From</span>
                        </i-input>
                    </i-col>
                    <i-col span="5">
                        <i-input v-model="range_end">
                            <span slot="prepend">To</span>
                        </i-input>
                    </i-col>
                    <i-col span="5">
                        <i-input v-model="around_lines">
                            <span slot="prepend">Around Lines</span>
                        </i-input>
                    </i-col>
                    <i-col span="5">
                        <Checkbox v-model="is_case_sensitive">Case Sensitive</Checkbox>
                    </i-col>
                </Row>
                <Row type="flex" justify="space-between" align="middle">
                    <i-col span="16">
                        <i-input type="text" v-model="keyword">
                            <span slot="prepend">Keyword</span>
                        </i-input>
                    </i-col>
                    <i-col span="4">
                        <i-button v-on:click="on_slk_search" type="primary" long>Search</i-button>
                    </i-col>
                </Row>
            </i-col>
        </Row>
        <Row type="flex" justify="center" align="middle">
            <i-col span="20" style="border: 1px solid lightgrey;">
                <Row type="flex" justify="center" align="middle">
                    <i-col span="24">
                        <pre style="background-color: lavender">{{query_info}}</pre>
                    </i-col>
                </Row>
                <Row type="flex" justify="center" align="middle">
                    <i-col span="24">
                        <pre style="background-color: rgba(168, 230, 138, 0.38);">{{log_output}}</pre>
                    </i-col>
                </Row>
            </i-col>
        </Row>
    </section>
</template>

<script>
    import {Tools} from './assets/js/common';

    export default {
        name: "sinri-log-keeper",
        data: function () {
            return {
                is_loading: false,
                has_error: false,
                error_message: '',
                target_server: '',
                server_list: [],
                target_file: '',
                file_list: [],
                file_select_loading: false,
                range_start: '',
                range_end: '',
                around_lines: 10,
                keyword: '',
                is_case_sensitive: false,
                log_output: '',
                query_info: 'Not Searched Yet',
            };
        },
        methods: {
            logout: function () {
                console.log("logout");
                CookieHelper.setToken(null);
                //window.location.href = "login.html";
                this.$router.push({path: '/login'});
            },
            load_server_list: function () {
                Tools.callInfuraOfficeJsonAPI(
                    'post',
                    '/api/SLKController/servers',
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
                        this.$Message.error(error);
                    },
                    () => {

                    }
                )
            },
            load_server_slk_files: function (server_name) {
                this.file_select_loading = true;
                Tools.callInfuraOfficeJsonAPI(
                    'post',
                    '/api/SLKController/listSLKFiles',
                    {
                        server_name: server_name,
                    },
                    (data) => {
                        let l = [];
                        for (let i = 0; i < data.files.length; i++) {
                            l.push({
                                key: data.files[i],
                                label: data.files[i],
                            });
                        }
                        this.file_list = l;
                    },
                    (error) => {
                        this.$Message.error(error);
                        this.file_list = [];
                    },
                    () => {
                        this.file_select_loading = false;
                    }
                );
            },
            on_server_changed: function (server_name) {
                console.log(server_name);
                this.load_server_slk_files(server_name);
            },
            on_slk_search: function () {
                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    '/api/SLKController/readSLKLogs',
                    {
                        target_server: this.target_server,
                        target_file: this.target_file,
                        range_start: this.range_start,
                        range_end: this.range_end,
                        around_lines: this.around_lines,
                        is_case_sensitive: this.is_case_sensitive,
                        keyword: this.keyword,
                    },
                    (data) => {
                        console.log(data);
                        this.log_output = data.output;
                        this.query_info = 'Found ' + data.lines.length + ' lines ' +
                            'from ' + this.target_file + ", total " + data.total_lines + " lines by wc" +
                            "\n" +
                            "Command: " + data.command;
                    },
                    (error) => {
                        this.$Message.error(error);
                        this.query_info = error;
                        this.log_output = '';
                    }
                );
            }
        },
        mounted: function () {
            this.load_server_list();
        }
    }
</script>

<style scoped>
    div.ivu-row-flex {
        padding: 5px;
    }

    pre {
        overflow: auto;
    }
</style>