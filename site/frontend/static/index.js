var vueIndex = null;

Vue.component(GeneralComponentOfInfuraServerSelect.componentDefinition.name, GeneralComponentOfInfuraServerSelect.componentDefinition);
Vue.component(GeneralComponentOfServerAndGroupSelect.componentDefinition.name, GeneralComponentOfServerAndGroupSelect.componentDefinition);

$(document).ready(function () {
    if (!CookieHelper.isLogin()) {
        window.location.href = "login.html";
        return;
    }

    // hljs.initHighlightingOnLoad();
    // $('.highlighted_box textarea').each(function(i, block) {
    //     hljs.highlightBlock(block);
    // });

    vueIndex = new Vue({
        el: '#app_index',
        data: {
            current_username: CookieHelper.getUsername(),
            //for menu
            menu_theme: 'dark',
            menu_full: true,
            menu_item_selected: 'dashboard',
            //for components
            dashboard_data: {},
            user_manage_data: {
                user_fields: [],
                users: []
            }
        },
        components: {
            dashboard: handlerOfIndexComponentDashboard.componentDefinition,
            user_manage: handlerOfIndexComponentUserManage.componentDefinition,
            platform_manage: handlerOfIndexComponentPlatformManage.componentDefinition,
            server_manage: handlerOfIndexComponentServerManage.componentDefinition,
            server_group_manage: handlerOfIndexComponentServerGroupManage.componentDefinition,
            database_manage: handlerOfIndexComponentDatabaseManage.componentDefinition,
            server_work: handlerOfIndexComponentServerWork.componentDefinition,
            database_work: handlerOfIndexComponentDatabaseWork.componentDefinition,
            job_config: handlerOfIndexComponentJobConfig.componentDefinition,
            update_shell_command_job: handlerOfIndexComponentUpdateShellCommandJob.componentDefinition,
            update_explode_log_job: handlerOfIndexComponentUpdateExplodeLogJob.componentDefinition,
            update_remove_antiquity_job: handlerOfIndexComponentUpdateRemoveAntiquityJob.componentDefinition,
        },
        methods: {
            logout: function () {
                console.log("logout");
                CookieHelper.setToken(null);
                window.location.href = "login.html";
            },
            on_menu_item_selected: function (menu_item_name) {
                console.log('menu item selected', menu_item_name);
                if (!CookieHelper.isLogin()) {
                    window.location.href = "login.html";
                    return;
                }
                if (menu_item_name === 'menu_full_switch') {
                    this.menu_full = !this.menu_full;
                } else {
                    let current_menu_item_selected = this.menu_item_selected;
                    this.menu_item_selected = menu_item_name;
                }
                console.log('updated...', this.menu_item_selected);
            }
        },
        mounted: function () {
            //$('body').css({'height':$(window).height()});
            $('#main_div').css({
                'height': $(window).height() - 50,
            });
            $('#menu_pane').css({
                'height': $(window).height() - 50
            });
            $('#work_div').css({
                'height': $(window).height() - 50
            });
        }
    });

    vueIndex.$Loading.config({
        color: '#FEFEFE',
        failedColor: '#f0030a',
        height: 2
    });
});
