<template>
  <div class="info">
    <ul>
      <li>
        <span class="span-label">分组:</span>
        <el-select v-model="content.groupId" placeholder="请选择" @change="createChildGroupList()">
          <el-option v-for="item in groupList" :key="item.id" :label="item.title" :value="item.id"></el-option>
        </el-select>
        <span class="span-label">二级分组(可选):</span>
        <el-select v-model="content.groupIdSecond" placeholder="请选择">
          <el-option
            v-for="item in childGroups"
            :key="item.id"
            :label="item.title"
            :value="item.id"
          ></el-option>
        </el-select>
      </li>
      <li>
        <span class="span-label">标题:</span>
        <el-input v-model="content.title" placeholder="请输入标题" style="width:50%"></el-input>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: {
    groupList: [Array, Object],
    doc: Object,
    isUpdate: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      content: {
        groupId: null,
        groupIdSecond: null,
        title: "",
      },
      childGroups: [],
    };
  },
  created() {},
  methods: {
    createChildGroupList() {
      for (const item of this.groupList) {
        if (item["id"] == this.content.groupId) {
          this.childGroups = item["childs"];
        }
      }
    },
    updateChilds() {
      if (this.isUpdate && this.content.groupId) {
        let val = this.groupList;
        for (const key in val) {
          if (Object.hasOwnProperty.call(val, key)) {
            const element = val[key];
            if (element.id == this.content.groupId) {
              this.childGroups = element.childs;
              break;
            }
          }
        }
      }
    },
  },
  watch: {
    content: {
      handler: function (val) {
        this.$emit("update-info", val);
      },
      deep: true,
    },
    doc: function (val) {
      this.content.groupId = val.group_id;
      this.content.groupIdSecond = val.group_id_second;
      this.content.title = val.title;
      this.updateChilds();
    },
    groupList: function () {
      this.updateChilds();
    },
  },
};
</script>
<style lang="scss" scoped>
.info {
  ul {
    margin: 0;
    padding: 0;
    li {
      padding: 5px 0;
      list-style: none;
    }
  }

  li .span-label {
    display: inline-block;
    min-width: 50px;
    text-align: right;
    padding-right: 20px;
    font-size: 12px;
    font-weight: 600;
    &:nth-child(3) {
      margin-left: 10px;
    }
  }
}
</style>