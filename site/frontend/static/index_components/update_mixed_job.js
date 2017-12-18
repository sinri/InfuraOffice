const handlerOfIndexComponentUpdateMixedJob = {
    componentDefinition: {
        template: '<div>\
            <Row>\
                <i-col span="24"><h2>Mixed Job</h2></i-col>\
            </Row>\
            <Row>\
                <i-col span="24"><p>Call preset shell functions to do mixed jobs, including explosion, antiquity and zombie removal.</p></i-col>\
            </Row>\
            <Row>\
                <i-col span="16">\
                    <span>Select to update: </span> \
                    <Select style="width: 200px" v-model="edit_job_name" filterable>\
                        <Option v-for="item in job_list" :value="item.job_name" :key="item.job_name">{{ item.job_name }}</Option>\
                    </Select> \
                    <Button v-on:click="on_load_btn">Load</Button>\
                </i-col>\
                <i-col span="8">\
                    <span>Or create one new:</span> \
                    <Button v-on:click="on_create_btn">New</Button>\
                </i-col> \
            </Row>\
            <Row><i-col span="24"><div style="margin-top: 20px">&nbsp;</div></i-col> </Row>\
            <Row type="flex" justify="end" align="middle">\
                <i-col span="12"><h3>{{ !is_draft_for_creation?"Update "+draft.job_name:"New Mixed Job" }}</h3></i-col>\
                <i-col span="12"><Button class="right" v-on:click="on_save_btn">SAVE</Button></i-col>\
            </Row>\
            <Row type="flex" justify="center" align="middle">\
                <i-col span="3"><span>Job Name: </span></i-col>\
                <i-col span="20">\
                    <i-input style="margin: 5px" v-model="draft.job_name" :readonly="!is_draft_for_creation"></i-input>\
                </i-col>\
            </Row>\
            <Row type="flex" justify="center" align="middle">\
                <i-col span="3"><span>Servers and Groups: </span></i-col>\
                <i-col span="20">\
                    <infura_server_and_group_select :servers="draft.server_list" :server_groups="draft.server_group_list" v-on:change="infura_server_and_group_select_changed"></infura_server_and_group_select>\
                </i-col>\
            </Row>\
            <Row type="flex" justify="space-between" align="middle">\
                <i-col span="3"><span>Cron Timer: </span></i-col>\
                <i-col span="4"><i-input style="margin: 5px" v-model="draft.cron_time_minute"><span slot="prepend">Minute</span></i-input></i-col>\
                <i-col span="4"><i-input style="margin: 5px" v-model="draft.cron_time_hour"><span slot="prepend">Hour</span></i-input></i-col>\
                <i-col span="4"><i-input style="margin: 5px" v-model="draft.cron_time_day_of_month"><span slot="prepend">Day</span></i-input></i-col>\
                <i-col span="4"><i-input style="margin: 5px" v-model="draft.cron_time_month"><span slot="prepend">Month</span></i-input></i-col>\
                <i-col span="4"><i-input style="margin: 5px" v-model="draft.cron_time_day_of_week"><span slot="prepend">Weekday</span></i-input></i-col>\
            </Row>\
            <Row>\
                <i-col span="24"><h3>Explosion: thin files in use</h3></i-col>\
            </Row>\
            <Row v-for="(item,key) in draft.explosion_list" class="explosion_list_config_div">\
                <i-col span="10"><i-input v-model="item.keep_tail_lines"><span slot="prepend">Keep Tail Lines:</span></i-input></i-col>\
                <i-col span="10">\
                    <i-select v-model="item.keep_backup">\
                        <i-option value="0" label="Not Keep Backup"></i-option>\
                        <i-option value="1" label="Keep Backup"></i-option>\
                    </i-select>\
                </i-col>\
                <i-col span="4"><i-button v-on:click="remove_explosion_list_item(key)" icon="android-cancel" type="error">Delete</i-button></i-col>\
                <i-col span="24">\
                    <i-input type="textarea" v-model="item.joined_files" placeholder="file patterns in lines">\
                        <span slot="prepend">Files:</span>\
                    </i-input>\
                </i-col>\
            </Row>\
            <Row>\
                <i-col span="24" style="text-align: center">\
                    <i-button v-on:click="add_explosion_list_item" icon="android-add">Add</i-button>\
                </i-col>\
            </Row>\
            <Row>\
                <i-col span="24"><h3>Antiquity: remove files with name containing date passed</h3></i-col>\
            </Row>\
            <Row v-for="(item,key) in draft.antiquity_list" class="antiquity_list_config_div">\
                <i-col span="10"><i-input v-model="item.not_modified_days"><span slot="prepend">Not Modified Days:</span></i-input></i-col>\
                <i-col span="10"></i-col>\
                <i-col span="4"><i-button v-on:click="remove_antiquity_list_item(key)" icon="android-cancel" type="error">Delete</i-button></i-col>\
                <i-col span="24">\
                    <i-input type="textarea" v-model="item.joined_files" placeholder="file patterns in lines">\
                        <span slot="prepend">Files:</span>\
                    </i-input>\
                </i-col>\
            </Row>\
            <Row>\
                <i-col span="24" style="text-align: center">\
                    <i-button v-on:click="add_antiquity_list_item" icon="android-add">Add</i-button>\
                </i-col>\
            </Row>\
            <Row>\
                <i-col span="24"><h3>Zombie: remove files not accessed for a period</h3></i-col>\
            </Row>\
            <Row v-for="(item,key) in draft.zombie_list" class="zombie_list_config_div">\
                <i-col span="10"><i-input v-model="item.not_accessed_days"><span slot="prepend">Not Accessed Days:</span></i-input></i-col>\
                <i-col span="10"></i-col>\
                <i-col span="4"><i-button v-on:click="remove_zombie_list_item(key)" icon="android-cancel" type="error">Delete</i-button></i-col>\
                <i-col span="24">\
                    <i-input type="textarea" v-model="item.joined_files" placeholder="file patterns in lines">\
                        <span slot="prepend">Files:</span>\
                    </i-input>\
                </i-col>\
            </Row>\
            <Row>\
                <i-col span="24" style="text-align: center">\
                    <i-button v-on:click="add_zombie_list_item" icon="android-add">Add</i-button>\
                </i-col>\
            </Row>\
        </div>',
        data: function () {
            return {
                edit_job_name: '',
                job_list: [],
                is_draft_for_creation: true,
                draft: {
                    job_name: '',
                    job_type: 'MixedJob',
                    cron_time_minute: '*',
                    cron_time_hour: '*',
                    cron_time_day_of_month: '*',
                    cron_time_month: '*',
                    cron_time_day_of_week: '*',
                    last_run_timestamp: 0,
                    server_list: [],
                    server_group_list: [],
                    explosion_list: [],
                    antiquity_list: [],
                    zombie_list: [],
                }
            }
        },
        methods: {
            infura_server_and_group_select_changed: function (target_servers, target_server_groups) {
                this.draft.server_list = target_servers;
                this.draft.server_group_list = target_server_groups;
            },
            load_existed_mixed_jobs: function () {
                vueIndex.$Loading.start();
                $.ajax({
                    url: '../api/JobConfigController/jobs/MixedJob',
                    method: 'post',
                    dataType: 'json'
                }).done((response) => {
                    if (response.code === 'OK') {
                        vueIndex.$Loading.finish();
                        //this.refresh_platform_accounts();

                        this.job_list = response.data.list;
                    } else {
                        this.$Message.error("Loading jobs: " + response.data);
                        vueIndex.$Loading.error();
                    }
                }).fail(() => {
                    this.$Message.error("Loading jobs: " + 'ajax failed');
                    vueIndex.$Loading.error();
                });
            },
            on_load_btn: function () {
                console.log("on_load_btn");
                if (!this.edit_job_name) return;
                this.is_draft_for_creation = false;
                for (let i = 0; i < this.job_list.length; i++) {
                    if (this.job_list[i].job_name === this.edit_job_name) {
                        this.draft = this.job_list[i];
                        for (let i = 0; i < this.draft.explosion_list.length; i++) {
                            this.draft.explosion_list[i].joined_files = this.draft.explosion_list[i].files.join("\n");
                        }
                        for (let i = 0; i < this.draft.antiquity_list.length; i++) {
                            this.draft.antiquity_list[i].joined_files = this.draft.antiquity_list[i].files.join("\n");
                        }
                        for (let i = 0; i < this.draft.zombie_list.length; i++) {
                            this.draft.zombie_list[i].joined_files = this.draft.zombie_list[i].files.join("\n");
                        }
                        break;
                    }
                }
                console.log(this.draft);
            },
            on_create_btn: function () {
                console.log('on_create_btn');
                this.is_draft_for_creation = true;
                this.draft = {
                    job_name: '',
                    job_type: 'MixedJob',
                    cron_time_minute: '*',
                    cron_time_hour: '*',
                    cron_time_day_of_month: '*',
                    cron_time_month: '*',
                    cron_time_day_of_week: '*',
                    last_run_timestamp: 0,
                    server_list: [],
                    server_group_list: [],
                    explosion_list: [],
                    antiquity_list: [],
                    zombie_list: [],
                };
            },
            add_explosion_list_item: function () {
                this.draft.explosion_list.push({
                    joined_files: "",
                    keep_tail_lines: 0,
                    keep_backup: "0",
                });
            },
            remove_explosion_list_item: function (index) {
                console.log("remove_explosion_list_item", index);
                this.draft.explosion_list.splice(index, 1);
            },
            add_antiquity_list_item: function () {
                this.draft.antiquity_list.push({
                    joined_files: "",
                    not_modified_days: 7,
                });
            },
            remove_antiquity_list_item: function (index) {
                console.log("remove_antiquity_list_item", index);
                console.log("before", this.draft.antiquity_list);
                this.draft.antiquity_list.splice(index, 1);
                console.log("after", this.draft.antiquity_list);
            },
            add_zombie_list_item: function () {
                this.draft.zombie_list.push({
                    joined_files: "",
                    not_accessed_days: 7,
                });
            },
            remove_zombie_list_item: function (index) {
                this.draft.zombie_list.splice(index, 1);
            },
            on_save_btn: function () {
                console.log('on_save_btn');

                let draft = JSON.parse(JSON.stringify(this.draft));

                for (let i = 0; i < this.draft.explosion_list.length; i++) {
                    draft.explosion_list[i].files = this.draft.explosion_list[i].joined_files.split(/\s*[\r\n]\s*/);
                    delete draft.explosion_list[i].joined_files;
                }
                for (let i = 0; i < this.draft.antiquity_list.length; i++) {
                    draft.antiquity_list[i].files = this.draft.antiquity_list[i].joined_files.split(/\s*[\r\n]\s*/);
                    delete draft.antiquity_list[i].joined_files;
                }
                for (let i = 0; i < this.draft.zombie_list.length; i++) {
                    draft.zombie_list[i].files = this.draft.zombie_list[i].joined_files.split(/\s*[\r\n]\s*/);
                    delete draft.zombie_list[i].joined_files;
                }

                vueIndex.$Loading.start();
                $.ajax({
                    url: '../api/JobConfigController/updateJob',
                    method: 'post',
                    data: draft,
                    dataType: 'json'
                }).done((response) => {
                    if (response.code === 'OK') {
                        vueIndex.$Loading.finish();
                        this.$Message.success("Job updated");
                        this.load_existed_mixed_jobs();
                    } else {
                        this.$Message.error("Update job: " + response.data);
                    }
                }).fail(() => {
                    this.$Message.error("Update job: " + 'ajax failed');
                    vueIndex.$Loading.error();
                });
            }
        },
        mounted: function () {
            this.load_existed_mixed_jobs();
        }
    }
}