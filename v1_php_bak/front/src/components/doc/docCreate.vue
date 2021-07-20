<template>
  <div class="doc-create">
    <div class="btn">
      <div class="left">
        <el-button plain @click="$router.go(-1)">返 回</el-button>
      </div>
      <div class="right">
        <el-button type="success" plain @click="createDoc()">保 存</el-button>
        <el-button type="success" plain @click="createDoc(true)">继续添加</el-button>
      </div>
    </div>
    <div class="doc-box">
      <DocInfo :groupList="this.groupList" v-on:update-info="updateInfo($event)" />
      <mavon-editor v-model="content" />
    </div>
  </div>
</template>
<script>
import DocInfo from "./units/docInfo";

const CODE_OK = 200;

export default {
  components: {
    DocInfo,
  },
  name: "docCreate",
  data() {
    return {
      title: "",
      content: "",
      description: "",
      groupId: null,
      groupIdSecond:null,
      groupList: [],
    };
  },
  methods: {
    updateInfo(val) {
      this.title = val.title;
      this.groupId = val.groupId;
      this.groupIdSecond = val.groupIdSecond;
    },
    initDocCreate() {
      this.title = this.content = this.groupId = "";
    },
    //获取分组列表
    getGroup(pageSize = 10, curr = 1, projectId) {
      this.$http
        .get("/group/list", {
          params: {
            cp: curr,
            type: 3,
            ps: pageSize,
            projectId: projectId ? projectId : 0,
          },
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              if (response.data) {
                this.groupList = response.data;
              }
            } else {
              this.$message.error(
                "获取数据-操作失败!" + !response.msg ? response.msg : ""
              );
            }
          },
          () => {
            this.$message.error("获取数据-操作失败!");
          }
        );
    },
    //创建文档
    createDoc(isAgain) {
      if (!this.groupId) {
        this.$message.error("请选择分组");
        return;
      }

      if (this.title.length < 1) {
        this.$message.error("标题不能为空");
      }

      this.$confirm("要保存吗？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      }).then(() => {
        this.$http
          .post("/doc/create", {
            title: this.title,
            content: this.content,
            group_id: this.groupIdSecond ?  this.groupIdSecond : this.groupId,
            project_id: this.$route.params.projectId,
          })
          .then((res) => {
            res = res.data;
            if (res.code === CODE_OK) {
              this.$message.success("创建成功！");

              if (isAgain === true) {
                this.initDocCreate();
              } else {
                this.$router.go(-1);
              }
            } else {
              this.$message.error(res.msg);
            }
          });
      });
    },
  },
  created() {
    this.getGroup(100, 1, this.$route.params.projectId);
  },
};
</script>
<style lang="scss" scoped>
.doc-create {
  .doc-box {
    margin: 20px auto;
    min-height: 1200px;
  }

  .v-note-wrapper {
    margin: 10px;
    height: 1000px;
  }

  .btn {
    width: 100%;
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    .right {
      padding-right: 20px;
    }
  }
}
</style>