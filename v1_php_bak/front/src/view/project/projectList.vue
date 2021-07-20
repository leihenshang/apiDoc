<template>
  <div class="project-list">
    <div class="project-list-bar">
      <el-button @click="dialogFormVisible = true; " :disabled="!controlShow()">+新增项目</el-button>
    </div>
    <!-- 项目列表-开始 -->
    <div class="project-list-content">
      <el-table
        :data="projectList"
        v-loading="loading"
        @cell-click="detail"
        size="medium"
      >
        <el-table-column prop="title" label="项目名称"></el-table-column>
        <el-table-column prop="version" label="版本号" width="180"></el-table-column>
        <el-table-column prop="type" label="类型" width="180"></el-table-column>
        <el-table-column prop="create_time" label="创建时间"></el-table-column>
        <el-table-column prop label="操作">
          <template slot-scope="scope">
            <el-button
              type="text"
              slot="reference"
              @click.stop="deleteData(scope.row.id)"
              :disabled="$store.state.userInfo.type == 1"
            >删除</el-button>
            <el-divider direction="vertical"></el-divider>
            <el-button
              type="text"
              @click.stop="form = scope.row;dialogFormVisible = true; isUpdate = true;"
              :disabled="$store.state.userInfo.type == 1"
            >编辑</el-button>
            <el-divider direction="vertical"></el-divider>
            <el-button type="text" @click.stop="detail(scope.row);">详情</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <!-- 项目列表-结束 -->
    <div class="page-wrapper">
      <el-pagination
        background
        layout="total,prev, pager, next"
        :total="parseInt(itemCount)"
        :page-size="pageSize"
        :current-page="currPage"
        @current-change="jumpPage($event)"
      ></el-pagination>
    </div>
    <!-- 编辑添加项目 -->
    <el-dialog title="添加项目" :visible.sync="dialogFormVisible">
      <el-form :model="form" label-width="80px" ref="form" :rules="rules">
        <el-form-item label="项目名称" prop="title">
          <el-input v-model="form.title" autocomplete="off" placeholder="项目名称"></el-input>
        </el-form-item>
        <el-form-item label="版本号" prop="version">
          <el-input v-model="form.version" autocomplete="off" placeholder="版本号"></el-input>
        </el-form-item>
        <el-form-item label="项目类型" prop="type">
          <el-select v-model="form.type" placeholder="请选择">
            <el-option label="pc" value="pc"></el-option>
            <el-option label="web" value="web"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="项目描述" prop="description">
          <el-input type="textarea" :rows="4" placeholder="请输入内容" v-model="form.description"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false;$refs.form.resetFields();isUpdate = false;">取 消</el-button>
        <el-button type="primary" @click=" isUpdate ? update(): create() ; ">确 定</el-button>
      </div>
    </el-dialog>
    <!-- 编辑添加项目-结束 -->
  </div>
</template>

<script>
import controlShow from "../../mixins/controlShow";
const CODE_OK = 200;

export default {
  name: "projectList",
  mixins: [controlShow],
  methods: {
    jump(route) {
      this.$router.push({ path: "/" + route });
    },
    //获取项目列表
    getProjectList(curr, pageSize) {
      this.loading = true;
      this.$http
        .get("/project/list", {
          params: {
            cp: curr,
            ps: pageSize,
          },
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              this.projectList = response.data.items;
              this.itemCount = Number(response.data.count);
            }

            this.loading = false;
          },
          () => {
            this.$message.error("获取数据-操作失败!");
          }
        );
    },
    //创建项目
    create() {
      this.$refs.form.validate((valid) => {
        if (valid) {
          this.$http
            .post("/project/create", {
              ...this.form,
            })
            .then((res) => {
              let response = res.data;
              if (response.code === CODE_OK) {
                this.$message.success("成功!");
                this.getProjectList(this.currPage, this.pageSize);
                this.dialogFormVisible = false;
                this.$refs.form.resetFields();
              } else {
                this.$message.error(response.msg);
              }
            });
        }
      });
    },
    //删除数据
    deleteData(id) {
      this.$confirm("此操作将删除该分组, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$http
            .post("/project/delete", {
              id,
            })
            .then((res) => {
              let response = res.data;
              if (response.code === CODE_OK) {
                this.$message.success("成功!" + response.msg);
                this.getProjectList(this.currPage, this.pageSize);
              } else {
                this.$message.error("失败!" + response.msg);
              }
            });
        })
        .catch(() => {});
    },
    //更新
    update() {
      this.$refs.form.validate((valid) => {
        if (valid) {
          this.$http
            .post("/project/update", {
              ...this.form,
            })
            .then((res) => {
              let response = res.data;
              if (response.code === CODE_OK) {
                this.$message.success("成功!");
                this.dialogFormVisible = false;
                this.$refs.updateData.resetFields();
                this.getProjectList(this.currPage, this.pageSize);
              } else {
                this.$message.error("失败!" + response.msg);
              }
              this.update = false;
            })
            .catch(() => {
              this.update = false;
            });
        }
      });
    },
    jumpPage(page) {
      this.currPage = page;
      this.getProjectList(page, this.pageSize);
    },
    detail(row) {
      this.$router.push("/detail/" + row.id);
    },
  },
  created() {
    this.getProjectList(this.currPage, this.pageSize);
  },
  data() {
    return {
      projectList: [],
      pageSize: 5,
      currPage: 1,
      itemCount: 0,
      loading: true,
      dialogFormVisible: false,
      isUpdate: false,
      form: {
        title: "",
        version: "",
        type: "",
        description: "",
      },
      rules: {
        title: [
          { required: true, message: "请输入名称", trigger: "blur" },
          {
            min: 2,
            max: 50,
            message: "长度在 2 到 50 个字符",
            trigger: "blur",
          },
        ],
        type: [{ required: true, message: "请选择类型", trigger: "blur" }],
        description: [
          {
            min: 2,
            max: 50,
            message: "长度在 2 到 50 个字符",
            trigger: "blur",
          },
        ],
        version: [
          { required: true, message: "请输入版本号", trigger: "blur" },
          {
            min: 1,
            max: 50,
            message: "长度在 6 到 50 个字符",
            trigger: "blur",
          },
        ],
      },
    };
  },
};
</script>

<style lang="scss" scoped>
.project-list {
  .project-list-bar {
    margin-bottom: 10px;
  }
  .project-list-content {
    min-height: 600px;
  }
  .page-wrapper {
    text-align: right;
  }
}
</style>
