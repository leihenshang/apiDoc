<template>
  <div class="home-container">
    <el-container style="height:100%">
      <el-header style="padding:0">
        <topBar />
      </el-header>
      <el-main>
        <router-view></router-view>
      </el-main>
    </el-container>
  </div>
</template>
<script>
import TopBar from "@/components/common/topBar";
export default {
  name: "home",
  computed: {
    myRoute: function () {
      let route = [
        {
          title: "项目列表",
          route: "/projectList",
          icon: "el-icon-s-fold",
        },
      ];

      if (this.$store.state.userInfo && this.$store.state.userInfo.type > 1) {
        route.push({
          title: "用户管理",
          route: "/userManager",
          icon: "el-icon-user",
        });
      }

      if (this.$store.state.userInfo.id) {
        return route;
      }

      return [];
    },
  },
  components: { topBar: TopBar },
};
</script>
<style lang="scss" scoped>
</style>