import Vuex from 'vuex';
import Vue from "vue";
Vue.use(Vuex);
const store = new Vuex.Store({
  state: {
    count: 0,
    userInfo: {},
    project: {},
    projectPermission: 4,
  },
  mutations: {
    increment(state) {
      state.count++;
    },
    //用户信息
    saveUserInfo(state, user) {
      state.userInfo = user;
    },
    //项目信息
    saveProject(state, project) {
      state.project = project;
    },
    //用户对项目的操作权限
    saveProjectPermission(state, permission) {
      state.projectPermission = permission;
    },
  },
});



export default store;