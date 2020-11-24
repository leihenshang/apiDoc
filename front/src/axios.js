import Vue from "vue";
import store from './store/main';
import router from "./router";
import axios from 'axios';

//导入配置
import conf from "./conf";

//设置默认url
axios.defaults.baseURL =  conf.apiAddr;

//从localStorage中获取用户信息
function getUserInfoByLocalStorage() {
    return JSON.parse(localStorage.getItem("userInfo"));
  }

//请求拦截
axios.interceptors.request.use( config => {
    let userInfo = getUserInfoByLocalStorage();
    if (userInfo) {
      if (config.method === "get" && config.params) {
        if ( !config.params.token) {
          config.params.token = userInfo.token;
        }
  
        //附加项目id
        if ( config.params && !config.params.projectId && store.state.project) {
          config.params.projectId = store.state.project.id;
        }
      } else if (config.method === "post" && config.data) {
        if (!config.data.token) {
          config.data.token = userInfo.token;
        }
  
        //附加项目id
        if (!config.data.projectId && store.state.project) {
          config.data.projectId = store.state.project.id;
        }
      }
    }
  
    return config;
  },error =>{
    return Promise.reject(error);
  });
  
  //响应拦截
 axios.interceptors.response.use(response => {
      let code = response.data.code;
    if (code === 34) {
      Vue.prototype.$message.error("超时,重新登录");
      localStorage.removeItem("userInfo");
      router.push("/login");
    }
  
    return response;
  }, function (error) {
    return Promise.reject(error);
  });
  
  export default axios;