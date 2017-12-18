let vueSLK = null;

$(document).ready(function () {
    if (!CookieHelper.isLogin()) {
        window.location.href = "login.html";
        return;
    }

    vueSLK = new Vue({
        el: '#app_slk',
        data: {
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
        },
        methods: {
            logout: function () {
                console.log("logout");
                CookieHelper.setToken(null);
                window.location.href = "login.html";
            },
            load_server_list: function () {
                $.ajax({
                    url: '../api/SLKController/servers',
                    method: 'get',
                    dataType: 'json'
                }).done((response) => {
                    console.log(response);

                    let servers = [];

                    if (response.code !== 'OK') {
                        this.$Message.error(response.data);
                    } else {
                        for (let i = 0; i < response.data.list.length; i++) {
                            let server_item = response.data.list[i];
                            servers.push({
                                key: server_item.server_name,
                                label: server_item.server_name,
                                disabled: false
                            });
                        }
                        this.server_list = servers;
                        //this.target_server_list = [];
                    }
                }).fail(() => {
                    this.$Message.error("infura_server_select ajax failed");
                }).always(() => {
                    //console.log("guhehe");
                });
            },
            load_server_slk_files: function (server_name) {
                this.file_select_loading = true;
                $.ajax({
                    url: '../api/SLKController/listSLKFiles',
                    method: 'post',
                    data: {
                        server_name: server_name,
                    },
                    dataType: 'json'
                }).done((response) => {
                    console.log(response);
                    if (response.code !== 'OK') {
                        this.$Message.error(response.data);
                        this.file_list = [];
                    } else {
                        let l = [];
                        for (let i = 0; i < response.data.files.length; i++) {
                            l.push({
                                key: response.data.files[i],
                                label: response.data.files[i],
                            });
                        }
                        this.file_list = l;
                    }
                }).fail(() => {
                    this.$Message.error("infura_server_select ajax failed");
                    this.file_list = [];
                }).always(() => {
                    this.file_select_loading = false;
                })
            },
            on_server_changed: function (server_name) {
                console.log(server_name);
                this.load_server_slk_files(server_name);
            },
            on_slk_search: function () {
                $.ajax({
                    url: '../api/SLKController/readSLKLogs',
                    method: 'post',
                    data: {
                        target_server: this.target_server,
                        target_file: this.target_file,
                        range_start: this.range_start,
                        range_end: this.range_end,
                        around_lines: this.around_lines,
                        is_case_sensitive: this.is_case_sensitive,
                        keyword: this.keyword,
                    },
                    dataType: 'json'
                }).done((response) => {
                    if (response.code !== 'OK') {
                        this.$Message.error(response.data);
                        this.query_info = response.data;
                        this.log_output = '';
                    } else {
                        console.log(response.data);
                        this.log_output = response.data.output;
                        this.query_info = 'Found ' + response.data.lines.length + ' lines ' +
                            'from ' + this.target_file + ", total " + response.data.total_lines + " lines by wc" +
                            "\n" +
                            "Command: " + response.data.command;
                    }
                }).fail(() => {
                    this.$Message.error("ajax failed");
                    this.query_info = "ajax failed";
                    this.log_output = '';
                })
            }
        },
        mounted: function () {
            this.load_server_list();
        }
    });
});