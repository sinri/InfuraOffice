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
            file_info: {
                file_size: 0,
                total_lines: '',
            },
            is_over_1GB: false,
            file_list: [],
            file_select_loading: false,
            range_start: '',
            range_end: '',
            last_lines: '',
            around_lines: 10,
            keyword: '',
            is_case_sensitive: false,
            log_output: '',
            query_info: 'Not Searched Yet',
            result: {
                type: 'info',
                output: '',
                return_value: '',
                status: '',
                outputLines: '',
            }
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
                    async: false,
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
            get_file_info: function (value) {
                if (!value) return false;
                const data = {
                    target_server: this.target_server,
                    target_file: value
                }
                this.axios({
                    url: '../api/SLKController/getFileInfoAsync',
                    method: 'post',
                    data
                }).then(res => {
                    if (res.code === 'OK') {
                        const file_size = (res.data.task_index_for_file_size / (1024*1024*1024)).toFixed(2);
                        this.file_info = {
                            file_size,
                            total_lines: res.data.task_index_for_file_lines
                        }
                        const int = Number.parseInt(file_size, 10);
                        this.is_over_1GB = !!int;
                    }
                })
            },
            on_server_changed: function (server_name) {
                console.log(server_name);
                if (!server_name) return false;
                this.load_server_slk_files(server_name);
            },
            on_slk_search: function () {
                if (!this.target_server) {
                    this.$Message.warning("please select Server");
                    return;
                }
                if (!this.target_file) {
                    this.$Message.warning("please select File");
                    return;
                }
                let url = 'readSLKLogsAsync';
                url = `../api/SLKController/${url}`;
                const formdata = {
                    target_server: this.target_server,
                    target_file: this.target_file,
                    around_lines: this.around_lines,
                    is_case_sensitive: this.is_case_sensitive,
                    keyword: this.keyword,
                    range_start: this.range_start,
                    range_end: this.range_end,
                    total_lines: this.file_info.total_lines
                };
                this.axios({ url: url, method: 'post', data: formdata})
                    .then(response => {
                        if (response.code !== 'OK') {
                            this.$Message.error(response.data);
                            
                            this.query_info = response.data;
                            this.log_output = '';
                        } else {
                            console.log(response.data);
                            this.register_slow_queryTask(response.data.task_index);
                            // this.log_output = response.data.output;
                            // this.query_info = 'Found ' + response.data.lines.length + ' lines ' +
                            //     'from ' + this.target_file + ", total " + response.data.total_lines + " lines by wc" +
                            //     "\n" +
                            //     "Command: " + response.data.command;
                        }
                    }).catch(err => {
                        this.query_info = "ajax failed";
                        this.log_output = '';
                    })
            },
            register_slow_queryTask: function (task_index) {
                let query_time = 0;
                const begin = (new Date()).getTime();
                const finish_status = ['FINISHED', 'FETCHED'];
                // 立即执行一次
                this.check_result_task(task_index).then(res => {
                    const { status, output, outputLines } = res;
                    const type = finish_status.includes(status) ? 'success' : 'info';
                    this.result = Object.assign(this.result, {type, status, output});
                    if (finish_status.includes(status)) {
                        const end = (new Date()).getTime();
                        query_time = end - begin;
                        this.query_info = 'Found ' + outputLines.length + ' lines ' +
                            'from ' + this.target_file + ', cost ' + query_time + 'ms';
                        setTimeout(() => {
                            this.result = Object.assign(this.result, {status: ''});
                        }, 4000);
                        return;
                    }
                    let clock = setInterval(() => {
                        this.check_result_task(task_index).then(response => {
                            const { status, output, outputLines } = response;
                            const type = finish_status.includes(status) ? 'success' : 'info';
                            this.result = Object.assign(this.result, {type, status, output});
                            if (finish_status.includes(status)) {
                                clearInterval(clock);
                                clock = null;
                                const end = (new Date()).getTime();
                                query_time = end - begin;
                                this.query_info = 'Found ' + outputLines.length + ' lines ' +
                                    'from ' + this.target_file + ', cost ' + query_time + 'ms';
                                setTimeout(() => {
                                    this.result = Object.assign(this.result, {status: ''});
                                }, 4000);
                            }
                        })
                    }, 2000)
                })
            },
            check_result_task: function(task_index) {
                const url = '../api/SLKController/checkAsyncTaskResult';
                return this.axios({url, method: 'post', data: {task_index}})
                    .then(response => {
                        if (response.code !== 'OK') {
                            this.result = {type: 'error', status: 'ERROR'};
                            this.$Message.error(response.data);
                            this.query_info = response.data;
                            this.log_output = '';
                        } else {
                            return response.data;
                        }
                    }).catch(err => {
                        this.result = {type: 'error', status: 'ERROR'};
                        this.query_info = "ajax failed";
                        this.log_output = '';
                    })
            },
            axios: function (option) {
                const options = Object.assign({method: 'get', dataType: 'json'}, option);
                return new Promise((resolve, reject) => {
                    $.ajax(options).then(res => {
                        resolve(res);
                    }).catch(err => {
                        this.$Message.error("ajax failed");
                        console.error(err);
                        reject(err);
                    })
                })
            }
        },
        mounted: function () {
            this.load_server_list();
        }
    });
});