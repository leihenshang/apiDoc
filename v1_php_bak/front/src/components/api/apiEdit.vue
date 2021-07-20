<template>
  <div class="create-api" v-loading="loading">
    <div class="box1">
      <el-button @click="returnApiPage">↩ 返 回</el-button>
      <el-button style="float:right;" @click="saveApi()">保 存</el-button>
    </div>

    <el-tabs v-model="activeName">
      <el-tab-pane label="基本信息" name="first">
        <!-- api信息 -->
        <apiInfo
          :groupList="groupList"
          :propertyList="propertyList"
          :apiData="apiData"
          :isUpdate="true"
          ref="apiInfo"
        />
      </el-tab-pane>
      <el-tab-pane label="接口备注" name="second">
        <!-- 详细说明 -->
        <detailDescription
          :description="apiData.description"
          v-on:update="apiData.description=$event"
        />
      </el-tab-pane>
    </el-tabs>

    <!-- 请求参数 -->
    <requestParams
      :propertyList="propertyList"
      v-on:update:header="apiData.http_request_header=$event"
      v-on:update:param="apiData.http_request_params=$event"
      :header="http_request_header"
      :params="http_request_params"
    />

    <!-- 响应参数 -->
    <returnParams
      :propertyList="propertyList"
      v-on:update="apiData.http_return_params=$event"
      :returnData="http_return_params"
    />
  </div>
</template>

<script>
import returnParams from "./units/returnDataParams.vue";
import requestParams from "./units/requestDataParams.vue";
import apiInfo from "./units/apiInfo.vue";
import detailDescription from "./units/detailDescription.vue";
const CODE_OK = 200;
export default {
  name: "apiEdit",
  props: {
    apiId: [Number, String],
  },
  created() {
    this.getApiDetail();
    this.getGroup();
    this.getProperty();
    this.isCopy = this.$route.query.copy == true;
  },
  data() {
    return {
      activeName: "first",
      loading: true,
      groupList: [],
      propertyList: [],
      apiData: {},
      http_request_header: [],
      http_request_params: [],
      http_return_params: [],
      isCopy: false,
    };
  },
  methods: {
    //获取api详情
    getApiDetail() {
      this.$http
        .get("/api/detail", {
          params: {
            id: this.apiId,
          },
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              this.apiData = response.data;
              this.http_request_params = this.apiData.http_request_params;
              this.http_request_header = this.apiData.http_request_header;
              this.http_return_params = this.apiData.http_return_params;
            } else {
              this.$message.error("获取数据失败");
            }

            this.loading = false;
          },
          () => {
            this.$message.error("获取数据失败");
          }
        );
    },
    //返回api列表页面
    returnApiPage() {
      this.$router.go(-1);
    },
    //更新api
    saveApi() {
      let url = this.isCopy ? "/api/create" : "/api/update";
      this.$refs.apiInfo.$refs.form.validate((validate) => {
        if (validate) {
          let data = JSON.stringify(this.apiData);
          data = JSON.parse(data);

          if (data.group_id_second) {
            data.group_id = data.group_id_second;
          }

          this.$http
            .post(url, {
              id: this.apiId,
              group_id: this.apiData.group_id,
              project_id: this.$route.params.projectId,
              data: JSON.stringify(data),
            })
            .then((response) => {
              response = response.data;
              if (response.code === CODE_OK) {
                this.$message({
                  message: "保存成功！",
                  type: "success",
                });
                this.$router.push({
                  name: "apiDetail",
                  params: { apiId: this.apiId },
                });
              } else {
                this.$message({
                  message: !response.msg ? response.msg : "",
                  type: "error",
                });
              }
            });
        }
      });
    },
    //获取分组数据
    getGroup() {
      this.$http
        .get("/group/list", {
          params: {
            projectId: this.$route.params.projectId,
            type: 1,
          },
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              this.groupList = response.data;
            } else {
              this.$message.error("获取数据失败");
            }
          },
          () => {
            this.$message.error("获取数据失败");
          }
        );
    },
    //获取通用属性
    getProperty() {
      this.$http
        .get("/property/list", {
          params: {},
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              this.propertyList = response.data;
            } else {
              this.$message.error("获取数据失败");
            }
          },
          () => {
            this.$message.error("获取数据失败");
          }
        );
    },
  },
  components: {
    returnParams,
    requestParams,
    apiInfo,
    detailDescription,
  },
};
</script>

<style lang="scss" scoped>
.create-api {
  margin-bottom: 20px;
}

.box1 {
  display: flex;
  justify-content: space-between;
}
</style>
