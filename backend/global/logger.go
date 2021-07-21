package global

import (
	"os"

	"go.uber.org/zap"
	"go.uber.org/zap/zapcore"
)

func initLogger() {
	writeSyncyer := getLogWriter()
	encoder := getEncoder()
	core := zapcore.NewCore(encoder, writeSyncyer, zapcore.DebugLevel)
	logger := zap.New(core)
	MyLogger = logger
}

func getEncoder() zapcore.Encoder {
	return zapcore.NewJSONEncoder(zap.NewProductionEncoderConfig())
}

func getLogWriter() zapcore.WriteSyncer {
	file, _ := os.Create("./test.log")
	return zapcore.AddSync(file)
}
