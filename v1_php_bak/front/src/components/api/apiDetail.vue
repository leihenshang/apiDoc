<template>
  <div class="api-detail" v-loading="loading">
    <div class="btn-group">
      <el-button @click="returnApiPage">↩ 返 回</el-button>
      <div>
        <el-button @click="copyApi">复制接口</el-button>
        <el-button @click="updateApi()" type="primary">编辑</el-button>
      </div>
    </div>
    <!-- api信息 -->
    <div class="api-detail-info">
      <em>
        分组:
        <i>{{apiData.group_name}}</i>
      </em>
      <el-divider direction="vertical"></el-divider>
      <em>
        请求协议:
        <i>{{apiData.protocol_type ? apiData.protocol_type : 'unknown'}}</i>
      </em>
      <el-divider direction="vertical"></el-divider>
      <em>
        请求方式:
        <i>{{apiData.http_method_type ? apiData.http_method_type : 'unknown'}}</i>
      </em>
      <el-divider direction="vertical"></el-divider>
      <i>{{apiData.url ? apiData.url : 'unknown'}}</i>
      <el-divider direction="vertical"></el-divider>

      <i>{{apiData.api_name ? apiData.api_name : 'unknown'}}</i>
    </div>
    <!-- api信息-结束 -->
    <p style="font-size:14px;">{{apiData.description ? apiData.description :"没有描述" }}</p>
    <!-- 请求头信息 -->
    <div class="content-item">
      <table>
        <tr>
          <td colspan="2" style="text-align:center;">请求头</td>
        </tr>
        <tr>
          <th>头</th>
          <th>值</th>
        </tr>
        <tbody v-if="apiData.http_request_header && apiData.http_request_header[0]">
          <tr v-for="(item,index) in apiData.http_request_header" :key="index">
            <td>{{item.name}}</td>
            <td>{{item.content}}</td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="2">无</td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- 请求头信息-结束 -->
    <!-- 参数信息-start -->
    <div class="content-item">
      <div @click="copyAsPostman()" style="margin:5px 0;">
        <el-button size="mini">生成调试参数</el-button>
      </div>
      <!-- 对话框 -->
      <el-dialog title="复制到剪贴板" :visible.sync="dialogFormVisible" width="30%">
        <el-form>
          <el-form-item label="分隔符">
            <el-select v-model="delimiter" @change="generateStrParams()">
              <el-option label="冒号(:)" value=":"></el-option>
              <el-option label="逗号(,)" value=","></el-option>
              <el-option label="空格( )" value=" "></el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-input type="textarea" v-model="copyStr" :rows="8"></el-input>
          </el-form-item>
          <el-form-item>
            <el-button @click="copyToClipboard()">复制</el-button>
          </el-form-item>
        </el-form>
      </el-dialog>

      <table>
        <tr>
          <td colspan="5" style="text-align:center;">请求参数</td>
        </tr>
        <tr>
          <th>参数</th>
          <th>说明</th>
          <th>必填</th>
          <th>类型</th>
          <th>示例</th>
        </tr>
        <tbody v-if="apiData.http_request_params && apiData.http_request_params[0]">
          <tr v-for="(item,index) in apiData.http_request_params" :key="index">
            <td>{{item.name}}</td>
            <td>{{item.desc}}</td>
            <td>{{item.required}}</td>
            <td>{{item.type}}</td>
            <td>{{item.example}}</td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="5">无</td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- 参数信息-end -->

    <!-- 响应 -->
    <div class="content-item" v-show="apiData.http_return_params && apiData.http_return_params[0] ">
      <table>
        <tr>
          <td colspan="5" style="text-align:center;">响应信息</td>
        </tr>
        <tr>
          <th>字段</th>
          <th>类名</th>
          <th>说明</th>
          <th>必含</th>
          <th>类型</th>
        </tr>
        <tbody v-if=" apiData.http_return_params && apiData.http_return_params[0]">
          <tr v-for="(item,index) in apiData.http_return_params" :key="index">
            <td>{{item.fieldName}}</td>
            <td>{{item.objectName}}</td>
            <td>{{item.description}}</td>
            <td>{{item.required}}</td>
            <td>{{item.type}}</td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="5">无</td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- 响应-结束 -->

    <div class="content-item">
      <div v-if="apiData.http_return_sample">
        <h5>响应数据示例</h5>

        <h5 v-show=" apiData.http_return_sample.returnDataSuccess">成功</h5>
        <pre v-show="apiData.http_return_sample.returnDataSuccess">
      
    <code>
   {{ apiData.http_return_sample.returnDataSuccess}}
    </code>
  </pre>

        <div>
          <h5 v-show="apiData.http_return_sample.returnDataFailed">失败</h5>
          <pre v-show="apiData.http_return_sample.returnDataFailed">
    <code>
   {{ apiData.http_return_sample.returnDataFailed}}
    </code>
  </pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const CODE_OK = 200;
