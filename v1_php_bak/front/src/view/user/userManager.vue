<template>
  <div class="user">
    <div class="text-box">
      <el-input
        placeholder="请输入用户名/昵称/邮箱"
        v-model="keyword"
        clearable
        style="width:20%;margin-right:8px"
        @clear="keyword='';getUserList();"
      ></el-input>
      <el-button icon="el-icon-search" type="primary" plain @click="getUserList()">搜索</el-button>
      <el-button icon="el-icon-plus" @click="dialogFormVisible = true">用户</el-button>
      <!-- 新增用户 -->
      <el-dialog title="新建用户" :visible.sync="dialogFormVisible" width="30%">
        <el-form :model="form" label-width="80px" ref="form" :rules="rules">
          <el-form-item label="登录名" prop="name">
            <el-input v-model="form.name" autocomplete="off" placeholder="登录名"></el-input>
          </el-form-item>
          <el-form-item label="邮箱" prop="email">
            <el-input v-model="form.email" autocomplete="off" placeholder="邮箱"></el-input>
          </el-form-item>
          <el-form-item label="密码" prop="pwd">
            <el-input v-model="form.pwd" autocomplete="off" type="password" placeholder="密码"></el-input>
          </el-form-item>
          <el-form-item label="重复密码" prop="re_pwd">
            <el-input v-model="form.re_pwd" autocomplete="off" type="password" placeholder="重复密码"></el-input>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">取 消</el-button>
          <el-button type="primary" @click="createUser()">确 定</el-button>
        </div>
      </el-dialog>
      <!-- 新增用户-结束 -->
    </div>
    <div class="all-user" v-loading="loading">
      <el-table :data="userList.items" stripe style="width: 100%" border>
        <el-table-column prop="name" label="账号" width="180"></el-table-column>
        <el-table-column prop="nick_name" label="昵称" width="180"></el-table-column>

        <el-table-column prop="type" label="类型" width="180">
          <template slot-scope="scope">{{ transferType(scope.row.type) }}</template>
        </el-table-column>

        <el-table-column prop="email" label="邮箱"></el-table-column>
        <el-table-column prop="create_time" label="创建时间"></el-table-column>
        <el-table-column prop="last_login_time" label="最后登录时间"></el-table-column>
        <el-table-column prop="state" label="禁用" width="100">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.state"
              :active-value="2"
              :inactive-value="1"
              :disabled="actionBtnShow(scope.row)"
              @change="updateState(scope.row)"
            ></el-switch>
          </template>
        </el-table-column>
        <el-table-column align="center" label="操作" width="100">
          <template slot-scope="scope">
            <el-dropdown :hide-on-click="false" trigger="click" @command="handleCommand">
              <el-button :disabled="actionBtnShow(scope.row)">操作</el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item :command="{action:'del',data:scope.row}">删除用户</el-dropdown-item>
                <el-dropdown-item :command="{action:'initPwd',data:scope.row}">初始化密码</el-dropdown-item>
                <el-dropdown-item
                  v-if="scope.row.type == 1"
                  :command="{action:'setAdminUser',data:scope.row}"
                >设为管理员</el-dropdown-item>
                <el-dropdown-item
                  v-else-if="scope.row.type == 2"
                  :command="{action:'setNormalUser',data:scope.row}"
                >设为普通用户</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <div class="page">
      <el-pagination
        background
        layout="prev, pager, next"
        :total=" parseInt(userList.count)"
        :page-size="ps"
        :current-page="cp"
        @prev-click="cp --; getUserList()"
        @next-click="cp ++; getUserList()"
        @current-change="getUserList($event)"
      ></el-pagination>
    </div>
  </div>
</template>
<script>
const CODE_OK = 200;
const IS_DELETED = 1;

