package global

import (
	"fast-duck/goApiDoc/config"

	"github.com/go-redis/redis"
	"github.com/spf13/viper"
	"go.uber.org/zap"
	"gorm.io/gorm"
)

var Conf *config.Config
var Viper *viper.Viper
var Db *gorm.DB
var Log zap.Logger
var Redis *redis.Client
