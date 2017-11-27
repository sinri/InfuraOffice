const handlerOfIndexComponentUserManage = {
    componentDefinition: {
        template: '<div>' +
        '<Row> ' +
        '<i-col span="12"> ' +
        '<h2>User Manage</h2> ' +
        '</i-col> ' +
        '<i-col span="12"> ' +
        '<i-button class="right" icon="person-add" v-on:click="add_user">Add User</i-button> ' +
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
        '<i-table :columns="user_fields" :data="users"></i-table>' +
        '<Modal v-model="show_edit_user" title="Update User" @on-ok="model_edit_user" @on-cancel="modal_close" :loading="modal_loading">' +
        '<i-input style="margin: 5px" v-model="edit_username" :readonly="modal_for_editing"><span slot="prepend">Username</span></i-input>' +
        '<i-input style="margin: 5px" v-model="edit_password" placeholder="Keep empty here if you do not want to change password." type="password"><span slot="prepend">Password</span></i-input>' +
        '<i-select v-model="edit_role" size="small" style="margin:5px">' +
        '<i-option v-for="item in role_options" :value="item.value" :key="item.value">{{ item.label }}</i-option>' +
        '</i-select>' +
        '</Modal>' +
        //'<div>{{user_fields}}</div>' +'<div>{{users}}</div>' +
        '</div>',
        data: function () {
            return {
                user_fields: [],
                users: [],
                show_edit_user: false,
                modal_loading: true,
                modal_for_editing: false,
                edit_username: '',
                edit_password: '',
                edit_role: 'WATCHER',
                edit_privileges: [],
                role_options: [
                    {value: 'WATCHER', label: 'ROLE: WATCHER'},
                    {value: 'WORKER', label: 'ROLE: WORKER'},
                    {value: 'ADMIN', label: 'ROLE: ADMIN'},
                ],
                has_error: false,
                error_message: ""
            }
        },
        //props: ['user_fields', 'users'],
        methods: {
            refreshUserList: function () {
                vueIndex.$Loading.start();

                $.ajax({
                    url: '../api/UserManageController/users',
                    method: 'get',
                    dataType: 'json'
                }).done((response) => {
                    console.log(response);

                    if (response.code !== 'OK') {
                        // vueIndex.$Notice.error({
                        //     title: 'Load Failed',
                        //     desc: response.data
                        // });

                        this.has_error = true;
                        this.error_message = response.data;
                    } else {

                        let UserManageData = {
                            user_fields: [
                                {
                                    key: 'username', title: 'Username'
                                },
                                {
                                    key: 'role', title: 'Role'
                                },
                                {
                                    key: 'privileges', title: 'Privileges'
                                },
                                {
                                    key: 'last_login', title: 'Last Login'
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
                                                        console.log("click edit", params);
                                                        //if (params.row.username === 'admin') {
                                                        //    vueIndex.$Message.error("Cannot edit admin!");
                                                            //return;
                                                        //}
                                                        this.edit_user(params.row.username, params.row.role);
                                                    }
                                                }
                                            }, 'Edit'),
                                            h('Button', {
                                                props: {
                                                    type: 'error',
                                                    size: 'small'
                                                },
                                                style: {
                                                    margin: '5px'
                                                },
                                                on: {
                                                    click: () => {
                                                        //this.remove(params.index)
                                                        console.log("click remove", params);
                                                        if (params.row.username === 'admin') {
                                                            vueIndex.$Message.error("Cannot delete admin!");
                                                            return;
                                                        }
                                                        this.remove_user(params.row.username);
                                                    }
                                                }
                                            }, 'Delete')
                                        ]);
                                    }
                                }
                            ],
                            users: []
                        };

                        //for (let username in response.data.list) {
                        for (let user_index = 0; user_index < response.data.list.length; user_index++) {
                            //if (!response.data.list.hasOwnProperty(username)) continue;
                            //let item = response.data.list[username];
                            let item = response.data.list[user_index];
                            UserManageData.users.push({
                                username: item.username,
                                role: item.role,
                                privileges: item.privileges.join(", "),
                                last_login: (
                                    item.last_login_time > 0 ?
                                        "From " + item.last_login_ip + " on " + (new Date(item.last_login_time * 1000)) :
                                        "Never Login"
                                )
                            });
                        }

                        //vueIndex.user_manage_data = UserManageData;
                        this.user_fields = UserManageData.user_fields;
                        this.users = UserManageData.users;
                        //this.show_edit_user=false;
                        this.has_error = false;
                        this.error_message = "";
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
            add_user: function () {
                console.log('add_user');
                this.show_edit_user = true;
                this.edit_username = '';
                this.edit_password = '';
                this.edit_role = 'WORKER';

                this.modal_for_editing = false;
            },
            edit_user: function (username, role) {
                console.log('edit_user', arguments);
                this.show_edit_user = true;
                this.edit_username = username;
                this.edit_password = '';
                this.edit_role = role;

                this.modal_for_editing = true;
            },
            remove_user: function (username) {
                console.log("remove user ...", username);
                vueIndex.$Loading.start();
                $.ajax({
                    url: '../api/UserManageController/deleteUser',
                    method: 'post',
                    data: {
                        username: username
                    },
                    dataType: 'json',
                }).done((response) => {
                    if (response.code === 'OK') {
                        vueIndex.$Loading.finish();
                        this.refreshUserList();
                    } else {
                        this.$Message.error(response.data);
                        vueIndex.$Loading.error();
                    }
                }).fail(() => {
                    this.$Message.error('ajax failed');
                    vueIndex.$Loading.error();
                });
            },
            model_edit_user: function () {
                console.log('add user go');
                //  call api and refresh

                vueIndex.$Loading.start();
                this.modal_loading = true;
                $.ajax({
                    url: '../api/UserManageController/updateUser',
                    method: 'post',
                    data: {
                        username: this.edit_username,
                        password: this.edit_password,
                        role: this.edit_role,
                        privileges: this.edit_privileges
                    },
                    dataType: 'json'
                }).done((response) => {
                    if (response.code === 'OK') {
                        vueIndex.$Loading.finish();
                        this.refreshUserList();
                        this.show_edit_user = false;
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
                console.log('modal close');
                this.show_add_user = false;
                // actually do nothing
            }
        },
        mounted() {
            console.log(".....");
            this.refreshUserList();
        }
    },
    handleIndexComponentUserManage: function () {
        console.log("handleIndexComponentUserManage");
    }
};