package api

import (
	"fast-duck/goApiDoc/global"
	"fmt"
)

func ApiTest() {
	fmt.Println(global.MyViper.AllSettings())
	fmt.Println("this is ApiTest")
}
