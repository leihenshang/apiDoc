<template>
  <div class="create-api">
    <div class="box1">
      <el-button @click="returnApiPage">↩ 接口列表</el-button>
      <el-button @click="createApi()">保存</el-button>
    </div>
    <el-tabs v-model="activeName">
      <el-tab-pane label="基本信息" name="first">
        <apiInfo
          :groupList="groupList"
          :propertyList="propertyList"
          :groupId="groupId"
          v-on:update:apiInfo="apiInfo = $event"
          ref="apiInfo"
        />
      </el-tab-pane>
      <el-tab-pane label="接口备注" name="second">
        <detailDescription :description="description" v-on:update="description = $event" />
      </el-tab-pane>
    </el-tabs>

    <requestParams
      :propertyList="propertyList"
      v-on:update:header="apiData.http_request_header = $event"
      v-on:update:param="apiData.http_request_params = $event"
    />
    <returnParams :propertyList="propertyList" v-on:update="apiData.http_return_params = $event" />
  </div>
</template>

<script>
import returnParams from "./units/returnDataParams.vue";
import requestParams from "./units/requestDataParams.vue";
import apiInfo from "./units/apiInfo.vue";
import detailDescription from "./units/detailDescription.vue";

const CODE_OK = 200;
export default {
  name: "apiCreate",
  props: {},
  created() {
    this.getGroup();
    this.getProperty();
  },
  data() {
    return {
      groupId: this.$route.query.groupId ? this.$route.query.groupId : 0,
      activeName: "first",
      //分组列表
      groupList: [],
      //api属性列表
      propertyList: [],
      //说明和备注
      description: "",
      apiInfo: {},
      apiData: {
        http_request_header: [], //请求头
        http_request_params: [], //请求参数
        http_return_params: [], //返回参数
        http_return_sample: {
          returnDataSuccess: "", //返回数据成功
          returnDataFailed: "", //返回数据失败
        },
      },
      finalData: {},
      errors: "信息填写错误",
    };
  },
  methods: {
    errorHanle(val) {
      this.errors = val;
    },
    //返回api页面
    returnApiPage() {
      this.$router.go(-1);
    },
    //创建api
    createApi() {
      this.$refs.apiInfo.$refs.form.validate((validate) => {
        if (validate) {
          //loading
          let loadingInstance = this.$loading({ fullscreen: true });
          let data = Object.assign(this.finalData, this.apiInfo);
          data = Object.assign(data, this.apiData);

          if (data.group_id_second) {
            data.group_id = data.group_id_second;
          }

          this.$http
            .post("/api/create", {
              group_id: data.group_id,
              project_id: this.$route.params.projectId,
              data: JSON.stringify(data),
            })
            .then(
              (response) => {
                response = response.data;
                if (response.code === CODE_OK) {
                  this.$message.success("保存api成功!");
                  //跳转携带分组参数
                  this.$router.push({
                    name: "apiList",
                    params: { groupId: this.$route.query.groupId },
                  });
                } else {
                  this.$message.error("  保存api失败 ： " + response.msg);
                }
                this.$nextTick(() => {
                  loadingInstance.close();
                });
              },
              () => {
                this.$message.error("操作失败");
                this.$nextTick(() => {
                  loadingInstance.close();
                });
              }
            );
        } else {
          return false;
        }
      });
    },

    //获取分组信息
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
            }
          },
          () => {
            this.$message.error("获取数据失败");
          }
        );
    },
    //获取创建api的默认属性
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
/* 第一行按钮 */
.box1 {
  display: flex;
  justify-content: space-between;
}
</style>
