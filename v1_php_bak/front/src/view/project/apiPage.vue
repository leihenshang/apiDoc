<template>
  <div class="api-page">
    <div class="group-wrapper" v-show=" $route.name == 'apiList'  ">
      <group :type="type" v-on:change-group="changeGroup">全部接口</group>
    </div>

    <div class="api-wrapper">
      <div class="btn-wrapper">
        <div v-show=" $route.name == 'apiList'  ">
          <el-input placeholder="请输入标题" v-model="keyword" style="width:30%" clearable>
            <el-button size slot="append" icon="el-icon-search" @click="searchApi()">搜 索</el-button>
          </el-input>
          <el-button style="margin:0 0 0 10px;" @click="addApi()" :disabled="!controlShow()">+创建api</el-button>
        </div>
      </div>
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
import group from "@/components/project/group";
import controlShow from "@/mixins/controlShow";

export default {
  name: "apiPage",
  props: {},
  created() {},
  data() {
    return {
      type: 1,
      keyword: "",
    };
  },
  methods: {
    searchApi() {
      this.$router
        .push({
          name: "apiList",
          params: { groupId: 0 },
          query: { keyword: this.keyword },
        })
        .catch(() => {});
    },
    //更改分组
    changeGroup(id) {
      this.$router.push(
        { name: "apiList", query: { groupId: id } },
        () => {
          return;
        },
        () => {
          return;
        }
      );
    },
    //调整添加api
    addApi() {
      this.$router.push({
        name: "apiCreate",
        query: { groupId: this.$route.query.groupId },
      });
    },
  },
  components: {
    group,
  },
  mixins: [controlShow],
};
</script>

<style lang="scss" scoped>
.api-page {
  width: 100%;
  display: flex;

  .group-wrapper {
    width: 260px;
    height: auto;
    border: 1px solid #e5e5e5;
    flex: 0.2;
  }

  .api-wrapper {
    flex: 1;
    padding-left: 10px;
    .btn-wrapper {
      padding: 10px 0;
      overflow: hidden;
    }
  }
}
</style>
