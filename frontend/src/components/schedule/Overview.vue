<template>
    <div>
        <Row>
            <i-col span="12">
                <h2>Job Config</h2>
            </i-col>
            <i-col span="12">
                <i-button icon="android-add" class="right" v-on:click="on_add_btn_click">Add Job</i-button>
                <div class="right">&nbsp;</div>
                <i-button icon="android-refresh" class="right" v-on:click="refresh_jobs">Refresh</i-button>
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
        <Row>
            <i-col span="24">
                <i-table :columns="job_fields" :data="job_list" stripe></i-table>
            </i-col>
        </Row>
        <Modal v-model="display_history" width="90%">
            <div slot="header">
                <h2>History of {{display_history_job_name}}</h2>
                <i-select v-model="history_log_name" @on-change="on_history_log_changed" style="margin: 5px 0;">
                    <i-option v-for="item in history_log_list" :value="item.log_name" :key="item.log_name">
                        {{item.title}}
                    </i-option>
                </i-select>
            </div>
            <div style="overflow: auto;width: 100%;height: 400px;background: #def3e3;">
                <pre>{{history_log_content}}</pre>
            </div>
        </Modal>
    </div>
</template>

<script>
    import {Tools} from '../../assets/js/common';
    import ICol from "iview/src/components/grid/col";

    export default {
        components: {ICol},
        name: "overview",
        data: function () {
            return {
                has_error: false,
                error_message: '',
                job_fields: [
                    {key: 'job_name', title: 'Job Name', sortable: true},
                    {key: 'job_type', title: 'Job Type', sortable: true},
                    {
                        key: 'affection', title: 'Target Machines',
                        render: (h, params) => {
                            return h('div', [
                                h('Poptip', {
                                        props: {
                                            //trigger: 'hover',
                                            title: 'Affected these servers:' + params.row.job_name,
                                            width: 400,
                                            transfer: true
                                        },
                                    },
                                    [
                                        h('pre', {slot: "content"}, params.row.affection),
                                        h('i-button', {
                                            props: {
                                                type: "ghost",
                                                shape: "circle",
                                                icon: "ios-glasses-outline"
                                            }
                                        })
                                    ]
                                )
                            ]);
                        }
                    },
                    {
                        key: 'history', title: 'History',
                        render: (h, params) => {
                            return h('div', [
                                h('i-button', {
                                    props: {
                                        type: "ghost",
                                        shape: "circle",
                                        icon: "ios-recording-outline",
                                    },
                                    on: {
                                        click: () => {
                                            console.log('lalala');
                                            console.log(params.row.job_name);
                                            this.load_history_list(params.row.job_name);
                                        }
                                    }
                                })
                            ]);
                        }
                    },
                    {key: 'cron_timer', title: 'Cron Timer'},
                    {key: 'last_run', title: 'Last Run'},
                    {key: 'running_status', title: 'Status'},
                    {
                        key: 'action', title: 'Action',
                        render: (h, params) => {
                            return h('div', [
                                h('Button', {
                                    props: {
                                        type: params.row.stopped ? 'success' : 'warning',
                                        size: 'small'
                                    },
                                    style: {
                                        margin: '5px'
                                    },
                                    on: {
                                        click: () => {
                                            //this.remove(params.index)
                                            console.log("click change status", params);
                                            this.change_job_status(params.row.job_name, !params.row.stopped);
                                        }
                                    }
                                }, params.row.stopped ? 'Active' : 'Stop'),
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
                                            this.remove_job(params.row.job_name);
                                        }
                                    }
                                }, 'Delete')
                            ]);
                        }
                    }
                ],
                job_list: [],
                display_history: false,
                display_history_job_name: '',
                history_log_name: '',
                history_log_list: [],
                history_log_content: '',
            }
        },
        methods: {
            howLongBefore: function (oneDate) {
                let diff_seconds = ((new Date().getTime() - oneDate.getTime()) / 1000);
                if (diff_seconds < 60) {
                    return Math.floor(diff_seconds) + " seconds ago";
                } else if (diff_seconds < 60 * 60) {
                    return Math.floor(diff_seconds / 60) + " minutes ago";
                } else if (diff_seconds < 60 * 60 * 24) {
                    return Math.floor(diff_seconds / 60 / 60) + " hours ago";
                } else {
                    return Math.floor(diff_seconds / 60 / 60 / 24) + " days ago";
                }
            },
            refresh_jobs: function () {
                this.$Loading.start();
                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    "/api/JobConfigController/jobs",
                    {},
                    (data) => {
                        this.$Loading.finish();
                        //this.refresh_platform_accounts();

                        let job_list = data.list;
                        for (let i = 0; i < job_list.length; i++) {
                            job_list[i].cron_timer = job_list[i].cron_time_minute + " "
                                + job_list[i].cron_time_hour + " "
                                + job_list[i].cron_time_day_of_month + " "
                                + job_list[i].cron_time_month + " "
                                + job_list[i].cron_time_day_of_week;
                            let oneDate = (new Date(job_list[i].last_run_timestamp * 1000));
                            job_list[i].last_run = job_list[i].last_run_timestamp
                                ? oneDate + " (" + this.howLongBefore(oneDate) + ")"
                                : 'Never';
                            job_list[i].running_status = job_list[i].stopped ? 'STOPPED' : 'NORMAL';
                            job_list[i].affection = (
                                job_list[i].server_list && job_list[i].server_list.length > 0 ?
                                    "Servers: " + job_list[i].server_list.join(",") :
                                    ''
                            ) + "\n" + (
                                job_list[i].server_group_list && job_list[i].server_group_list.length > 0 ?
                                    "Groups: " + job_list[i].server_group_list.join(",") :
                                    ''
                            );
                        }
                        this.job_list = job_list;
                    },
                    (error) => {
                        this.$Message.error("Loading jobs: " + error);
                        this.$Loading.error();
                    }
                );
            },
            on_add_btn_click: function () {
                this.$Message.info('Add job config of certain type, please use the menu item!');
            },
            remove_job: function (job_name) {
                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    '/api/JobConfigController/removeJob',
                    {
                        job_name: job_name
                    },
                    (data) => {
                        this.$Message.success("Remove job: Done");
                        this.refresh_jobs();
                    },
                    (error) => {
                        this.$Message.error("Remove job: " + error);
                    }
                );
            },
            change_job_status: function (job_name, to_status) {
                let action_text = to_status ? 'STOP' : 'RECOVER';
                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    '/api/JobConfigController/changeJobRunningSwitch',
                    {
                        job_name: job_name,
                        stop_it: (to_status ? 'YES' : 'NO')
                    },
                    (data) => {
                        this.$Message.success(action_text + " job: Done");
                        this.refresh_jobs();
                    },
                    (error) => {
                        this.$Message.error(action_text + " job: " + error);
                    }
                );
            },
            load_history_list: function (job_name) {
                this.display_history_job_name = job_name;
                this.display_history = true;
                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    '/api/JobConfigController/listJobLog',
                    {
                        job_name: job_name
                    },
                    (data) => {
                        this.history_log_name = '';
                        this.history_log_list = data.list;
                    },
                    (error) => {
                        this.$Message.error("Failed to load history of job: " + error);
                    }
                );
            },
            on_history_log_changed: function () {
                this.history_log_content = '';
                console.log('history_log_name changed to ' + this.history_log_name);
                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    '/api/JobConfigController/readJobLog',
                    {
                        log_name: this.history_log_name
                    },
                    (data) => {
                        this.history_log_content = data.content;
                    },
                    (error) => {
                        this.$Message.error("Failed to load log of job: " + error);
                    }
                );
            }
        },
        mounted: function () {
            this.refresh_jobs();
        }
    }
</script>

<style scoped>
    h2 {
        margin: 10px;
    }
</style>