var vueIndex = null;

$(document).ready(function () {
    if (!CookieHelper.isLogin()) {
        window.location.href = "login.html";
        return;
    }
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
            user_manage: handlerOfIndexComponentUserManage.componentDefinition,
            server_manage: handlerOfIndexComponentServerManage.componentDefinition,
            database_manage: handlerOfIndexComponentDatabaseManage.componentDefinition,
            server_work: handlerOfIndexComponentServerWork.componentDefinition,
            database_work: handlerOfIndexComponentDatabaseWork.componentDefinition
        },
        methods: {
            logout: function () {
                console.log("logout");
                CookieHelper.setToken(null);
                window.location.href = "login.html";
            },
            on_menu_item_selected: function (menu_item_name) {
                console.log('menu item selected', menu_item_name);
                if (menu_item_name === 'menu_full_switch') {
                    this.menu_full = !this.menu_full;
                } else {
                    let current_menu_item_selected = this.menu_item_selected;
                    this.menu_item_selected = menu_item_name;
                    // switch (menu_item_name){
                    //     case 'dashboard':
                    //         handleIndexComponentDashboard();
                    //         break;
                    //     // case 'user_manage':
                    //     //     handlerOfIndexComponentUserManage.handleIndexComponentUserManage();
                    //     //     break;
                    //     default:
                    //         console.log('unsupported menu item name');
                    //         this.menu_item_selected = current_menu_item_selected;
                    //         break;
                    // }
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
