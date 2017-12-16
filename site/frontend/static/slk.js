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
        },
        methods: {
            load_server_list: function () {
                $.ajax({
                    url: '../api/ServerWorkController/servers',
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
                    url: '../api/ServerWorkController/listSLKFiles',
                    method: 'post',
                    data: {
                        server_name: server_name,
                    },
                    dataType: 'json'
                }).done((response) => {
                    console.log(response);
                    if (response.code !== 'OK') {
                        this.$Message.error(response.data);
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
                }).always(() => {
                    this.file_select_loading = false;
                })
            },
            on_server_changed: function (server_name) {
                console.log(server_name);
                this.load_server_slk_files(server_name);
            }
        },
        mounted: function () {
            this.load_server_list();
        }
    });
});