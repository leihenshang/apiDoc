<template>
  <div class="field-wrapper">
    <div class="btn-wrapper">
      <el-button @click="showAddWindow = true">
        <i class="el-icon-plus"></i>
        新增
      </el-button>
      <el-divider></el-divider>

      <!-- 表单-开始 -->
      <el-dialog
        title="添加映射字段"
        :visible.sync="showAddWindow"
        :show-close="false"
        @close="actionCancel"
      >
        <el-form :model="fieldMapping" label-width="80px" ref="form" :rules="rules">
          <el-form-item label="字段名" prop="field">
            <el-input v-model="fieldMapping.field" autocomplete="off" placeholder="字段名"></el-input>
          </el-form-item>
          <el-form-item label="类型" prop="type">
            <el-select v-model="fieldMapping.type" placeholder="请选择类型" v-if="propertyList.var_type">
              <el-option-group
                v-for="(group,index) in propertyList.var_type"
                :key="group.label"
                :label="index"
              >
                <el-option
                  v-for="item in group"
                  :key="item.tag_name"
                  :label="item.tag_name"
                  :value="item.tag_name"
                ></el-option>
              </el-option-group>
            </el-select>
          </el-form-item>
          <el-form-item label="描述" prop="description">
            <el-input
              type="textarea"
              :rows="4"
              v-model="fieldMapping.description"
              autocomplete="off"
              placeholder="版本号"
            ></el-input>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="actionCancel">取 消</el-button>
          <el-button type="primary" @click="saveFieldMapping()">确 定</el-button>
        </div>
      </el-dialog>
      <!-- 表单-结束 -->
    </div>
    <!-- 数据-开始 -->
    <el-table :data="fieldList" stripe v-loading="loading">
      <el-table-column prop="field" label="字段名" width="180"></el-table-column>
      <el-table-column prop="type" label="类型"></el-table-column>
      <el-table-column prop="description" label="描述"></el-table-column>
      <el-table-column prop="create_time" label="创建时间"></el-table-column>
      <el-table-column prop label="操作">
        <template slot-scope="scope" v-show="!controlShow()">
          <el-button slot="reference" type="text" @click="del(scope.row.id)">删除</el-button>
          <el-divider direction="vertical"></el-divider>
          <el-button type="text" @click="updateAction(scope.row) ">编辑</el-button>
        </template>
      </el-table-column>
    </el-table>
    <!-- 数据-结束 -->
  </div>
</template>

<script>
import controlShow from "../../mixins/controlShow";
import getCommonProperty from "../../mixins/getCommonProperty";

const CODE_OK = 200;

export default {
  name: "fieldMapping",
  props: {
    projectId: [Number, String],
  },
  mixins: [controlShow, getCommonProperty],
  created() {
    this.loading = true;
    this.getFieldList();
    this.getProperty();
  },
  data() {
    return {
      propertyList: [],
      loadding: false,
      showAddWindow: false,
      fieldList: [],
      fieldMapping: {},
      rules: {
        field: [
          { required: true, message: "请输入字段名", trigger: "blur" },
          {
            min: 1,
            max: 50,
            message: "长度在 1 到 50 个字符",
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
      },
    };
  },
  methods: {
    del(id) {
      this.$confirm("此操作将删除该记录, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$http
            .post("/field-mapping/delete", {
              id,
            })
            .then(
              (res) => {
                let response = res.data;
                if (response.code === CODE_OK) {
                  this.$message.success("成功!");
                  this.getFieldList();
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
    actionCancel() {
      this.fieldMapping = {};
      this.showAddWindow = false;
    },

    updateAction(data) {
      this.fieldMapping = data;
      this.showAddWindow = true;
    },
    //保存字段
    saveFieldMapping() {
      let url = this.fieldMapping.id
        ? "/field-mapping/update"
        : "/field-mapping/create";

      this.$refs.form.validate((valid) => {
        if (!valid) {
          return;
        }

        this.$http
          .post(url, {
            ...this.fieldMapping,
            project_id: this.projectId,
          })
          .then((res) => {
            let response = res.data;
            if (response.code === CODE_OK) {
              this.$message.success("操作成功!");
              this.getFieldList();
              this.showAddWindow = false;
              this.$refs.form.resetFields();
            } else {
              this.getFieldList();
              this.$message.error("操作失败!");
              this.actionCancel();
            }
          });
      });
    },
    //获取字段列表
    getFieldList() {
      this.loading = true;
      this.$http
        .get("/field-mapping/list", {
          params: {
            projectId: this.$route.params.projectId,
          },
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              this.fieldList = response.data;
            }

            this.loading = false;
          },
          () => {
            this.$message.error("获取数据-操作失败!");
          }
        );
    },
  },
};
</script>

<style lang="scss" scoped>
</style>