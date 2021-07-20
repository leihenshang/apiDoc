
<template>
  <div class="group">
    <!-- 分组操作-start -->
    <ul v-show="group" class="operator">
      <li>
        <a href="javascript:;" @click="clientBtn(null,null)">
          <i class="el-icon-s-order"></i>
          <slot>全 部</slot>
        </a>
      </li>
      <li @click="dialogFormVisible = true" v-show="controlShow()">
        <a href="javascript:;">
          <i class="el-icon-delete"></i> 新增分组
        </a>
      </li>
      <li class="last-item">
        <a href="javascript:;" @click="clientBtn(-1,null)">
          <i class="el-icon-delete"></i> 回 收 站
        </a>
      </li>
    </ul>
    <!-- 分组操作-end -->

    <!-- 分组列表-start -->
    <div class="list">
      <div v-for="(item,index) in group" :key="item.id" class="list-item">
        <div class="list-item-one">
          <div>
            <i
              :class=" item.isClickShowChild ? 'el-icon-arrow-down' : 'el-icon-arrow-right'"
              @click="clickFoldBtn(index)"
              v-show="item.childs.length > 0"
            ></i>
            <a href="javascript:;" @click="clientBtn(item.id,index)">{{item.title}}</a>
          </div>
          <el-dropdown
            placement="left-start"
            @command="handleCommand"
            trigger="click"
            v-show="controlShow()"
          >
            <span class="el-icon-s-unfold"></span>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item :command="{action:'del',data:item}">删除</el-dropdown-item>
              <el-dropdown-item :command="{action:'edit',data:item}">编辑</el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
        </div>

        <!-- 子分组-start -->
        <ul v-show="item.childs && item.isClickShowChild === true">
          <li v-for="(child,index) in item.childs" :key="child.id">
            <a href="javascript:;" @click="clientBtn(child.id,index,true)">{{child.title}}</a>

            <el-dropdown
              placement="left-start"
              @command="handleCommand"
              trigger="click"
              v-show="controlShow()"
            >
              <span class="el-icon-s-unfold"></span>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item :command="{action:'del',data:child,parent:item}">删除</el-dropdown-item>
                <el-dropdown-item :command="{action:'edit',data:child,parent:item}">编辑</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </li>
        </ul>
        <!-- 子分组-end -->
      </div>
    </div>
    <!-- 分组列表-end -->

    <!-- 新增分组-start -->
    <el-dialog title="新增分组" :visible.sync="dialogFormVisible" width="40%">
      <el-form :model="form" ref="form">
        <el-form-item label="上级" :label-width="formLabelWidth">
          <el-select v-model="form.p_id" placeholder="请选择上级" :disabled="isFirstUpdate" clearable>
            <el-option v-for="item in group" :key="item.id" :label="item.title" :value="item.id"></el-option>
          </el-select>
        </el-form-item>

        <el-form-item label="组名" :label-width="formLabelWidth">
          <el-input v-model="form.title" autocomplete="off" style="width:70%"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click=" updateId > 0 ? updateGroup() : createGroup()">确 定</el-button>
          <el-button @click="dialogFormVisible = false">取 消</el-button>
        </el-form-item>
      </el-form>
    </el-dialog>
    <!-- 新增分组-end -->
  </div>
</template>

<script>
import controlShow from "../../mixins/controlShow";

