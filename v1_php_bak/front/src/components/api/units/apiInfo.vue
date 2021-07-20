<template>
  <div class="api-info-box">
    <div class="info">
      <el-form :model="apiInfo" :rules="rules" ref="form">
        <el-form-item>
          <el-col :span="5">
            <el-form-item prop="group_id" label="分组" label-width="80px">
              <el-select
                v-model="apiInfo.group_id"
                placeholder="分组"
                style="width: 90%"
                @change="selectChange"
              >
                <el-option
                  v-for="item in groupList"
                  :key="item.id"
                  :label="item.title"
                  :value="item.id"
                ></el-option>
              </el-select>
            </el-form-item>
          </el-col>

          <el-col :span="5">
            <el-form-item label="二级分组(可选)" label-width="110px">
              <el-select
                v-model="apiInfo.group_id_second"
                placeholder="子分组"
                style="width: 90%"
                clearable
              >
                <el-option
                  v-for="item in childGroup"
                  :key="item.id"
                  :label="item.title"
                  :value="item.id"
                ></el-option>
              </el-select>
            </el-form-item>
          </el-col>

          <el-col :span="5">
            <el-form-item label="请求协议" prop="protocol_type" label-width="80px">
              <el-select v-model="apiInfo.protocol_type" placeholder="选择请求协议" style="width: 90%">
                <el-option
                  v-for="item in propertyList.http_protocol"
                  :key="item.id"
                  :label="item.tag_name"
                  :value="item.tag_name"
                ></el-option>
              </el-select>
            </el-form-item>
          </el-col>

          <el-col :span="5">
            <el-form-item label="请求方式" prop="http_method_type" label-width="80px">
              <el-select v-model="apiInfo.http_method_type" placeholder="选择请求方式" style="width: 90%">
                <el-option
                  v-for="item in propertyList.http_method"
                  :key="item.id"
                  :label="item.tag_name"
                  :value="item.tag_name"
                ></el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-form-item>
        <el-form-item label="url" prop="url" label-width="80px">
          <el-input v-model="apiInfo.url"></el-input>
        </el-form-item>
        <el-form-item label="名称" prop="api_name" label-width="80px">
          <el-input v-model="apiInfo.api_name"></el-input>
        </el-form-item>
        <el-form-item label="对象名" prop="object_name" label-width="80px">
          <el-input v-model="apiInfo.object_name" placeholder="非必填"></el-input>
        </el-form-item>
        <el-form-item label="方法名" prop="function_name" label-width="80px">
          <el-input v-model="apiInfo.function_name" placeholder="非必填"></el-input>
        </el-form-item>
      </el-form>
    </div>
  </div>
</template>

<script>
export default {
  name: "apiInfo",
  props: {
    groupList: Array,
    propertyList: [Array, Object],
    apiData: Object,
    groupId: [String, Number],
    isUpdate: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    selectChange(value) {
      if (!value) {
        return;
      }

      this.$http
        .get("/group/list", {
          params: {
            cp: 1,
            type: 0,
            ps: 1000,
            projectId: this.$route.params.projectId,
            pId: value,
          },
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === 200) {
              if (response.data) {
                this.childGroup = response.data;
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
    updateChildGroup(val) {
      for (const key in val) {
        if (Object.hasOwnProperty.call(val, key)) {
          const element = val[key];
          if (element.id == this.apiData.group_id) {
            this.childGroup = element.childs;
            break;
          }
        }
      }
    },
  },

  data() {
    return {
      rules: {
        group_id: [{ required: true, message: "请选择分组", trigger: "blur" }],
        protocol_type: [
          { required: true, message: "请选择协议类型", trigger: "blur" },
        ],
        http_method_type: [
          { required: true, message: "请选择分组", trigger: "blur" },
        ],
        http_return_type: [
          { required: true, message: "请选择分组", trigger: "blur" },
        ],
        url: [{ required: true, message: "请输入url", trigger: "blur" }],
        api_name: [
          { required: true, message: "请输入api名称", trigger: "blur" },
        ],
      },
      apiInfo: {
        group_id: null, //分组
        project_id: null, //项目Id
        protocol_type: "", //协议
        http_method_type: "", //http请求方法
        http_return_type: "", //返回值类型
        url: "", //http请求URL
        api_name: "", //接口名称
        object_name: "", //对象名
        function_name: "", //程序内部方法名
        develop_language: "", //接口开发语言
        id: "",
        group_id_second: null,
        realGroupId: null,
      },
      childGroup: [],
    };
  },
  watch: {
    apiData: function () {
      this.apiInfo = this.apiData;
      if (this.apiData.group_id_second == 0) {
        this.apiInfo.group_id_second = null;
      }

      if (this.isUpdate && this.groupList.length > 0) {
        this.updateChildGroup(this.groupList);
      }
    },
    apiInfo: {
      handler: function (newdata) {
        this.$emit("update:apiInfo", newdata);
      },
      deep: true,
    },
    propertyList: function (val) {
      if (this.apiInfo.id <= 0) {
        for (let item in val) {
          switch (item) {
            case "http_method":
              this.apiInfo.http_method_type = val[item][0].tag_name;
              break;

            case "http_protocol":
              this.apiInfo.protocol_type = val[item][0].tag_name;
              break;

            case "http_return":
              this.apiInfo.http_return_type = val[item][0].tag_name;
              break;
          }
        }
      }
    },
    groupList: function (val) {
      if (this.isUpdate && this.childGroup.length < 1) {
        // childGroup  group_id_second
        this.updateChildGroup(val);
      }
    },
  },
};
</script>

<style lang="scss" scoped>
dl dd div {
  display: inline-block;
  width: 95%;
}

.info {
  border: 1px solid #dddddd;
  padding: 20px 10px 0 0;
  position: relative;
  background-color: #fff;
  box-sizing: border-box;
}

.info em {
  font-style: normal;
  font-size: 12px;
  font-weight: 700;
  margin: 0 5px;
  display: inline-block;
  color: red;
}

.info p {
  display: inline-block;
}

.info span {
  font-style: normal;
  font-size: 12px;
  font-weight: 700;
  display: inline-block;
  text-align: left;
  width: 4%;
}

.info dl dd {
  margin: 10px 0;
}

.info input,
select {
  padding: 0;
  outline: none;
  border: 1px solid #ddd;
  height: 28px;
  border-radius: 3px;
}

.info input {
  width: 80%;
  box-sizing: border-box;
  padding-left: 10px;
}

.info dl dd {
  font-size: 0;
  text-align: left;
}

.info dl dd select {
  font-size: 12px;
  margin: 0 5px;
}

.info dl dd i {
  font-size: 12px;
  font-style: normal;
  margin-right: 4px;
  font-weight: 600;
}

.info dl dd:first-child select {
  margin-left: 0;
}
</style>