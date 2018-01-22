<template>
    <section>
        <Row>
            <i-col span="4">&nbsp;</i-col>
            <i-col span="16">
                <Row>
                    <i-col span="24">
                        <h1>Login - Infura Office</h1>
                    </i-col>
                    <i-col span="24">
                        <i-input v-model="username">
                            <span slot="prepend">Username</span>
                        </i-input>
                    </i-col>
                    <i-col span="24">
                        <i-input v-model="password" type="password">
                            <span slot="prepend">Password</span>
                        </i-input>
                    </i-col>
                    <i-col span="24">
                        Note:
                        One account could only have one session,
                        i.e.
                        if you login now, the previous session of this account would be revoked.
                    </i-col>
                    <i-col span="24" style="position: relative;">
                        <Spin v-if="is_loading" fix></Spin>
                        <i-button type="primary" v-else v-on:click="login">Login</i-button>
                    </i-col>
                    <i-col span="24" v-if="has_error">
                        <Alert type="warning" closable @on-close="alert_closed">
                            {{ error_message }}
                        </Alert>
                    </i-col>
                </Row>
            </i-col>
            <i-col span="4">&nbsp;</i-col>
        </Row>
    </section>
</template>

<script>
    import {Tools} from './assets/js/common';

    export default {
        name: "login",
        data: function () {
            return {
                username: '',
                password: '',
                is_loading: false,
                has_error: false,
                error_message: '',
            }
        },
        methods: {
            login: function () {
                console.log("login");

                this.is_loading = true;

                Tools.callInfuraOfficeJsonAPI(
                    "post",
                    '/api/LoginController/loginWithUsernameAndPassword',
                    {
                        username: this.username,
                        password: this.password
                    },
                    (data) => {
                        let token = data.token;
                        let life_seconds = data.life;
                        let username = data.username;
                        let last_login_time = data.last_login_time;
                        let last_login_ip = data.last_login_ip;

                        let last_login_text = '';
                        if (last_login_time > 0) {
                            let t = new Date(last_login_time);
                            last_login_text = "Your last login was from " + last_login_ip + " on " + t + ".";
                        }

                        Tools.CookieHelper.setToken(token, username, life_seconds);

                        this.has_error = false;
                        this.error_message = '';

                        this.$Message.success({
                            content: 'User validated. ' + last_login_text + " Soon open main page.",
                            duration: 1,
                            onClose: () => {
                                if (data.role === 'SLK_READER') {
                                    window.location.href = "/slk"
                                } else {
                                    this.$router.push({path: "/"});
                                }
                            }
                        });
                    },
                    (failure, error) => {
                        this.has_error = true;
                        this.error_message = failure;
                    },
                    () => {
                        this.is_loading = false;
                    }
                );
                /*
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
                                        duration: 1,
                                        onClose: () => {
                                            if (response.data.role === 'SLK_READER') {
                                                window.location.href = "slk.html"
                                            } else {
                                                window.location.href = "index.html";
                                            }
                                        }
                                    });
                                }).fail((err) => {
                                    console.log(err);
                                    this.has_error = true;
                                    this.error_message = "HTTP STATUS CODE: " + err.status;
                                }).always(() => {
                                    this.is_loading = false;
                                })
                                */
            },
            alert_closed: function () {
                this.has_error = false;
                this.error_message = '';
            }
        },
        mounted: function () {
            console.log("process.env.NODE_ENV", process.env.NODE_ENV);
        }
    }
</script>

<style scoped>
    div {
        margin: 5px;
        text-align: center;
    }
</style>