const CODE_OK = 200;
export default {
  name: "group",
  props: {
    showIsEdit: {
      type: Boolean,
      default: false,
    },
    formLabelWidth: {
      type: String,
      default: "80",
    },
    type: {
      type: Number,
      default: 0,
    },
  },
  created() {
    if (this.group.length < 1) {
      this.getGroup(this.pageSize, this.curr, this.$route.params.projectId);
    }
  },
  data() {
    return {
      group: [],
      curr: 1,
      pageSize: 100,
      dialogFormVisible: false,
      isFirstUpdate: false,
      form: {
        p_id: null,
        title: "",
      },
      updateId: 0,
    };
  },
  methods: {
    handleCommand(command) {
      if (command.action === "del") {
        this.delete(command.data.id);
      } else if (command.action === "edit") {
        this.showUpdateDiglog(command.data, command.parent);
      }
    },
    //获取分组列表
    getGroup(pageSize, curr, projectId) {
      this.$http
        .get("/group/list", {
          params: {
            cp: curr,
            type: this.type,
            ps: pageSize,
            projectId: projectId ? projectId : 0,
          },
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              //添加点击状态默认值
              if (response.data) {
                for (const key in response.data) {
                  response.data[key].isClick = false;
                  response.data[key].isClickShowChild = false;
                  for (const childKey in response.data[key]["childs"]) {
                    response.data[key]["childs"][childKey].isClick = false;
                  }
                }
                this.group = response.data;
              }
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
    //删除分组
    delete(id) {
      if (!id) {
        this.$message.error("id错误");
        return;
      }

      this.$confirm("该分组将被删除, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$http
            .post("/group/del", {
              id: id,
            })
            .then((response) => {
              response = response.data;
              if (response.code === CODE_OK) {
                this.$message.success("成功!");
              } else {
                this.$message.error("操作失败");
              }
              this.getGroup(
                this.pageSize,
                this.curr,
                this.$route.params.projectId
              );
            });
        })
        .catch(() => {
          this.showCreateGroup = false;
        });
    },
    //点击折叠按钮
    clickFoldBtn(index) {
      if (index !== null) {
        this.group[index].isClickShowChild = this.group[index].isClickShowChild
          ? false
          : true;
      }
    },
    //点击分组
    clientBtn(id, index, isChild = false) {
      if (!isChild) {
        for (const key in this.group) {
          this.group[key].isClick = false;
        }
        if (index !== null) {
          this.group[index].isClick = true;
          this.group[index].isClickShowChild = true;
        }
      }

      this.$emit("change-group", id);
    },
    showUpdateDiglog(data, parent = null) {
      this.isFirstUpdate = true;
      this.form.title = data.title;
      if (parent) {
        this.form.p_id = parent.id;
        this.isFirstUpdate = false;
      }
      this.updateId = data.id;
      this.dialogFormVisible = true;
    },

    //更新数据
    updateGroup() {
      if (this.updateId <= 0) {
        return;
      }

      this.$http
        .post("/group/update", {
          title: this.form.title,
          p_id: this.form.p_id,
          id: this.updateId,
          type: this.type,
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              this.$message.success("更新成功!");
            } else {
              this.$message.error(response.msg);
            }

            this.getGroup(
              this.pageSize,
              this.curr,
              this.$route.params.projectId
            );
            this.dialogFormVisible = false;

            this.updateId = 0;
            this.form.title = "";
            this.form.p_id = 0;
            this.isFirstUpdate = false;
          },
          (res) => {
            let response = res.data;
            this.$message.error(
              "获取数据-操作失败!" + !response.msg ? response.msg : ""
            );
          }
        );
    },
    createGroup() {
      if (this.form.title.length < 1) {
        this.$message.error("分组标题不能为空");
        return;
      }

      this.$http
        .post("/group/create", {
          project_id: this.$route.params.projectId,
          type: this.type,
          ...this.form,
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              this.$message.success("创建成功!");
            } else {
              this.$message.error(response.msg);
            }

            this.getGroup(
              this.pageSize,
              this.curr,
              this.$route.params.projectId
            );

            this.dialogFormVisible = false;
          },
          (res) => {
            let response = res.data;
            this.dialogFormVisible = false;
            this.$message.error(
              "操作失败!" + !response.msg ? response.msg : ""
            );
          }
        );
    },
  },
  watch: {
    $route: function (to) {
      if (to.params.groupId == 0) {
        this.getGroup(this.pageSize, this.curr, this.$route.params.projectId);
      }
    },
  },
  components: {},
  mixins: [controlShow],
};
</script>

<style lang="scss" scoped>
.group {
  width: 100%;
  height: 100%;
  box-sizing: border-box;
  background-color: #fff;
  overflow-y: auto;
  ul.operator {
    margin: 0;
    padding: 0;
    list-style: none;

    li {
      width: 100%;
      overflow: hidden;
      display: flex;
      margin: 8px 0;
      height: 32px;
      line-height: 32px;
      &:last-child {
        border-bottom: 1px solid rgb(228, 219, 219);
        padding-bottom: 6px;
      }

      i {
        margin-right: 5px;
      }

      a {
        display: inline-block;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 14px;
        height: 28px;
        color: rgb(75, 74, 74);
        margin-left: 8px;
        padding-left: 10px;
        height: 100%;
        text-decoration: none;
      }

      &:hover {
        background-color: rgb(125, 195, 241);
        a {
          color: white;
        }
      }
    }
  }

  .list {
    a {
      text-decoration: none;
      font-size: 14px;
    }

    .list-item {
      .list-item-one {
        height: 40px;
        display: flex;
        justify-content: space-between;
        padding: 0 20px 0 10px;
        align-items: center;
        i {
          box-sizing: border-box;
          margin-right: 5px;
          font-weight: bold;
        }
      }

      ul {
        list-style: none;
        margin: 0;
        padding: 0;

        li {
          position: relative;
          height: 40px;
          line-height: 40px;
          a {
            position: absolute;
            display: inline-block;
            left: 45px;
          }

          .el-dropdown {
            position: absolute;
            right: 20px;
          }
        }
      }
    }
  }
}
</style>
