<template>
  <div class="message" v-loading="loading">
    <el-timeline>
      <el-timeline-item
        v-for="(item, index) in operationLog"
        :key="index"
        :timestamp="item.create_time"
      >{{item.nick_name}}, {{item.content}}</el-timeline-item>
    </el-timeline>
  </div>
</template>

<script>
const CODE_OK = 200;

export default {
  name: "operationLog",
  created() {
    this.getOperationLog();
  },
  props: {},
  data() {
    return {
      loading: true,
      operationLog: [],
      ps: 15,
      cp: 1,
    };
  },
  methods: {
    //获取操作日志
    getOperationLog() {
      this.$http
        .get("/operation-log/list", {
          params: {
            object_id: this.$route.params.projectId,
            type: "1,2,3,4",
            ps: this.ps,
            cp: this.cp,
          },
        })
        .then(
          (response) => {
            response = response.data;
            if (response.code === CODE_OK) {
              this.operationLog = response.data;
            } else {
              this.$message.error("获取数据失败");
            }
            this.loading = false;
          },
          (res) => {
            let response = res.data;
            this.$message.error(
              "获取数据-操作失败!" + !response.msg ? response.msg : ""
            );
          }
        );
    },
  },
};
</script>

<style lang="scss"  scoped>
.message {
  height: 700px;
}
</style>

