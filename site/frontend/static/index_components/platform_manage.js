const handlerOfIndexComponentPlatformManage = {
    componentDefinition: {
        template: '<div>' +
        '<Row> ' +
        '<i-col span="12"> ' +
        '<h2>Platform Manage</h2> ' +
        '</i-col> ' +
        '<i-col span="12"> ' +
        '<i-button class="right" icon="android-add" v-on:click="on_add_btn">Add Platform Account</i-button> ' +
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
        '<i-table :columns="platform_fields" :data="platform_data"></i-table>' +
        '<Modal v-model="show_edit_platform_account" title="Update Platform Account" @on-ok="modal_edit_platform_account" @on-cancel="modal_close" :loading="modal_loading">' +
        //'<i-input style="margin: 5px" v-model="modal_data.platform_type"><span slot="prepend">Platform Type</span></i-input>' +
        '<span style="margin: 10px">Platform Type</span>&nbsp;&nbsp;' +
        '<RadioGroup v-model="modal_data.platform_type" type="button">' +
        '<Radio label="IDC"></Radio>' +
        '<Radio label="Aliyun"></Radio>' +
        '<Radio label="Else"></Radio>' +
        '</RadioGroup>' +
        '<i-input style="margin: 5px" v-model="modal_data.platform_name"><span slot="prepend">Platform Account</span></i-input>' +
        '<i-input style="margin: 5px" v-model="modal_data.auth_id"><span slot="prepend">Auth ID</span></i-input>' +
        '<i-input style="margin: 5px" v-model="modal_data.auth_key"><span slot="prepend">Auth Key</span></i-input>' +
        '</Modal>' +
        '</div>',
        data: function () {
            return {
                has_error: false,
                error_message: '',

                show_edit_platform_account: false,
                modal_loading: true,
                modal_data: {
                    platform_name: '',
                    platform_type: '',
                    auth_id: '',
                    auth_key: ''
                },

                platform_fields: [
                    {
                        key: 'platform_name', title: 'Platform Name'
                    },
                    {
                        key: 'platform_type', title: 'Platform Type'
                    },
                    {
                        key: 'auth_id', title: 'Auth ID (ak key)'
                    },
                    {
                        key: 'auth_key', title: 'Auth Key (ak secret)'
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
                                    on: {
                                        click: () => {
                                            //this.remove(params.index)
                                            console.log("click remove", params);
                                            this.on_remove_btn(params.row.platform_name);
                                        }
                                    }
                                }, 'Delete')
                            ]);
                        }
                    }
                ],
                platform_data: [],
            };
        },
        methods: {
            refresh_platform_accounts: function () {
                vueIndex.$Loading.start();
                $.ajax({
                    url: '../api/PlatformWorkController/platforms',
                    method: 'post',
                    dataType: 'json'
                }).done((response) => {
                    if (response.code === 'OK') {
                        vueIndex.$Loading.finish();
                        //this.refresh_platform_accounts();

                        this.platform_data = response.data.list;

                        this.show_edit_platform_account = false;
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
                    platform_name: '',
                    platform_type: '',
                    auth_id: '',
                    auth_key: ''
                };
                this.show_edit_platform_account = true;
                this.modal_loading = true;
            },
            on_edit_btn: function (item) {
                this.modal_data = {
                    platform_name: item.platform_name,
                    platform_type: item.platform_type,
                    auth_id: item.auth_id,
                    auth_key: item.auth_key
                };
                this.show_edit_platform_account = true;
                this.modal_loading = true;
            },
            on_remove_btn: function (platform_name) {
                // api
                vueIndex.$Loading.start();
                $.ajax({
                    url: '../api/PlatformManageController/removePlatformAccount',
                    method: 'post',
                    data: {
                        platform_name: platform_name,
                    },
                    dataType: 'json'
                }).done((response) => {
                    if (response.code === 'OK') {
                        vueIndex.$Loading.finish();
                        this.refresh_platform_accounts();
                    } else {
                        this.$Message.error(response.data);
                        vueIndex.$Loading.error();
                    }
                }).fail(() => {
                    this.$Message.error('ajax failed');
                    vueIndex.$Loading.error();
                });
            },
            modal_edit_platform_account: function () {
                //  API
                vueIndex.$Loading.start();
                this.modal_loading = true;
                $.ajax({
                    url: '../api/PlatformManageController/updatePlatformAccount',
                    method: 'post',
                    data: {
                        platform_name: this.modal_data.platform_name,
                        platform_type: this.modal_data.platform_type,
                        auth_id: this.modal_data.auth_id,
                        auth_key: this.modal_data.auth_key
                    },
                    dataType: 'json'
                }).done((response) => {
                    if (response.code === 'OK') {
                        vueIndex.$Loading.finish();
                        this.refresh_platform_accounts();
                        this.show_edit_platform_account = false;
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
                this.show_edit_platform_account = false;
            }
        },
        mounted: function () {
            this.refresh_platform_accounts();
        }
    }
};