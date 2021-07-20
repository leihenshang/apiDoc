<template>
  <div class="select-component">
    <el-select
      v-model="contentFilterData.to_user_id"
      :no-match-text="contentFilterData.to_user_info ? contentFilterData.to_user_info.nick_name : ''"
      filterable
      remote
      clearable
      placeholder="选择用户"
      :remote-method="userFilterSearch"
      :loading="userFilterLoading"
    >
      <el-option
        v-for="item in userFilterOptionsData"
        :key="item.id"
        :label="item.nick_name"
        :value="item.id"
      ></el-option>
    </el-select>
  </div>
</template>

<script>
export default {
  name: "userSearchSelect",
  props: {
    myTargetObject: Object,
    userFilterOptions: Array,
  },
  data() {
    return {
      userFilterLoading: false,
      userFilterOptionsData: this.userFilterOptions,
      contentFilterData: this.myTargetObject,
    };
  },
  methods: {
    userFilterSearch(query) {
      if (query !== "") {
        this.userFilterLoading = true;
        this.$http
          .get("/user/list", {
            params: {
              query,
              projectId: this.$route.params.projectId,
            },
          })
          .then((response) => {
            response = response.data;
            if (response.code === 200) {
              this.userFilterOptionsData = response.data.items;
            }
            this.userFilterLoading = false;
          });
      } else {
        this.userFilterOptionsData = [];
      }
    },
  },
};
</script>

<style>
</style>