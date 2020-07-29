import Vue from "vue";
import VueRouter from "vue-router";

//项目相关
import Home from "./viewPage/home";
import Detail from "./viewPage/project";
// import Msg from "./viewPage/msg";
import ProjectList from "./components/project/projectList";
import FieldMapping from "./components/project/fieldMapping";
import CommonlyParams from "./components/project/commonlyParams";
import EnvConf from "./components/project/envConf";

//api相关操作
import ApiPage from "./components/api/apiPage";
import apiCreate from "./components/api/apiCreate";
import DetailPage from "./components/project/detailPage";
import ApiList from "./components/api/apiList";
import ApiDetail from "./components/api/apiDetail";
import ApiEdit from "./components/api/apiEdit";

//文档相关操作
import DocPage from "./components/doc/docPage";
import DocCreate from "./components/doc/docCreate";
import DocEdit from "./components/doc/docEdit";
import DocDetail from "./components/doc/docDetail";
import DocList from "./components/doc/docList";

//用户相关操作
import Login from "./components/user/login";
import Register from "./components/user/register";
import MyCenter from "./components/user/myCenter";
import UserManager from "./components/user/userManager";

//bug
import BugHome from "./components/bug/home";

import store from "./store/main";

const originalPush = VueRouter.prototype.push
VueRouter.prototype.push = function push(location) {
  return originalPush.call(this, location).catch(err => err)
}

Vue.use(VueRouter);

const router = new VueRouter({
  routes: [
    { path: "/register", component: Register, name: "register" },
    { path: "/login", name: "userLogin", component: Login },
    {
      path: "/detail/:id",
      component: Detail,
      props: true,
      children: [

        {
          path: "",
          name: "detailPage",
          component: DetailPage,
          meta: { requiresAuth: true },
          props: true,
        },
        {
          path: "bugHome",
          name: "bugHome",
          component: BugHome,
          meta: { requiresAuth: true },
          props: true,
        },
        {
          path: "fieldMapping",
          name: "fieldMapping",
          component: FieldMapping,
          meta: { requiresAuth: true },
          props: true,
        },
        {
          path: "commonlyParams",
          name: "commonlyParams",
          component: CommonlyParams,
          meta: { requiresAuth: true },
          props: true,
        },
        {
          path: "envConf",
          name: "envConf",
          component: EnvConf,
          meta: { requiresAuth: true },
          props: true,
        },
        {
          path: "apiPage",
          name: "apiPage",
          component: ApiPage,
          meta: { requiresAuth: true },
          redirect: "apiPage/apiList/0",
          props: true,
          children: [
            {
              path: "apiCreate/:groupId",
              name: "apiCreate",
              component: apiCreate,
              meta: { requiresAuth: true },
              props: true,
            },
            {
              path: "apiList/:groupId",
              name: "apiList",
              component: ApiList,
              meta: { requiresAuth: true },
              props: true,
            },
            {
              path: "apiEdit/:apiId",
              name: "apiEdit",
              component: ApiEdit,
              meta: { requiresAuth: true },
              props: true,
            },
            {
              path: "apiDetail/:apiId",
              name: "apiDetail",
              component: ApiDetail,
              meta: { requiresAuth: true },
              props: true,
            },
          ],
        },
        {
          path: "projectDoc",
          name: "projectDoc",
          component: DocPage,
          props: true,
          redirect: "projectDoc/docList/0",
          children: [
            {
              path: "docList/:groupId",
              name: "docList",
              component: DocList,
              props: true,
              meta: { requiresAuth: true },
            },
            {
              path: "docDetail/:docId",
              name: "docDetail",
              component: DocDetail,
              props: true,
              meta: { requiresAuth: true },
            },
            {
              path: "docEdit/:docId",
              name: "docEdit",
              component: DocEdit,
              props: true,
              meta: { requiresAuth: true },
            },
            {
              path: "docCreate",
              name: "docCreate",
              component: DocCreate,
              props: true,
              meta: { requiresAuth: true },
            },
          ],
        },
      ],
    },
    {
      path: "/",
      component: Home,
      redirect: "projectList",
      children: [
        { path: "projectList", component: ProjectList, name: "projectList" },
        {
          path: "myCenter",
          component: MyCenter,
          name: "myCenter",
        },
        {
          path: "userManager",
          component: UserManager,
          name: "userManager",
        },
      ],
    },
  ],
});




router.beforeEach((to, from, next) => {

  let userInfo = JSON.parse(localStorage.getItem("userInfo"));
  if (userInfo && from.fullPath === '/') {
    store.commit("saveUserInfo", userInfo);
  }

  if (
    to.matched.some((record) => record.meta.requiresAuth) && userInfo
  ) {
    Vue.http.get(Vue.prototype.apiAddress + "/project/get-project-operation-permission").then(
      (response) => {
        response = response.body;
        if (response.code === 200) {
          store.commit("saveProjectPermission", response.data);
        }
      },
      (res) => {
        let response = res.body;
        Vue.$message.error(
          "获取项目权限信息失败!" + !response.msg ? response.msg : ""
        );
      }
    );
  }

  let routerArr = ["userLogin", "register"];
  if (routerArr.indexOf(to.name) === -1 && !userInfo) {
    next("/login");
  } else {
    next();
  }

});


export default router;
