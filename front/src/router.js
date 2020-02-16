import Vue from "vue";
import VueRouter from "vue-router";

//页面
import Project from "./viewPage/project";
import Detail from "./viewPage/projectDetail";
// import Msg from "./viewPage/msg";
import Login from "./viewPage/login";
import Register from "./viewPage/register";
import UserCenter from "./viewPage/userCenter";

// 组件
import ProjectList from "./components/project/projectList";
import ApiPage from "./components/api/apiPage";
import CreateApi from "./components/api/createApi";
import DetailPage from "./components/detail/detailPage";
import User from "./components/user/user";
import UserManagement from "./components/user/userManagement";
import DocPage from "./components/doc/docPage";
import DocCreate from "./components/doc/docCreate";
import DocEdit from "./components/doc/docEdit";
import DocDetail from "./components/doc/docDetail";

Vue.use(VueRouter);

const router = new VueRouter({
  routes: [
    { path: "/register", component: Register, name: "register" },
    { path: "/project", component: Project },
    { path: "/login", name: "userLogin", component: Login },
    {
      path: "/",
      component: Project,
      children: [
        { path: "", component: ProjectList },
        {
          path: "userManagement",
          name: "userManagement",
          component: UserManagement
        },
        {
          path: "commonDoc",
          name: "commonDoc",
          component: DocPage,
          props: true,
          children: [
            {
              path: "docDetail/:docId",
              name: "docDetail",
              component: DocDetail,
              props: true
            },
            {
              path: "docEdit/:docId",
              name: "docEdit",
              component: DocEdit,
              props: true
            }
          ]
        },
        { path: "docCreate", name: "docCreate", component: DocCreate }
      ]
    },
    {
      path: "/detail/:id",
      name: "detail",
      component: Detail,
      props: true,
      children: [
        {
          path: "detailPage",
          name: "detailPage",
          component: DetailPage,
          props: true
        },
        {
          path: "apiPage",
          name: "apiPage",
          component: ApiPage
        },
        { path: "createApi", name: "createApi", component: CreateApi },

        { path: "user", name: "user", component: User, props: true },
        {
          path: "projectDoc",
          name: "projectDoc",
          component: DocPage,
          props: true
        }
      ]
    },
    {
      path: "/userCenter",
      component: UserCenter,
      name: "userCenter"
    }
    // { path: "/msg", name: "msg", component: Msg }
  ]
});

router.beforeEach((to, from, next) => {
  if (to.name !== "register") {
    let routerArr = ["userLogin", "register"];
    if (routerArr.indexOf(to.name) === 0) {
      next();
    } else {
      if (!Vue.prototype.userInfo) {
        next("/login");
      } else {
        next();
      }
    }
  } else {
    next();
  }
});

export default router;