export default {
  name: "userManager",
  data() {
    //自定义的 密码验证，判断两次密码是否一致
    var validatePass = (rule, value, callback) => {
      if (value === "") {
        callback(new Error("请输入密码"));
      } else {
        if (this.form.pwd !== "") {
          this.$refs.form.validateField("re_pwd");
        }
        callback();
      }
    };
    var reValidatePass = (rule, value, callback) => {
      if (value === "") {
        callback(new Error("请再次输入密码"));
      } else if (value !== this.form.pwd) {
        callback(new Error("两次输入密码不一致!"));
      } else {
        callback();
      }
    };

    return {
      userList: [],
      projectUser: [],
      keyword: "",
      ps: 10,
      cp: 1,
      loading: true,
      dialogFormVisible: false,
      form: {
        name: "",
        email: "",
        pwd: "",
        re_pwd: "",
      },
      rules: {
        name: [
          { required: true, message: "请输入登录名称", trigger: "blur" },
          {
            min: 2,
            max: 50,
            message: "长度在 2 到 50 个字符",
            trigger: "blur",
          },
        ],
        email: [
          {
            type: "email",
            message: "请输入邮箱地址",
            trigger: "blur",
          },
        ],
        pwd: [
          { required: true, message: "请输入密码", trigger: "blur" },
          {
            min: 6,
            max: 50,
            message: "长度在 6 到 50 个字符",
            trigger: "blur",
          },
          { validator: validatePass, trigger: "blur" },
        ],
        re_pwd: [
          { required: true, message: "请再次输入密码", trigger: "blur" },
          {
            min: 6,
            max: 50,
            message: "长度在 6 到 50 个字符",
            trigger: "blur",
          },
          { validator: reValidatePass, trigger: "blur" },
        ],
      },
    };
  },
  created() {
    this.getUserList();
  },
  methods: {
    //处理下拉列表命令
    handleCommand(command) {
      switch (command.action) {
        case "del":
          this.deleteUser(command.data.id);
          break;
        case "initPwd":
          this.initPwd(command.data.id);
          break;
        case "setNormalUser":
          this.updateUserType(command.data, 1);
          break;
        case "setAdminUser":
          this.updateUserType(command.data, 2);
          break;

        default:
          break;
      }
    },
    createUser() {
      this.$refs.form.validate((valid) => {
        if (valid) {
          this.$http
            .post("/user/create", {
              ...this.form,
            })
            .then(
              (response) => {
                response = response.data;
                if (response.code === CODE_OK) {
                  this.getUserList();
                  this.$message.success("成功！~");
                } else {
                  this.$message.error(response.msg);
                }
                this.dialogFormVisible = false;
                this.$refs.form.resetFields();
              },
              () => {
                this.$message.error("操作失败!");
              }
            );
        } else {
          return false;
        }
      });
    },
    updateState(data) {
      this.$http
        .post("/user/update", {
          state: data.state,
          userId: data.id,
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code !== CODE_OK) {
              data.state = 2;
            }
          },
          () => {
            this.$message.error("请求失败!");
          }
        );
    },
    updateUserType(data, userType) {
      this.$http
        .post("/user/update", {
          userType,
          userId: data.id,
        })
        .then((response) => {
          response = response.data;
          if (response.code !== CODE_OK) {
            this.$message.error(response.msg);
          } else {
            data.type = userType;
          }
        });
    },
    deleteUser(id) {
      this.$confirm("此操作将删除该用户, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$http
            .post("/user/update-status", {
              is_deleted: IS_DELETED,
              userId: id,
            })
            .then(
              (response) => {
                response = response.data;
                if (response.code === CODE_OK) {
                  this.getUserList();
                  this.$message.success("成功！~");
                } else {
                  this.$message.error(response.msg);
                }
              },
              () => {
                this.$message.error("请求失败!");
              }
            );
        })
        .catch(() => {});
    },
    initPwd(id) {
      this.$confirm("此操作将重置用户密码, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$http
            .post("/user/init-pwd", {
              userId: id,
            })
            .then(
              (response) => {
                response = response.data;
                if (response.code === CODE_OK) {
                  this.getUserList();
                  this.$message.success("成功:" + response.msg);
                } else {
                  this.$message.error(response.msg);
                }
              },
              () => {
                this.$message.error("请求失败!");
              }
            );
        })
        .catch(() => {});
    },
    //获取用户列表
    getUserList(cp) {
      this.loading = true;
      if (!cp) {
        cp = this.cp;
      }

      this.$http
        .get("/user/list", {
          params: {
            keyword: this.keyword,
            ps: this.ps,
            cp: cp,
            all: true,
          },
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              this.userList = response.data;
            } else {
              this.$message.error(response.msg);
            }
            this.loading = false;
          },
          () => {
            this.$message.error("获取数据-操作失败!");
          }
        );
    },
    //转换状态字符串显示
    transferState(state) {
      state = parseInt(state);
      let str = "";
      //1正常2禁用3未激活
      switch (state) {
        case 1:
          str = "正常";
          break;
        case 2:
          str = "禁用";
          break;
        default:
          str = "unknown";
          break;
      }

      return str;
    },
    transferType(type) {
      type = parseInt(type);
      let str = "";
      //1普通用户2管理员
      switch (type) {
        case 1:
          str = "普通用户";
          break;
        case 2:
          str = "管理员";
          break;
        case 3:
          str = "超级管理员";
          break;
        default:
          str = "未知";
          break;
      }

      return str;
    },
    actionBtnShow(data) {
      //如果是超级管理员关闭
      if (data.type == 3) {
        return true;
      }

      //如果是超级管理员打开
      if (this.$store.state.userInfo.type == 3) {
        return false;
      }

      //当前行是自己，关闭
      if (this.$store.state.userInfo.id == data.id) {
        return true;
      }

      //当前行是普通用户，打开
      if (data.type == 1) {
        return false;
      }

      //当前行是其他管理员，关闭
      if (data.type == 2) {
        return true;
      }

      return true;
    },
  },
};
</script>
<style lang="scss" scoped>
.text-box {
  margin: 10px 0;
}

.all-user {
  min-height: 600px;
}

.page {
  text-align: right;
}
</style>