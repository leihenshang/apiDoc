<template>
  <div class="members">
    <!-- 用户选择 -->
    <div class="user-search">
      <el-select
        size="medium"
        v-model="keyword"
        filterable
        remote
        placeholder="输入昵称/邮箱搜索用户"
        :remote-method="getUserList"
        :loading="loading"
        @change="addProjectUser($event)"
        clearable
      >
        <el-option
          v-for="item in searchUserList"
          :key="item.id"
          :label="item.nick_name"
          :value="item"
        >
          <span style="float: right">添加</span>
          <span style="float: left; color: #8492a6; font-size: 13px">{{ item.nick_name }}</span>
        </el-option>
      </el-select>
         <el-divider></el-divider>
    </div>
    <!-- 用户选择-结束 -->

    <!-- 项目用户 -->
    <div class="all-user" v-loading="loading">
      <el-table :data="userList" stripe style="width: 100%" border>
        <el-table-column prop="name" label="账号" width="180"></el-table-column>
        <el-table-column prop="state" label="状态" width="180">
          <template slot-scope="scope">{{ transferState(scope.row.state) }}</template>
        </el-table-column>
        <el-table-column prop="is_leader" label="用户类型">
          <template slot-scope="scope">{{scope.row.is_leader == 1 ? '项目管理者' : '项目成员' }}</template>
        </el-table-column>
        <el-table-column prop="nick_name" label="昵称"></el-table-column>
        <el-table-column prop="email" label="邮箱"></el-table-column>
        <el-table-column prop="email" label="权限">
          <template slot-scope="scope">{{scope.row.permission == 6 ? '读/写':'只读'}}</template>
        </el-table-column>
        <el-table-column align="center" label="操作">
          <template slot-scope="scope">
            <el-dropdown placement="bottom" trigger="click" @command="handleCommand">
              <el-button type="text">权限设置</el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item
                  :command="{action:'setPermission',data:scope.row}"
                >设置{{scope.row.permission == 4 ? '读/写':'只读'}}</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
            <el-button type="text" style="margin-left:10px" @click="quitProject(scope.row)">移出项目</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <!-- 项目用户 -->
  </div>
</template>

<script>
const CODE_OK = 200;

export default {
  name: "members",
  data() {
    return {
      loading: false,
      keyword: "",
      searchUserList: [],
      userList: [],
    };
  },
  created() {
    this.getProjectUserList();
  },
  methods: {
    //转换状态字符串显示
    transferState(state) {
      state = parseInt(state);
      let str = "";
      //1正常2禁用
      switch (state) {
        case 1:
          str = "正常";
          break;
        case 2:
          str = "禁用";
          break;

        default:
          str = "unkonwn";
          break;
      }

      return str;
    },
    handleCommand(val) {
      switch (val.action) {
        case "setPermission":
          this.setPermission(val.data);
          break;

        default:
          break;
      }
    },
    //获取项目用户列表
    getProjectUserList() {
      this.$http
        .get("/project/project-user", {
          params: {
            id: this.$route.params.projectId,
          },
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              this.userList = response.data;
            }
          },
          (res) => {
            let response = res.data;
            this.$message.error(
              "获取数据-操作失败!" + !response.msg ? response.msg : ""
            );
          }
        );
    },

    //增加项目用户
    addProjectUser(val) {
      this.$confirm(
        "将用户" + val.nick_name + "加入该项目, 是否继续?",
        "提示",
        {
          confirmButtonText: "确定",
          cancelButtonText: "取消",
          type: "warning",
        }
      )
        .then(() => {
          this.$http
            .post("/project/add-user", {
              user_id: val.id,
              project_id: this.$route.params.projectId,
            })
            .then(
              (response) => {
                response = response.data;
                if (response.code === CODE_OK) {
                  this.getProjectUserList();
                  this.$message.success("操作成功");
                } else {
                  this.$message.error(response.msg);
                }
              },
              () => {
                this.$message.error("操作失败!");
              }
            );
        })
        .catch(() => {});
    },
    //设置用户对项目的读写权限
    setPermission(val) {
      let userPermission = val.permission == 4 ? 6 : 4;
      userPermission = Number.parseInt(userPermission);
      this.$confirm("将用户" + val.nick_name + "修改权限, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$http
            .post("/project/set-permission", {
              userId: val.id,
              permission: userPermission,
              projectId: this.$route.params.projectId,
            })
            .then(
              (response) => {
                response = response.data;
                if (response.code === CODE_OK) {
                  this.getProjectUserList();
                  this.$message.success("操作成功!");
                } else {
                  this.$message.error("操作失败!");
                }
              },
              () => {
                this.$message.error("操作失败!");
              }
            );
        })
        .catch(() => {});
    },
    //移除出项目
    quitProject(val) {
      this.$confirm("将" + val.nick_name + "移出该项目, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$http
            .post("/project/quit-project", {
              userId: val.id,
              projectId: this.$route.params.projectId,
            })
            .then(
              (response) => {
                response = response.data;
                if (response.code === CODE_OK) {
                  this.getProjectUserList();
                  this.$message.success("成功!");
                } else {
                  this.$message.faild(response.msg);
                }
              },
              () => {
                this.$message.error("操作失败!");
              }
            );
        })
        .catch(() => {});
    },
    //获取用户列表
    getUserList(keyword) {
      if (keyword && keyword.length < 2) {
        return;
      }

      this.$http
        .get("/user/list", {
          params: {
            keyword,
            notProjectId: true,
          },
        })
        .then((response) => {
          response = response.data;
          if (response.code === CODE_OK) {
            this.searchUserList = response.data.items;
          }
        });
    },
  },
};
</script>

<style lang="scss" scope>
.user-search {
  margin: 10px 0;
  .el-select {
    width: 400px;
  }
}
</style>