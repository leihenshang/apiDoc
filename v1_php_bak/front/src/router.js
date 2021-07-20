import Vue from "vue";
import VueRouter from "vue-router";
import store from "./store/main";

const originalPush = VueRouter.prototype.push
VueRouter.prototype.push = function push(location) {
  return originalPush.call(this, location).catch(err => err)
}

Vue.use(VueRouter);

const router = new VueRouter({
  routes: [
    { path: "/register", component: resolve => require(['@/view/user/register'], resolve), name: "register" },
    { path: "/login", name: "userLogin", component: resolve => require(['@/view/user/login'], resolve) },
    {
      path: "/",
      component: resolve => require(['@/view/home'], resolve),
      name: "home",
      redirect: "manage",
      children: [
        {
          path: "/manage",
          component: resolve => require(['@/view/manage'], resolve),
          name: "manage",
          redirect: "projectList",
          children: [
            {
              path: "/projectList",
              component: resolve => require(['@/view/project/projectList'], resolve),
              name: "projectList"
            },
            {
              path: "/userManager",
              component: resolve => require(['@/view/user/userManager'], resolve),
              name: "userManager"
            },
            {
              path: "/myCenter",
              component: resolve => require(['@/view/user/myCenter'], resolve),
              name: "myCenter"
            },
          ]
        },
        {
          path: "/detail/:projectId",
          component: resolve => require(['@/view/project/project'], resolve),
          props: true,
          redirect: "detailPage",
          children: [

            {
              path: "/detail/:projectId",
              name: "detailPage",
              component: resolve => require(['@/view/project/detailPage'], resolve),
              meta: { requiresAuth: true},
              props: true,
            },
            {
              path: "members",
              name: "members",
              component: resolve => require(['@/view/project/members'], resolve),
              meta: { requiresAuth: true },
              props: true,
            },
            {
              path: "fieldMapping",
              name: "fieldMapping",
              component: resolve => require(['@/view/project/fieldMapping'], resolve),
              meta: { requiresAuth: true },
              props: true,
            },
            {
              path: "dataDict",
              name: "dataDict",
              component: resolve => require(['@/view/project/dataDict'], resolve),
              meta: { requiresAuth: true },
              props: true,
            },
            {
              path: "apiPage",
              name: "apiPage",
              component: resolve => require(["@/view/project/apiPage"], resolve),
              meta: { requiresAuth: true },
              redirect: "apiPage/apiList",
              props: true,
              children: [
                {
                  path: "apiCreate",
                  name: "apiCreate",
                  component: resolve => require(["./components/api/apiCreate"], resolve),
                  meta: { requiresAuth: true},
                  props: true,
                },
                {
                  path: "/",
                  name: "apiList",
                  component: resolve => require(["./components/api/apiList"], resolve),
                  meta: { requiresAuth: true},
                  props: true,
                },
                {
                  path: "apiEdit/:apiId",
                  name: "apiEdit",
                  component: resolve => require(["./components/api/apiEdit"], resolve),
                  meta: { requiresAuth: true},
                  props: true,
                },
                {
                  path: "apiDetail/:apiId",
                  name: "apiDetail",
                  component: resolve => require(["./components/api/apiDetail"], resolve),
                  meta: { requiresAuth: true},
                  props: true,
                },
              ],
            },
            {
              path: "projectDoc",
              name: "projectDoc",
              component: resolve => require(["@/view/project/docPage"], resolve),
              props: true,
              redirect: "projectDoc/docList",
              children: [
                {
                  path: "/",
                  name: "docList",
                  component: resolve => require(["./components/doc/docList"], resolve),
                  props: true,
                  meta: { requiresAuth: true },
                },
                {
                  path: "docDetail/:docId",
                  name: "docDetail",
                  component: resolve => require(["./components/doc/docDetail"], resolve),
                  props: true,
                  meta: { requiresAuth: true },
                },
                {
                  path: "docEdit/:docId",
                  name: "docEdit",
                  component: resolve => require(["./components/doc/docEdit"], resolve),
                  props: true,
                  meta: { requiresAuth: true },
                },
                {
                  path: "docCreate",
                  name: "docCreate",
                  component: resolve => require(["./components/doc/docCreate"], resolve),
                  props: true,
                  meta: { requiresAuth: true },
                },
              ],
            },
          ],
        },
      ]
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
    Vue.axios.get("/project/get-project-operation-permission", {
      params: {
        token: ""
      }
    }).then(
      (response) => {
        response = response.data;
        if (response.code === 200) {
          store.commit("saveProjectPermission", response.data);
        }
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