export default {
  name: "apiDetail",
  props: {
    apiId: [Number, String],
  },
  created() {
    this.getApiDetail();
  },
  data() {
    return {
      delimiter: ":",
      dialogFormVisible: false,
      copyStr: "",
      loading: true,
      apiData: {},
    };
  },
  methods: {
    copyToClipboard() {
      if (this.$clipboard(this.copyStr) === true) {
        this.$message.success("复制成功，使用ctrl + v粘贴");
        this.dialogFormVisible = !this.dialogFormVisible;
      }
    },
    //复制为postman参数
    copyAsPostman() {
      this.generateStrParams();
      this.dialogFormVisible = true;
    },
    //生成参数字符串
    generateStrParams() {
      this.copyStr = "";
      for (let value of this.apiData.http_request_params) {
        this.copyStr +=
          value.name +
          this.delimiter +
          (value.example ? value.example : "unknown") +
          "\r\n";
      }
    },
    updateApi() {
      this.$router.push({ name: "apiEdit", params: { apiId: this.apiId } });
    },
    returnApiPage() {
      this.$router.go(-1);
    },
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
              this.loading = false;
            } else {
              this.$message({
                message: "failed:" + response.msg,
                type: "error",
              });
            }
          },
          () => {
            this.$message({
              message: "获取数据-操作失败",
              type: "error",
            });
          }
        );
    },
    copyApi() {
      this.$router
        .push({
          name: "apiEdit",
          query: { copy: true },
        })
        .catch(() => {});
    },
  },
};
</script>

<style lang="scss" scoped>
.api-detail {
  padding-left: 10px;

  .btn-group {
    display: flex;
    justify-content: space-between;
  }

  .api-detail-info {
    padding: 10px;
    position: relative;
    background-color: #fff;
    margin: 10px 0;
    border-radius: 2px;
    em i {
      font-style: normal;
      background-color: #4caf4fc7;
      color: white;
      padding: 5px 6px;
      margin-left: 5px;
      border-radius: 4px;
      display: inline-block;
      text-align: center;
    }

    i {
      font-style: normal;
      font-weight: bold;
      min-width: 100px;
      display: inline-block;
      text-align: center;
    }

    em {
      font-style: normal;
      font-size: 12px;
      font-weight: 700;
      margin-right: 5px;
      min-width: 100px;
      display: inline-block;
    }

    span {
      font-style: normal;
      font-size: 12px;
      font-weight: 700;
      display: inline-block;
      text-align: center;
      width: 5%;
    }

    .api-detail-comment {
      width: 100%;
      height: 150px;
      border-radius: 4px;
      box-sizing: border-box;
      padding: 8px;
      font-size: 14px;
    }
  }

  .content-item {
    margin: 10px 0;
    padding: 10px 8px;
    box-sizing: border-box;
    border-radius: 6px;

    table {
      border-collapse: collapse;
      font-size: 14px;
      th,
      td {
        border: 1px solid black;
        padding: 5px 15px;
        max-width: 500px;
        word-break: break-all;
      }

      th {
        background: gray;
        color: #fff;
      }
    }

    pre {
      border-radius: 10px;
      background-color: rgba(128, 128, 128, 0.473);
    }
  }
}
</style>
