import Vue from "vue";
import store from './store/main';
import App from "./App.vue";
//自定义路由配置
import router from "./router";
//复制到剪贴板
import VueClipboard from "vue2-clipboard";
//axios
import VueAxios from 'vue-axios'
import axios from './axios'
//markdown组件
import mavonEditor from "mavon-editor";
import "mavon-editor/dist/css/index.css";
//element ui
import ElementUI from "element-ui";
import "element-ui/lib/theme-chalk/index.css";


Vue.config.productionTip = false;
Vue.use(VueAxios, axios)
Vue.use(mavonEditor);
//medium / small / mini
Vue.use(ElementUI,{ size: 'medium', zIndex: 3000 });
Vue.use(VueClipboard);


(function () {
  axios.get('/config.json').then((res) => {
    Vue.prototype.BASE_URL = res.data.BASE_URL;

    //实例化Vue
    new Vue({
      router,
      store,
      render: (h) => h(App),
    }).$mount("#app");

  });
})();
