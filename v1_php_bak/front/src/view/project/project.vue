<template>
  <div class="detail">
    <el-row :gutter="10">
      <el-col :span="3">
        <el-menu :default-active="selected" ref="menu" class="el-menu" router>
          <el-menu-item :index="item.route" v-for="item in insideRoute" :key="item.id">
            <i :class="item.icon ? item.icon : 'el-icon-setting'"></i>
            <span slot="title">{{ item.title }}</span>
          </el-menu-item>
        </el-menu>
      </el-col>
      <el-col :span="21">
        <transition name="el-fade-in-linear" mode="out-in" appear>
          <router-view></router-view>
        </transition>
      </el-col>
    </el-row>
  </div>
</template>

<script>
export default {
  name: "project",
  props: {
    id: String,
  },
  mounted() {
    this.getSelectedPath()
  },
  watch:{
    $route() {
      this.getSelectedPath()
    }
  },
  data() {
    let menu = [
      {
        title: "项目概况",
        route: "/detail/" + this.$route.params.projectId,
        icon: "el-icon-s-platform",
      },
      {
        title: "API接口",
        route: "/detail/" + this.$route.params.projectId + "/apiPage",
        icon: "el-icon-s-promotion",
      },
      {
        title: "文档",
        route: "/detail/" + this.$route.params.projectId + "/projectDoc",
        icon: "el-icon-reading",
      },
    ];

    let mmanagerMenu = [
      {
        title: "字段映射",
        route: "/detail/" + this.$route.params.projectId + "/fieldMapping",
        icon: "el-icon-coin",
      },
      {
        title: "成员管理",
        route: "/detail/" + this.$route.params.projectId + "/members",
        icon: "el-icon-user",
      },
        {
        title: "数据字典",
        route: "/detail/" + this.$route.params.projectId + "/dataDict",
        icon: "el-icon-setting",
      },
    ];

    //如果不是普通用户，加入管理菜单
    if (this.$store.state.userInfo.type != 1) {
      menu.push(...mmanagerMenu);
    }

    return {
      projectData: {},
      insideRoute: menu,
      selected:''
    };
  },
  methods: {
    getSelectedPath(){
      let parent = this.$route.matched[this.$route.matched.length-1].parent.path
      let paramsObj = this.$route.params
      for (let key in paramsObj){
        parent = parent.replace(`:${key}`,paramsObj[key])
      }
      this.$route.matched.length >3? this.selected = parent: this.selected=this.$route.path
    }
  }
};
</script>

<style lang="scss" scoped>
.el-menu {
  min-height: 700px;
}
</style>
