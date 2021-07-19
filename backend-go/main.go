package main

import (
	"fast-duck/goApiDoc/app/api"
	"fast-duck/goApiDoc/config"
	"fmt"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
	"github.com/go-redis/redis"
	"github.com/spf13/viper"
	"go.uber.org/zap"
	"gorm.io/driver/mysql"
	"gorm.io/gorm"
)

var Conf *config.Config
var Viper *viper.Viper
var Db *gorm.DB
var Log zap.Logger
var Redis *redis.Client

func main() {

	//读取配置
	Viper = viper.New()
	Viper.SetConfigFile("config.yaml")
	Viper.AddConfigPath(".")
	err := Viper.ReadInConfig()
	if err != nil {
		panic(fmt.Errorf("Fatal error config file: %w \n", err))
	}

	err = Viper.Unmarshal(&Conf)
	if err != nil {
		panic("解析app配置失败")
	}
	fmt.Printf("%+v \n", Conf)

	//初始化redis
	Redis = redis.NewClient(&redis.Options{
		Addr:     fmt.Sprintf("%s:%d", Conf.Redis.Host, Conf.Redis.Port),
		Password: Conf.Redis.Password, // no password set
		DB:       Conf.Redis.DbId,     // use default DB)
	})

	if Redis.Ping().Err() != nil {
		panic("链接redis失败")
	}

	mysqlPort := strconv.Itoa(Conf.Mysql.Port)
	//初始化数据库
	dsn := Conf.Mysql.User + ":" + Conf.Mysql.Password + "@tcp(" + Conf.Mysql.Host + ":" + mysqlPort + ")/" +
		Conf.Mysql.DbName + "?charset=" + Conf.Mysql.Charset + "&parseTime=True&loc=Local"
	MyDb, err := gorm.Open(mysql.Open(dsn), &gorm.Config{})
	if err != nil {
		fmt.Println(err.Error())
		fmt.Println(dsn)
		panic("初始化mysql失败")
	}

	fmt.Println(MyDb.Config)

	//打印信息
	fmt.Println("开启api-doc-go重构第一个版本")
	r := gin.Default()
	r.GET("/ping", func(c *gin.Context) {
		c.JSON(http.StatusOK, gin.H{
			"msg": "pong",
		})
	})

	api.ApiTest()

	r.Run()
}
