const CODE_OK = 200;
export default {
    methods: {
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
    }
}