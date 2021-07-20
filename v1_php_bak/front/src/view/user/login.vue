
<template>
  <div class="login-page">
    <div class="login-page-content">
      <div class="title">
        <span>apiDoc</span>
      </div>
      <div class="login-box" v-loading="loading">
        <el-form :inline="true" :model="form" :rules="rules" ref="form" @submit.native.prevent>
          <el-form-item prop="name">
            <el-input v-model="form.name" placeholder="用户名" style="width: 240px"></el-input>
          </el-form-item>
          <el-form-item prop="pwd">
            <el-input
              v-model="form.pwd"
              type="password"
              placeholder="密码"
              style="width: 240px"
              @keyup.enter.native="login()"
            ></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="login()" icon="el-icon-arrow-left">登录</el-button>
          </el-form-item>
        </el-form>
      </div>
      <!-- <div class="remember">
          <input type="checkbox" name="remember" id="remember" />
          <label for="remember">记住密码</label>
      </div>-->
    </div>
  </div>
</template>

<script>
const CODE_OK = 200;
// const NO_ACTIVATE = 3;

export default {
  name: "loginPage",
  created() {
    this.loginByLocalStorage();
  },
  data() {
    return {
      form: {
        name: "",
        pwd: "",
      },
      rules: {
        name: [{ required: true, message: "请输入用户名", trigger: "blur" }],
        pwd: [{ required: true, message: "请输入密码", trigger: "blur" }],
      },
      loading: false,
    };
  },
  methods: {
    //用户登录
    login() {
      this.$refs.form.validate((validate) => {
        if (validate) {
          this.loading = true;
          this.$http
            .post("/user/login", {
              ...this.form,
            })
            .then(
              (response) => {
                response = response.data;
                if (response.code === CODE_OK) {
                  let userInfo = response.data;
                  this.$store.commit("saveUserInfo", userInfo);
                  localStorage.setItem("userInfo", JSON.stringify(userInfo));
                  this.$router.push("/").catch(() => {});
                } else {
                  this.$message.error(response.msg);
                  localStorage.removeItem("userInfo");
                  this.$router.push("/login").catch(() => {});
                }
                this.loading = false;
              },
              () => {
                this.$message.error("请求失败!");
              }
            );
        } else {
          return false;
        }
      });
    },
    //通过localStorage登录
    loginByLocalStorage() {
      let userInfo1 = JSON.parse(localStorage.getItem("userInfo"));
      if (userInfo1) {
        let currDate = new Date();
        let expireTime = new Date(Date.parse(userInfo1.token_expire_time));
        if (expireTime > currDate) {
          this.$store.commit("saveUserInfo", userInfo1);
          this.$router.push("/");
        } else {
          localStorage.removeItem("userInfo");
        }
      }
    },
  },
  components: {},
};
</script>
<style>
/* 中间内容开始 */
.login-page-content {
  position: absolute;
  left: 50%;
  top: 30%;
  transform: translate(-50%);
}

.login-page-content .title {
  height: 140px;
  font-size: 50px;
  line-height: 140px;
  text-align: center;
}

span.login-text {
  color: white;
}

.remember {
  text-align: right;
  margin-top: 10px;
  font-size: 14px;
  color: #666;
  height: 20px;
  line-height: 20px;
}

.remember input {
  vertical-align: middle;
}

.remember label {
  vertical-align: middle;
  cursor: pointer;
}
.remember label:hover {
  color: black;
}

/* 中间内容结束 */
</style>
