import Vue from 'vue'
import Router from 'vue-router'
import HelloWorld from '@/components/HelloWorld'
import SinriLogKeeper from '../SinriLogKeeper.vue'

import App from '../App'
import componentOfLogin from '../login.vue'
import componentOfDashboard from '@/components/Dashboard'

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
                    name: 'root_page',
                    title: 'InfuraOffice',
                    redirect: '/dashboard',
                    hidden: true,
                },
                {
                    path: '/hello-world',
                    name: 'hello-world',
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
                    name: 'work-group',
                    is_group: true,
                    title: 'Work',
                    hidden: false,
                    children: [
                        {
                            path: '/work-group/server',
                            name: 'server_work',
                            icon: 'ios-medical-outline',
                            title: 'Server',
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/work-group/database',
                            name: 'database_work',
                            icon: 'social-buffer-outline',
                            title: 'Database',
                            is_group: false,
                            hidden: false,
                        },
                    ]
                },
                {
                    path: '/schedule-group',
                    name: 'schedule-group',
                    title: 'Schedule',
                    is_group: true,
                    hidden: false,
                    children: [
                        {
                            path: '/schedule-group/overview',
                            name: 'job_config',
                            icon: 'ios-alarm-outline',
                            title: 'Overview',
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/schedule-group/shell',
                            name: 'update_shell_command_job',
                            icon: 'android-list',
                            title: 'Shell Job',
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/schedule-group/mixed',
                            name: 'update_mixed_job',
                            icon: 'ios-color-filter-outline',
                            title: 'Mixed Job',
                            is_group: false,
                            hidden: false,
                        }
                    ]
                },
                {
                    path: '/settings-group',
                    name: 'settings-group',
                    title: 'Settings',
                    is_group: true,
                    hidden: false,
                    children: [
                        {
                            path: '/settings-group/user',
                            name: 'user_manage',
                            icon: 'person-stalker',
                            title: 'User',
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/settings-group/platform',
                            name: 'platform_manage',
                            icon: 'android-cloud-outline',
                            title: 'Platform',
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/settings-group/server',
                            name: 'server_manage',
                            icon: 'android-desktop',
                            title: 'Server',
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/settings-group/server-group',
                            name: 'server_group_manage',
                            icon: 'ios-pricetags-outline',
                            title: 'Server Group',
                            is_group: false,
                            hidden: false,
                        },
                        {
                            path: '/settings-group/database',
                            name: 'database_manage',
                            icon: 'soup-can-outline',
                            title: 'Database',
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
