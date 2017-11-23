// ES2016
// use https://github.com/js-cookie/js-cookie
const CookieHelper = {
    tokenKey: "infura-office-token",
    isLogin: function () {
        let token = CookieHelper.getToken();
        return !!token;
    },
    getToken: function () {
        return Cookies.get(CookieHelper.tokenKey);
    },
    setToken: function (token) {
        Cookies.set(CookieHelper.tokenKey, token);
    }
};