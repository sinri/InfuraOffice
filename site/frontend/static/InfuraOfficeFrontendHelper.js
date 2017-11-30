// ES2016
// use https://github.com/js-cookie/js-cookie
const CookieHelper = {
    tokenKey: "infura-office-token",
    usernameKey: "infura-office-username",
    isLogin: function () {
        let token = CookieHelper.getToken();
        console.log('isLogin?', token, !!token);
        return !!token;
    },
    getToken: function () {
        return Cookies.get(CookieHelper.tokenKey);
    },
    getUsername: function () {
        return Cookies.get(CookieHelper.usernameKey);
    },
    setToken: function (token, username, life_seconds) {
        if (!life_seconds) {
            life_seconds = -1;
        }
        Cookies.set(CookieHelper.tokenKey, token, {
            expires: new Date(new Date().getTime() + life_seconds * 1000)
        });
        Cookies.set(CookieHelper.usernameKey, username, {
            expires: new Date(new Date().getTime() + life_seconds * 1000)
        });
    }
};

const callInfuraOfficeJsonAPI = function (method, url, data, callbackForOK, callbackForFail, callbackForAlways) {
    $.ajax({
        url: url,
        method: method,
        data: data,
        dataType: 'json'
    }).done((response, textStatus, jqXHR) => {
        if (response.OK === 'OK') {
            if (callbackForOK) callbackForOK(response.data);
        } else {
            if (callbackForFail) callbackForFail(response.data);
        }
    }).fail((jqXHR, textStatus, errorThrown) => {
        if (jqXHR.status === 403) {
            //goto login
            console.log("token invalidated -> logout");
            CookieHelper.setToken(null);
            window.location.href = "login.html";
        } else {
            if (callbackForFail) callbackForFail('AJAX RESPONDED ' + jqXHR.status);
        }
    }).always(() => {
        if (callbackForAlways) {
            callbackForAlways();
        }
    })
};