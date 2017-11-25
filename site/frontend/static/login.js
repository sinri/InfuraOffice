var vueLogin = null;

$(document).ready(function () {
    vueLogin = new Vue({
        el: '#app_login',
        data: {
            username: '',
            password: '',
            is_loading: false,
            has_error: false,
            error_message: '',
        },
        methods: {
            login: function () {
                console.log("login");

                this.is_loading = true;

                $.ajax({
                    url: '../api/LoginController/loginWithUsernameAndPassword',
                    method: 'post',
                    data: {
                        username: this.username,
                        password: this.password
                    },
                    dataType: 'json'
                }).done((response) => {
                    console.log(response);
                    if (response.code !== 'OK') {
                        this.has_error = true;
                        this.error_message = response.data;
                        return;
                    }

                    let token = response.data.token;
                    let life_seconds = response.data.life;
                    let username = response.data.username;
                    let last_login_time = response.data.last_login_time;
                    let last_login_ip = response.data.last_login_ip;

                    let last_login_text = '';
                    if (last_login_time > 0) {
                        let t = new Date(last_login_time);
                        last_login_text = "Your last login was from " + last_login_ip + " on " + t + ".";
                    }

                    CookieHelper.setToken(token, username, life_seconds);

                    this.has_error = false;
                    this.error_message = '';

                    this.$Message.success({
                        content: 'User validated. ' + last_login_text + " Soon open main page.",
                        duration: 2,
                        onClose: () => {
                            window.location.href = "index.html";
                        }
                    });
                }).fail((err) => {
                    console.log(err);
                    this.has_error = true;
                    this.error_message = "HTTP STATUS CODE: " + err.status;
                }).always(() => {
                    this.is_loading = false;
                })
            },
            alert_closed: function () {
                this.has_error = false;
                this.error_message = '';
            }
        },
    });
    console.log(vueLogin);
});