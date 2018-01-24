import Vue from 'vue'
import Router from 'vue-router'

import HelloWorld from '@/components/HelloWorld'
import SinriLogKeeper from '../SinriLogKeeper.vue'

import App from '../App'
import componentOfLogin from '../login.vue'
import componentOfDashboard from '@/components/Dashboard'
import componentOfServerInWork from '@/components/work/ServerWork'
import componentOfDatabaseInWork from '@/components/work/DatabaseWork'
import componentOfOverviewInSchedule from '@/components/schedule/Overview'
import componentOfShellJobInSchedule from '@/components/schedule/ShellJob'
import componentOfMixedJobInSchedule from '@/components/schedule/MixedJob'
import componentOfUsersInSettings from '@/components/settings/Users'
import componentOfPlatformsInSettings from '@/components/settings/Platforms'
import componentOfServersInSettings from '@/components/settings/Servers'
import componentOfServerGroupsInSettings from '@/components/settings/ServerGroups'
import componentOfDatabasesInSettings from '@/components/settings/Databases'

Vue.use(Router);

export default new Router({
    routes: [
        {
            path: '/',
            name: 'InfuraOffice',
            title: 'InfuraOffice',
            component: App,
            is_group: true,
            hidden: false,
            children: [
                {
                    path: '/',
                    name: 'Root',
                    title: 'InfuraOffice',
                    redirect: '/dashboard',
                    hidden: true,
                },
                {
                    path: '/hello-world',
                    name: 'HelloWorld',
                    title: 'Hello World',
                    component: HelloWorld,
                    is_group: false,
                    hidden: true
                },
                {
                    path: '/dashboard',
                    name: 'Dashboard',
                    title: 'Dashboard',
                    icon: 'ios-speedometer-outline',
                    component: componentOfDashboard,
                    is_group: false,
                    hidden: false,
                },
                {
                    path: '/work-group',
                    name: 'Work',
                    is_group: true,
                    title: 'Work',
                    component: {template: '<router-view></router-view>'},
                    hidden: false,
                    children: [
                        {
                            path: '/work-group/server',
                            name: 'Server Work',
                            icon: 'ios-medical-outline',
                            title: 'Server',
                            component: componentOfServerInWork,
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/work-group/database',
                            name: 'Database Work',
                            icon: 'social-buffer-outline',
                            title: 'Database',
                            component: componentOfDatabaseInWork,
                            is_group: false,
                            hidden: false,
                        },
                    ]
                },
                {
                    path: '/schedule-group',
                    name: 'Schedule',
                    title: 'Schedule',
                    component: {template: '<router-view></router-view>'},
                    is_group: true,
                    hidden: false,
                    children: [
                        {
                            path: '/schedule-group/overview',
                            name: 'Schedule List',
                            icon: 'ios-alarm-outline',
                            title: 'Overview',
                            component: componentOfOverviewInSchedule,
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/schedule-group/shell',
                            name: 'Shell Job',
                            icon: 'android-list',
                            title: 'Shell Job',
                            component: componentOfShellJobInSchedule,
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/schedule-group/mixed',
                            name: 'Mixed Job',
                            icon: 'ios-color-filter-outline',
                            title: 'Mixed Job',
                            component: componentOfMixedJobInSchedule,
                            is_group: false,
                            hidden: false,
                        }
                    ]
                },
                {
                    path: '/settings-group',
                    name: 'Settings',
                    title: 'Settings',
                    component: {template: '<router-view></router-view>'},
                    is_group: true,
                    hidden: false,
                    children: [
                        {
                            path: '/settings-group/user',
                            name: 'Users',
                            icon: 'person-stalker',
                            title: 'Users',
                            component: componentOfUsersInSettings,
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/settings-group/platform',
                            name: 'Platforms',
                            icon: 'android-cloud-outline',
                            title: 'Platforms',
                            component: componentOfPlatformsInSettings,
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/settings-group/server',
                            name: 'Servers',
                            icon: 'android-desktop',
                            title: 'Servers',
                            component: componentOfServersInSettings,
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/settings-group/server-group',
                            name: 'Server Groups',
                            icon: 'ios-pricetags-outline',
                            title: 'Server Groups',
                            component: componentOfServerGroupsInSettings,
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/settings-group/database',
                            name: 'Databases',
                            icon: 'soup-can-outline',
                            title: 'Databases',
                            component: componentOfDatabasesInSettings,
                            is_group: false,
                            hidden: false,
                        },
                    ]
                }
            ]
        },
        {
            path: '/login',
            name: 'login',
            component: componentOfLogin,
            hidden: true
        },
        {
            path: '/SinriLogKeeper',
            name: 'SinriLogKeeper',
            component: SinriLogKeeper,
            hidden: true
        }
    ]
})
