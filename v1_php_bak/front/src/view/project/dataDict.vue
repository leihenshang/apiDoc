<template>
  <div class="dataDict">
    <el-button @click="showAddWindow = !showAddWindow">
      <i class="el-icon-plus"></i>新增
    </el-button>
    <el-divider></el-divider>
    <!-- 数据-开始 -->
    <el-table :data="dataArr" stripe style="width: 100%" border v-loading="loading">
      <el-table-column prop="type" label="类型" width="180">
        <template slot-scope="scope">{{showDbType(scope.row.type)}}</template>
      </el-table-column>
      <el-table-column prop="address" label="地址" width="180"></el-table-column>
      <el-table-column prop="port" label="端口" width="180"></el-table-column>
      <el-table-column prop="description" label="描述"></el-table-column>
      <el-table-column prop="username" label="用户名"></el-table-column>
      <el-table-column prop="database_name" label="数据库名"></el-table-column>
      <el-table-column prop="create_time" label="创建时间"></el-table-column>
      <el-table-column prop="create_user" label="创建者"></el-table-column>
      <el-table-column align="center" label="操作">
        <template slot-scope="scope">
          <el-button type="text" @click="deleteData(scope.row)">删除</el-button>
          <el-divider direction="vertical"></el-divider>
          <el-button type="text" @click="updateData(scope.row)">编辑</el-button>
          <el-divider direction="vertical"></el-divider>
          <el-button
            type="text"
            @click="showDictWindow = true;getSchemas(scope.row.id);currentRow=scope.row"
          >字典</el-button>
        </template>
      </el-table-column>
    </el-table>
    <!-- 数据-结束 -->

    <!-- 表单-开始 -->
    <el-dialog
      :title=" dataConnForm.id ? '修改数据库连接' : '新增数据库连接'"
      :visible.sync="showAddWindow"
      :show-close="false"
      width="40%"
      @close="formCancel"
    >
      <el-form :model="dataConnForm" label-width="80px" ref="form" :rules="rules">
        <el-form-item label="类型" prop="type">
          <el-select v-model="dataConnForm.type" placeholder="请选择">
            <el-option
              v-for="item in dbTypeSelect"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="地址" prop="address">
          <el-input v-model="dataConnForm.address" autocomplete="off" placeholder="地址"></el-input>
        </el-form-item>
        <el-form-item label="端口" prop="port">
          <el-input v-model.number="dataConnForm.port" autocomplete="off" placeholder="端口"></el-input>
        </el-form-item>
        <el-form-item label="用户名" prop="username">
          <el-input v-model="dataConnForm.username" autocomplete="off" placeholder="用户名"></el-input>
        </el-form-item>
        <el-form-item label="密码" prop="password">
          <el-input
            v-model="dataConnForm.password"
            autocomplete="off"
            placeholder="密码"
            type="password"
          ></el-input>
        </el-form-item>
        <el-form-item label="数据库名" prop="database_name">
          <el-input v-model="dataConnForm.database_name" autocomplete="off" placeholder="数据库名"></el-input>
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input
            type="textarea"
            :rows="4"
            v-model="dataConnForm.description"
            autocomplete="off"
            placeholder="输入描述"
          ></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="formCancel">取 消</el-button>
        <el-button type="primary" @click="saveDataConnForm()">确 定</el-button>
      </div>
    </el-dialog>
    <!-- 表单-结束 -->

    <!-- 字典-开始 -->
    <el-dialog title="数据字典" :visible.sync="showDictWindow" v-loading="dictLoading" width="70%">
      <el-button @click="exportCsv">导出为 CSV</el-button>
      <div class="dictItem" v-for="(item,index) in currentSchemas" :key="index">
        <h4>{{item.name}}</h4>
        <p>{{item.fullName}}</p>
        <table class="dictTable">
          <tr>
            <th>字段名</th>
            <th>类型</th>
            <th>非空</th>
            <th>自增</th>
            <th>主键</th>
            <th>描述</th>
          </tr>
          <tr v-for="(item1,index1) in item.columns" :key="index1">
            <td>{{item1.name}}</td>
            <td>{{item1.dbType}}</td>
            <td>{{item1.allowNull}}</td>
            <td>{{item1.autoIncrement}}</td>
            <td>{{item1.isPrimaryKey}}</td>
            <td>{{item1.comment}}</td>
          </tr>
        </table>
      </div>
    </el-dialog>
    <!-- 字典-结束 -->
  </div>
