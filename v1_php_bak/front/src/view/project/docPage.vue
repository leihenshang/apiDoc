<template>
  <div class="doc-page">
    <div class="group-wrapper" v-show=" $route.name == 'docList'">
      <group :type="type" v-on:change-group="changeGroup" :showIsEdit=" true">全部</group>
    </div>
    <div class="doc-wrapper">
      <div class="btn-wrapper" v-show=" $route.name == 'docList' ">
        <div class="input" style="display:inline-block;">
          <el-input
            placeholder="请输入标题"
            v-model="keyword"
            class="input-with-select"
            style="width:300px"
            clearable
          ></el-input>
          <el-button icon="el-icon-search" @click="searchDoc()" style="margin-left:5px;">搜索</el-button>
          <el-button
            icon="el-icon-plus"
            @click="addDoc()"
            style="margin-right:5px"
            :disabled="!controlShow()"
          >创 建</el-button>
        </div>
      </div>
      <router-view />
    </div>
  </div>
</template>

<script>
import controlShow from "@/mixins/controlShow";
import group from "@/components/project/group";

export default {
  name: "docPage",
  props: {
    id: String,
  },
  created() {},
  //默认数据
  data() {
    return {
      keyword: "",
      type: 3,
    };
  },
  methods: {
    searchDoc() {
      this.$router
        .push({
          name: "docList",
          params: { groupId: 0 },
          query: { keyword: this.keyword },
        })
        .catch(() => {});
    },
    changeGroup(id) {
      id = id ? id : 0;
      this.$router.push(
        { name: "docList", query: { groupId: id } },
        () => {
          return;
        },
        () => {
          return;
        }
      );
    },
    //添加文档
    addDoc() {
      this.$router.push({ name: "docCreate" });
    },
  },
  components: {
    group,
  },
  mixins: [controlShow],
};
</script>

<style lang="scss" scoped>
.doc-page {
  width: 100%;
  height: calc(100% - 21px);
  display: flex;

  .group-wrapper {
    width: 260px;
    height: auto;
    border: 1px solid #e5e5e5;
    flex: 0.2;
  }

  .doc-wrapper {
    flex: 1;
    padding-left: 10px;
    .btn-wrapper {
      padding: 10px 0;
      overflow: hidden;
    }
  }
}
</style>
