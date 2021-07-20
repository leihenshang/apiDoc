<template>
  <div class="detail-description">
    <div class="description">
      <el-input type="textarea" :rows="8" v-model="newDescription"></el-input>
    </div>
  </div>
</template>

<script>
export default {
  name: "detailDescription",
  props: {
    description: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      newDescription: this.description,
    };
  },
  watch: {
    newDescription: function (val) {
      this.$emit("update", val);
    },
    description: function () {
      this.newDescription = this.description;
    },
  },
};
</script>

<style lang="scss" scoped>
.description {
  padding-top: 10px;
  textarea {
    width: 100%;
    height: 100%;
    resize: none;
    outline: none;
    padding: 5px;
    box-sizing: border-box;
    border: 1px solid rgb(204, 203, 203);
  }
}
</style>