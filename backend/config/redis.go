package config

type Redis struct {
	Enabled  bool
	Host     string
	Port     int
	User     string
	Password string
	DbId     int
}
