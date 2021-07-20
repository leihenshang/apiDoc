<template>
  <div class="api-list">
    <div class="api-box">
      <el-table :data="apiList.items" stripe style="width: 100%" v-loading="loading" border>
        <el-table-column prop="api_name" label="名称"></el-table-column>
        <el-table-column label="url">
          <template slot-scope="scope">
            <el-tag
              size="mini"
              type="success"
              v-if="scope.row.http_method_type === 'POST'"
            >{{ scope.row.http_method_type }}</el-tag>
            <el-tag size="mini" v-else-if="scope.row.http_method_type === 'GET'">
              {{
              scope.row.http_method_type
              }}
            </el-tag>
            <el-tag size="mini" type="warning" v-else>
              {{
              scope.row.http_method_type
              }}
            </el-tag>
            <em
              style="font-style:normal; display:inline-block;margin-left:15px;font-weight:600;"
            >{{scope.row.url}}</em>
          </template>
        </el-table-column>
        <el-table-column prop="protocol_type" label="协议" width="80"></el-table-column>
        <el-table-column prop="create_time" label="创建时间"></el-table-column>
        <el-table-column prop label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="success" plain @click="jumpPage('detail', scope.row.id)">详情</el-button>
            <el-popconfirm
              v-if="scope.row.is_deleted == 1"
              title="确定要还原这个api?"
              placement="top"
              @confirm="restoreApi(scope.row.id)"
              width="200"
            >
              <el-button slot="reference" v-show="controlShow()">还原</el-button>
            </el-popconfirm>

            <el-popconfirm
              v-else
              title="确定要删除这个api?"
              placement="top"
              @confirm="delApi(scope.row.id)"
              width="200"
            >
              <el-button slot="reference" :disabled="!controlShow()">删除</el-button>
            </el-popconfirm>

            <el-button
              type="warning"
              plain
              @click="jumpPage('edit', scope.row.id)"
              :disabled="!controlShow()"
            >编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <div class="page" v-show="count > 0 && !$route.query.keyword">
      <el-pagination
        background
        layout="total,prev, pager, next"
        :total="count"
        :page-size="ps"
        :current-page="cp"
        @current-change="changePage($event)"
      ></el-pagination>
    </div>
  </div>
</template>

<script>
import controlShow from "../../mixins/controlShow";
const CODE_OK = 200;

export default {
  name: "apiList",
  props: {},
  created() {
    this.getApiList(
      this.ps,
      this.cp,
      this.$route.params.projectId,
      this.$route.query.groupId
    );
  },
  data() {
    return {
      loading: true,
      apiList: {},
      ps: 10,
      cp: 1,
      count: 0,
    };
  },
  methods: {
    restoreApi(id) {
      this.$confirm("该API将被还原, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$http
            .post("/api/restore", {
              id: id,
            })
            .then((response) => {
              response = response.data;
              if (response.code === CODE_OK) {
                this.$message.success("成功!");
                this.$router.push({
                  name: "apiList",
                  params: { groupId: 0 },
                });
              } else {
                this.$message.error("操作失败!");
              }
            });
        })
        .catch(() => {});
    },
    //获取api列表
    getApiList(ps, cp, projectId, groupId, keyword) {
      this.$http
        .get("/api/list", {
          params: {
            cp: cp,
            ps: ps,
            projectId,
            isDeleted: groupId < 0 ? 1 : 0,
            groupId: groupId,
            keyword,
          },
        })
        .then((response) => {
          response = response.data;
          if (response.code === CODE_OK) {
            this.apiList = response.data;
            this.count = parseInt(response.data.count);
            this.loading = false;
          }
        });
    },
    //翻页
    changePage(event) {
      if (event == this.cp) {
        return;
      }
      this.loading = true;
      this.getApiList(
        this.ps,
        event,
        this.$route.params.projectId,
        this.$route.query.groupId
      );
      this.cp = event;
    },
    delApi(id) {
      this.loading = true;
      this.$http
        .post("/api/del", {
          id: id,
        })
        .then((response) => {
          response = response.data;
          if (response.code === CODE_OK) {
            this.$message.success("操作成功");
            this.$router.push({
              name: "apiList",
              params: { groupId: this.$route.query.groupId },
              query: { random: Math.random() },
            });
          } else {
            this.$message.error("失败:" + response.msg);
          }
        })
        .catch(() => {
          this.loading = false;
        });
    },
    jumpPage(name, id) {
      id = parseInt(id);
      switch (name) {
        case "edit":
          this.$router.push({ name: "apiEdit", params: { apiId: id } });
          break;

        case "detail":
          this.$router.push({ name: "apiDetail", params: { apiId: id } });
          break;

        default:
          break;
      }
    },
  },
  computed: {
    getapiId() {
      return this.$route.params.apiId;
    },
  },
  components: {},
  watch: {
    $route: function (to) {
      if (to.query.keyword) {
        this.cp = 1;
      
        this.getApiList(this.ps, this.cp, to.params.projectId, 0, to.query.keyword);
        this.loading = true;
      } else {
        this.cp = 1;
        this.getApiList(this.ps, this.cp,to.params.projectId, to.query.groupId);
        this.loading = true;
      }
    },
  },
  mixins: [controlShow],
};
</script>

<style lang="scss" scoped>
.api-box {
  min-height: 600px;
}

.api-list .page {
  text-align: right;
  padding: 20px 0;
}

.api-box button {
  margin-right: 5px;
}
</style>
