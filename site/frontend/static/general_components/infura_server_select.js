// Register
// Vue.component(
//  GeneralComponentOfInfuraServerSelect.componentDefinition.name,
//  GeneralComponentOfInfuraServerSelect.componentDefinition
// );
// Usage
// <infura_server_select v-bind:value="draft.server_list" v-on:change="infura_server_select_changed"></infura_server_select>
// Event Handler: infura_server_select_changed(selected_server_list)
const GeneralComponentOfInfuraServerSelect = {
    componentDefinition: {
        name: 'infura_server_select',
        template: '<div style="display: inline-block;width: 100%;margin:5px;">\
            <Row type="flex" align="middle" justify="space-between">\
                <i-col span="16">\
                    <Select v-model="target_server_list" @on-change="select_changed" multiple style="width:100%">\
                        <Option v-for="item in full_server_list" :value="item.key" :key="item.key">{{ item.label }}</Option>\
                    </Select>\
                </i-col>\
                <i-col span="6">\
                    <Dropdown @on-click="on_server_group_dropdown_item_click" placement="bottom-end">\
                        <a href="javascript:void(0)"> \
                            Use Server Group  \
                            <Icon type="arrow-down-b"></Icon> \
                        </a> \
                        <DropdownMenu slot="list"> \
                            <Dropdown placement="left-start" v-for="group in full_server_group_list" :key="group.group_name"> \
                                <DropdownItem> \
                                    <Icon type="ios-arrow-left"></Icon> \
                                    {{group.group_name}}  \
                                </DropdownItem> \
                                <DropdownMenu slot="list"> \
                                    <DropdownItem :name="\'append-\'+group.group_name">Append {{group.group_name}}</DropdownItem> \
                                    <DropdownItem :name="\'remove-\'+group.group_name">Remove {{group.group_name}}</DropdownItem> \
                                </DropdownMenu> \
                            </Dropdown> \
                        </DropdownMenu> \
                    </Dropdown> \
                </i-col>\
            </Row>\
        </div>',
        props: ['value'],
        data: function () {
            return {
                full_server_group_list: [],
                full_server_list: [],
                target_server_list: [],
                //value:[],
            }
        },
        methods: {
            load_server_group_list: function () {
                //vueIndex.$Loading.start();
                $.ajax({
                    url: '../api/ServerWorkController/serverGroups',
                    method: 'post',
                    dataType: 'json'
                }).done((response) => {
                    if (response.code === 'OK') {
                        //vueIndex.$Loading.finish();

                        let group_data = response.data.list;
                        for (let i = 0; i < group_data.length; i++) {
                            //group_data[i].key=group_data[i].group_name;
                            group_data[i].server_name_list_readable = group_data[i].server_name_list.join(', ');
                        }
                        this.full_server_group_list = group_data;
                    } else {
                        this.$Message.error(response.data);
                        //vueIndex.$Loading.error();
                    }
                }).fail(() => {
                    this.$Message.error('ajax failed for server group loading');
                    //vueIndex.$Loading.error();
                });
            },
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
                        this.full_server_list = servers;
                        //this.target_server_list = [];
                    }
                }).fail(() => {
                    this.$Message.error("infura_server_select ajax failed");
                }).always(() => {
                    //console.log("guhehe");
                });
            },
            on_server_group_dropdown_item_click: function (action_group_name) {
                console.log('on_server_group_dropdown_item_click', action_group_name);
                let group_name = action_group_name.substring(7);
                let servers = [];
                for (let i = 0; i < this.full_server_group_list.length; i++) {
                    if (this.full_server_group_list[i].group_name === group_name) {
                        servers = this.full_server_group_list[i].server_name_list;
                        break;
                    }
                }
                console.log('found servers in group ' + group_name, servers);
                if (servers.length <= 0) {
                    return;
                }
                let action = '';
                if (action_group_name.startsWith("append-")) {
                    //append
                    for (let i = 0; i < servers.length; i++) {
                        if (this.target_server_list.indexOf(servers[i]) < 0) {
                            this.target_server_list.push(servers[i]);
                            console.log("pushed", servers[i]);
                        }
                    }
                } else if (action_group_name.startsWith("remove-")) {
                    //remove
                    for (let i = servers.length - 1; i >= 0; i--) {
                        if (this.target_server_list.indexOf(servers[i]) >= 0) {
                            this.target_server_list.splice(i, 1);
                            console.log("removed", servers[i]);
                        }
                    }
                } else {
                    //do nothing
                }
            },
            select_changed: function () {
                console.log('select changed');
                //this.$emit('change',this.target_server_list);
            },
        },
        watch: {
            value: function (val) {
                console.log('watch value', val);
                this.target_server_list = val;
            },
            target_server_list: function (val) {
                this.$emit('change', this.target_server_list);
            }
        },
        mounted: function () {
            this.load_server_group_list();
            this.load_server_list();
            this.target_server_list = this.value;
        },
    }
};