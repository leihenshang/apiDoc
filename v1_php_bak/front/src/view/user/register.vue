<template>
  <div class="register">
    <div class="middle">
      <h4>用户注册</h4>
      <ValidationObserver ref="form" vid="form" tag="div">
        <ul>
          <li>
            <span>用户名</span>
            <validation-provider
              rules="required"
              v-slot="{ errors }"
              vid="name"
              name="name"
              tag="i"
            >
              <input type="text" name="name" v-model="userInfo.name" placeholder="请输入昵称" />
              <em>{{ errors[0] }}</em>
            </validation-provider>
          </li>
          <li>
            <span>邮箱</span>
            <validation-provider
              rules="required|email"
              v-slot="{ errors }"
              vid="email"
              name="email"
              tag="i"
            >
              <input type="email" name="email" v-model="userInfo.email" placeholder="请输入邮箱" />
              <em>{{ errors[0] }}</em>
            </validation-provider>
          </li>
          <li>
            <span>密码</span>
            <validation-provider rules="required" v-slot="{ errors }" vid="pwd" name="pwd" tag="i">
              <input
                type="password"
                name="pwd"
                v-model="userInfo.pwd"
                placeholder="请输入密码"
                autocomplete="off"
              />
              <em>{{ errors[0] }}</em>
            </validation-provider>
          </li>
          <li>
            <span>重复密码</span>
            <validation-provider
              rules="required"
              v-slot="{ errors }"
              vid="re_pwd"
              name="re_pwd"
              tag="i"
            >
              <input type="password" name="re_pwd" v-model="userInfo.re_pwd" placeholder="请重复一次密码" />
              <em>{{ errors[0] }}</em>
            </validation-provider>
          </li>

          <li>
            <span></span>
            <button @click="register()">提交注册</button>
            <button>取消</button>
          </li>
        </ul>
      </ValidationObserver>
    </div>
  </div>
</template>
<script>


const CODE_OK = 200;
export default {
  name: "register",
  data() {
    return {
      userInfo: {
        pwd: "",
        re_pwd: "",
        name: "",
        email: "",
        code: "",
      },
      userInfoDefault: {
        pwd: "",
        re_pwd: "",
        name: "",
        email: "",
      },
    };
  },
  methods: {
    //用户注册
    register() {
      //执行验证
      this.$refs.form.validate().then((res) => {
        if (!res) {
          return;
        }
        this.$http
          .post("/user/reg", {
            ...this.userInfo,
          })
          .then(
            (response) => {
              response = response.data;
              if (response.code === CODE_OK) {
                this.$message.success("注册成功！");
                this.$router.push("/login");
              } else {
                this.$message.error("注册失败:" + response.msg);
              }
            },
            () => {
              this.$message.error("操作失败!");
            }
          );
      });
    },
  },
  components: {
  },
};
</script>
<style lang="scss" scoped>
.register .middle {
  border: 1px solid rgb(175, 175, 175);
  width: 50%;
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 750px;
  padding: 30px;
  border-radius: 10px;
  box-sizing: border-box;
}

.middle span {
  width: 100px;
  display: inline-block;
  text-align: right;
  margin-right: 10px;
}

.middle ul {
  margin: 0 0 0 50px;
}

.middle li {
  display: inline-block;
  width: auto;
  margin: 5px 0;
}
.middle input {
  width: 300px;
  height: 30px;
  margin-right: 10px;
}

.middle h4 {
  text-align: center;
  margin: 20px 0;
}

.middle button {
  height: 30px;
  width: 80px;
}

.middle em {
  color: red;
}
</style>