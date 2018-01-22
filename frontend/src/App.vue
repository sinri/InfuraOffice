<template>
    <div id="app">
        <!--<img src="./assets/logo.png">-->
        <div class="layout">
            <Layout>
                <Header>
                    <Menu mode="horizontal" theme="dark" active-name="1" @on-select="onTopBarMenuSelect">
                        <div class="layout-logo">
                            InfuraOffice
                        </div>
                        <div>
                            <Breadcrumb class="layout-breadcrumb">
                                <!--<BreadcrumbItem v-for="item in Breadcrumb">{{item}}</BreadcrumbItem>-->
                                <BreadcrumbItem v-for="(item,index) in $route.matched" style="color:white">
                                    <a v-if="index < $route.matched.length - 1">{{item.name}}</a>
                                    <strong v-else>{{item.name}}</strong>
                                </BreadcrumbItem>
                            </Breadcrumb>
                        </div>
                        <div class="layout-nav">
                            <MenuItem name="slk">
                                <Icon type="ios-paper-outline"></Icon>
                                <span>SLK</span>
                            </MenuItem>
                            <MenuItem name="user_center">
                                <Avatar icon="person"/>
                                <span>{{current_username}}</span>
                            </MenuItem>
                            <MenuItem name="logout">
                                <Icon type="log-out"></Icon>
                                <span>Logout</span>
                            </MenuItem>
                        </div>
                    </Menu>
                </Header>
                <Layout>
                    <Sider hide-trigger :style="{background: '#fff'}">
                        <i-menu id="menu_pane" :theme="menu_theme" active-name="1" accordion width="auto"
                                @on-select="onMenuItemSelected">
                            <template v-for="item_0 in $router.options.routes" v-if="item_0.path==='/'">
                                <template v-for="item_a in item_0.children" v-if="!item_a.hidden">
                                    <template v-if="item_a.is_group">
                                        <menu-group :title="item_a.title">
                                            <menu-item v-for="item_b in item_a.children" v-if="!item_b.hidden"
                                                       :name="item_b.name">
                                                <Icon :type="item_b.icon"></Icon>
                                                <span>{{item_b.title}}</span>
                                            </menu-item>
                                        </menu-group>
                                    </template>
                                    <template v-else>
                                        <menu-item :name="item_a.name">
                                            <Icon :type="item_a.icon"></Icon>
                                            <span>{{item_a.title}}</span>
                                        </menu-item>
                                    </template>
                                </template>
                            </template>
                        </i-menu>
                    </Sider>
                    <Layout :style="{padding: '0 24px 24px'}">
                        <!--
                        <Breadcrumb :style="{margin: '24px 0'}">
                        <BreadcrumbItem v-for="(item,index) in $route.matched" style="color:white">
                            <a v-if="index < $route.matched.length - 1">{{item.name}}</a>
                            <strong v-else>{{item.name}}</strong>
                        </BreadcrumbItem>
                        </Breadcrumb>
                        -->
                        <Content :style="{padding: '24px', minHeight: '280px', background: '#fff'}">
                            <router-view/>
                        </Content>
                    </Layout>
                </Layout>
            </Layout>
        </div>
    </div>
</template>

<script>
    import {Tools} from './assets/js/common';
    import MenuItem from "iview/src/components/menu/menu-item";

    export default {
        components: {MenuItem},
        name: 'App',
        data: function () {
            return {
                menu_theme: 'dark',
                current_username: Tools.CookieHelper.getUsername(),
                menu: [
                    {
                        name: 'dashboard',
                        icon: 'ios-speedometer-outline',
                        title: 'Dashboard',
                        is_group: false
                    },
                    {
                        name: 'work-group',
                        is_group: true,
                        title: 'Work',
                        children: [
                            {
                                name: 'server_work',
                                icon: 'ios-medical-outline',
                                title: 'Server',
                                is_group: false
                            },
                            {
                                name: 'database_work',
                                icon: 'social-buffer-outline',
                                title: 'Database',
                                is_group: false
                            },
                        ]
                    },
                    {
                        name: 'schedule-group',
                        title: 'Schedule',
                        is_group: true,
                        children: [
                            {
                                name: 'job_config',
                                icon: 'ios-alarm-outline',
                                title: 'Overview',
                                is_group: false
                            },
                            {
                                name: 'update_shell_command_job',
                                icon: 'android-list',
                                title: 'Shell Job',
                                is_group: false
                            },
                            {
                                name: 'update_mixed_job',
                                icon: 'ios-color-filter-outline',
                                title: 'Mixed Job',
                                is_group: false
                            }
                        ]
                    },
                    {
                        name: 'settings-group',
                        title: 'Settings',
                        is_group: true,
                        children: [
                            {
                                name: 'user_manage',
                                icon: 'person-stalker',
                                title: 'User',
                                is_group: false
                            },
                            {
                                name: 'platform_manage',
                                icon: 'android-cloud-outline',
                                title: 'Platform',
                                is_group: false
                            },
                            {
                                name: 'server_manage',
                                icon: 'android-desktop',
                                title: 'Server',
                                is_group: false
                            },
                            {
                                name: 'server_group_manage',
                                icon: 'ios-pricetags-outline',
                                title: 'Server Group',
                                is_group: false
                            },
                            {
                                name: 'database_manage',
                                icon: 'soup-can-outline',
                                title: 'Database',
                                is_group: false
                            },
                        ]
                    }
                ],
                Breadcrumb: [
                    'Home', 'Dashboard'
                ]
            };
        },
        methods: {
            onTopBarMenuSelect: function (top_menu_name) {
                //console.log('onTopBarMenuSelect',arguments);
                if (top_menu_name === 'logout') {
                    this.logout();
                }
                else if (top_menu_name === 'slk') {
                    window.open("/#/SinriLogKeeper");
                }
                else if (top_menu_name === 'user_center') {
                    console.log("user center is not yet");
                }
            },
            logout: function () {
                console.log("logout");
                Tools.CookieHelper.setToken(null);
                this.$router.push({path: "/login"});
            },
            onMenuItemSelected: function (menu_name) {
                console.log('onMenuItemSelected', menu_name);

                for (let i = 0; i < this.$router.options.routes.length; i++) {
                    let item_a = this.$router.options.routes[i];
                    if (item_a.name === menu_name) {
                        this.$router.push({path: item_a.path});
                        return;
                    }
                    if (item_a.is_group && item_a.children) {
                        for (let j = 0; j < item_a.children.length; j++) {
                            let item_b = item_a.children[j];
                            if (item_b.name === menu_name) {
                                this.$router.push({path: item_b.path});
                                return;
                            }
                            if (item_b.is_group && item_b.children) {
                                for (let k = 0; k < item_b.children.length; k++) {
                                    let item_c = item_b.children[k];
                                    if (item_c.name === menu_name) {
                                        this.$router.push({path: item_c.path});
                                        return;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
</script>

<style>
    .layout {
        border: 1px solid #d7dde4;
        background: #f5f7f9;
        position: relative;
        border-radius: 4px;
        overflow: hidden;
    }

    .layout-logo {
        width: 100px;
        height: 30px;
        background: #5b6270;
        border-radius: 3px;
        float: left;
        position: relative;
        top: 15px;

        font-family: 'Avenir', Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-align: center;
        color: white;
        font-size: 18px;
        line-height: 30px;
    }

    .layout-nav {
        width: 310px;
        margin: 0 10px 0 auto;
    }

    .layout-breadcrumb {
        /*width: 225px;*/
        margin: 0 10px 0 auto;
        float: left;
    }
</style>
