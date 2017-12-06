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

const AliyunRegionDictionary = [
    {label: '青岛 / 华北1 / REGION_ID_CN_NORTH_1', key: "cn-qingdao"},
    {label: '北京 / 华北2 / REGION_ID_CN_NORTH_2', key: "cn-beijing"},
    {label: '张家口 / 华北3 / REGION_ID_CN_NORTH_3', key: "cn-zhangjiakou"},
    {label: '杭州 / 华东1 / REGION_ID_CN_EAST_1', key: "cn-hangzhou"},
    {label: '上海 / 华东2 / REGION_ID_CN_EAST_2', key: "cn-shanghai"},
    {label: '深圳 / 华南1 / REGION_ID_CN_SOUTH_1', key: "cn-shenzhen"},
    {label: '香港 / REGION_ID_HK', key: "cn-hongkong"},
    {label: '新加坡 / 亚太东南1 / REGION_ID_AP_SOUTHEAST_1', key: "ap-southeast-1"},
    {label: '悉尼 / 亚太东南2 / REGION_ID_AP_SOUTHEAST_2', key: "ap-southeast-2"},
    {label: '吉隆坡 / 亚太东南3 / REGION_ID_AP_SOUTHEAST_3', key: "ap-southeast-3"},
    {label: '东京 / 亚太东北1 / REGION_ID_AP_NORTHEAST_1', key: "ap-northeast-1"},
    {label: '硅谷 / 美西1 / REGION_ID_US_WEST_1', key: "us-west-1"},
    {label: '弗吉尼亚 / 美东1 / REGION_ID_US_EAST_1', key: "us-east-1"},
    {label: '法兰克福 / 欧洲中部1 / REGION_ID_EU_CENTRAL_1', key: "eu-central-1"},
    {label: '迪拜 / 中东东部1 / REGION_ID_ME_EAST_1', key: "me-east-1"},
];