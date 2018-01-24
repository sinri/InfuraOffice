<template>
    <div>
        <Row>
            <i-col span="12">
                <h2>User Manage</h2>
            </i-col>
            <i-col span="12">
                <i-button class="right" icon="person-add" v-on:click="add_user">Add User</i-button>
            </i-col>
        </Row>
        <Row>
            <i-col span="24">
                <Alert type="error" show-icon v-if="has_error">
                    ERROR
                    <span slot="desc">{{ error_message }}</span>
                </Alert>
            </i-col>
        </Row>
        <i-table :columns="user_fields" :data="users"></i-table>
        <Modal v-model="show_edit_user" title="Update User" @on-ok="model_edit_user" @on-cancel="modal_close"
               :loading="modal_loading">
            <i-input style="margin: 5px" v-model="edit_username" :readonly="modal_for_editing"><span slot="prepend">Username</span>
            </i-input>
            <i-input style="margin: 5px" v-model="edit_password"
                     placeholder="Keep empty here if you do not want to change password." type="password"><span
                    slot="prepend">Password</span></i-input>
            <i-select v-model="edit_role" size="small" style="margin:5px">
                <i-option v-for="item in role_options" :value="item.value" :key="item.value">{{ item.label }}</i-option>
            </i-select>
        </Modal>
    </div>
</template>

<script>
    import {Tools} from '../../assets/js/common';

    export default {
        name: "users",
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
                    {value: 'SLK_READER', label: 'ROLE: SinriLogKeeper READER'},
                ],
                has_error: false,
                error_message: ""
            }
        },
        //props: ['user_fields', 'users'],
        methods: {
            refreshUserList: function () {
                this.$Loading.start();
                Tools.callInfuraOfficeJsonAPI(
                    "get",
                    "/api/UserManageController/users",
                    {},
                    (data) => {
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
                                                        //    this.$Message.error("Cannot edit admin!");
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
                                                            this.$Message.error("Cannot delete admin!");
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

                        for (let user_index = 0; user_index < data.list.length; user_index++) {
                            let item = data.list[user_index];
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

                        //this.user_manage_data = UserManageData;
                        this.user_fields = UserManageData.user_fields;
                        this.users = UserManageData.users;
                        //this.show_edit_user=false;
                        this.has_error = false;
                        this.error_message = "";

                        this.$Loading.finish();
                    },
                    (error) => {
                        this.has_error = true;
                        this.error_message = error;
                        this.$Loading.error();
                    }
                );
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
                this.$Loading.start();
                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    "/api/UserManageController/deleteUser",
                    {
                        username: username
                    },
                    (data) => {
                        this.$Loading.finish();
                        this.refreshUserList();
                    },
                    (error) => {
                        this.$Message.error(error);
                        this.$Loading.error();
                    }
                );
            },
            model_edit_user: function () {
                console.log('add user go');
                //  call api and refresh

                this.$Loading.start();
                this.modal_loading = true;

                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    "/api/UserManageController/updateUser",
                    {
                        username: this.edit_username,
                        password: this.edit_password,
                        role: this.edit_role,
                        privileges: this.edit_privileges
                    },
                    (data) => {
                        this.$Loading.finish();
                        this.refreshUserList();
                        this.show_edit_user = false;
                    },
                    (error) => {
                        this.$Message.error(error);
                        this.$Loading.error();
                        this.modal_loading = false;
                    }
                );
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
    }
</script>

<style scoped>
    h2 {
        margin-bottom: 10px;
    }
</style>