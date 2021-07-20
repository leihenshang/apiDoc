package model

import (
	"time"

	"gorm.io/gorm"
)

type BasicModel struct {
	ID         uint           `json:"id"  gorm:"primaryKey"`
	CreateTime time.Time      `json:"create_time"`
	UpdateTime time.Time      `json:"update_time"`
	DeleteTime gorm.DeletedAt `json:"delete_time" gorm:"index"`
}