</template>

<script>
const CODE_OK = 200;

export default {
  name: "dataDict",
  props: {
    projectId: 0,
  },
  data() {
    return {
      currentRow: null,
      pageSize: 15,
      currentPage: 1,
      loading: false,
      dictLoading: false,
      showAddWindow: false,
      showDictWindow: false,
      rules: {
        address: [
          { required: true, message: "请输入数据库地址", trigger: "blur" },
          {
            min: 1,
            max: 500,
            message: "长度在 1 到 500 个字符",
            trigger: "blur",
          },
        ],
        port: [
          {
            required: true,
            message: "请输入端口",
            trigger: "blur",
          },
          // { type: "number", message: "端口必须为数字" },
        ],

        username: [
          {
            required: true,
            message: "请输入用户名",
            trigger: "blur",
          },
        ],

        password: [
          {
            required: true,
            message: "请输入密码",
            trigger: "blur",
          },
        ],

        database_name: [
          {
            required: true,
            message: "请输入数据库名",
            trigger: "blur",
          },
        ],

        description: [
          {
            min: 1,
            max: 200,
            message: "长度在 1 到 200 个字符",
            trigger: "blur",
          },
        ],
        type: [
          { required: true, message: "请选择数据库类型", trigger: "blur" },
        ],
      },
      dataConnForm: {},
      dataArr: [],
      currentSchemas: [],
      dbTypeSelect: [
        {
          value: "1",
          label: "mysql",
        },
      ],
    };
  },
  created() {
    this.getList();
  },
  methods: {
    deleteData(data) {
      this.$confirm("此操作将删除该记录, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$http
            .post("/data-base/delete", {
              id: data.id,
            })
            .then((res) => {
              let response = res.data;
              if (response.code === CODE_OK) {
                this.$message.success("操作成功!");
                this.getList();
              } else {
                this.$message.error(response.msg);
              }
            });
        })
        .catch(() => {});
    },
    updateData(data) {
      this.dataConnForm = data;
      this.showAddWindow = true;
    },
    formCancel() {
      this.showAddWindow = false;
      this.dataConnForm = {};
    },
    saveDataConnForm() {
      let url = this.dataConnForm.id
        ? "/data-base/update"
        : "/data-base/create";

      this.$refs.form.validate((valid) => {
        if (!valid) {
          return;
        }

        this.$http
          .post(url, {
            ...this.dataConnForm,
            project_id: this.projectId,
          })
          .then((res) => {
            let response = res.data;
            if (response.code === CODE_OK) {
              this.$message.success("操作成功!");
              this.getList();
              this.formCancel();
              this.$refs.form.resetFields();
            } else {
              this.$message.error(response.msg);
              this.formCancel();
            }
          })
          .catch();
      });
    },
    getList() {
      this.$http
        .get("/data-base/list", {
          params: {
            ps: this.pageSize,
            cp: this.currentPage,
            projectId: this.projectId,
          },
        })
        .then((res) => {
          let response = res.data;
          if (response.code === CODE_OK) {
            this.dataArr = response.data.items;
          } else {
            this.$message.error("失败:" + response.msg);
          }
        });
    },
    getSchemas(id) {
      this.currentSchemas = [];
      this.dictLoading = true;
      this.$http
        .get("/data-base/schemas", {
          params: {
            id,
            projectId: this.projectId,
          },
        })
        .then((res) => {
          let response = res.data;

          if (response.code === CODE_OK) {
            this.currentSchemas = response.data;
          } else {
            this.$message.error("失败:" + response.msg);
          }
          this.dictLoading = false;
        });
    },
    exportCsv() {
      window.open(
        this.BASE_URL +
          "/data-base/schemas-export?id=" +
          this.currentRow.id +
          "&token=" +
          this.$store.state.userInfo.token
      );
    },
    showDbType(type) {
      switch (type) {
        case "1":
          return "mysql";
          break;

        default:
          return "other";
          break;
      }
    },
  },
};
</script>

<style lang="scss" scope>
.dictItem {
  h4,
  p {
    margin: 10px 0;
    padding: 0;
  }

  .dictTable {
    border: 1px solid black;
    border-collapse: collapse;
    td,
    th {
      border: 1px solid black;
      padding: 5px 10px;
      font-size: 14px;
    }
  }
}
</style>