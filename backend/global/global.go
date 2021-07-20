package global

import (
	"fastduck/apidoc/config"
	"fmt"
	"strconv"

	"github.com/go-redis/redis"
	"github.com/spf13/viper"
	"go.uber.org/zap"
	"gorm.io/driver/mysql"
	"gorm.io/gorm"
)

var MyConf *config.Config
var MyViper *viper.Viper
var MyDb *gorm.DB
var MyLogger *zap.Logger
var MyRedis *redis.Client

func init() {
	fmt.Println("执行了一次global init")
	initConf()
	fmt.Println("初始化配置完成")
	initLog()
	fmt.Println("初始化日志完成")
	initRedis()
	fmt.Println("初始化redis完成")
	initMysql()
	fmt.Println("初始化mysql完成")
}

func initConf() {
	//读取配置
	MyViper = viper.New()
	MyViper.SetConfigFile("config.yaml")
	MyViper.AddConfigPath(".")
	err := MyViper.ReadInConfig()
	if err != nil {
		panic(fmt.Errorf("Fatal error config file: %w \n", err))
	}

	err = MyViper.Unmarshal(&MyConf)
	if err != nil {
		panic("解析app配置失败")
	}
}

func initMysql() {
	mysqlPort := strconv.Itoa(MyConf.Mysql.Port)
	var err error
	//初始化数据库
	dsn := MyConf.Mysql.User + ":" + MyConf.Mysql.Password + "@tcp(" + MyConf.Mysql.Host + ":" + mysqlPort + ")/" +
		MyConf.Mysql.DbName + "?charset=" + MyConf.Mysql.Charset + "&parseTime=True&loc=Local"
	MyDb, err = gorm.Open(mysql.Open(dsn), &gorm.Config{})
	if err != nil {
		fmt.Println(err.Error())
		fmt.Println(dsn)
		panic("初始化mysql失败")
	}

}

func initLog() {
	MyLogger, _ = zap.NewProduction()
}

func initRedis() {
	//初始化redis
	MyRedis = redis.NewClient(&redis.Options{
		Addr:     fmt.Sprintf("%s:%d", MyConf.Redis.Host, MyConf.Redis.Port),
		Password: MyConf.Redis.Password, // no password set
		DB:       MyConf.Redis.DbId,     // use default DB)
	})

	if MyRedis.Ping().Err() != nil {
		panic("链接redis失败")
	}
}
