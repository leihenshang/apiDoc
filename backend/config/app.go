package config

const GIN_MODE_RELEASE = "release"
const GIN_MODE_DEV = "dev"

type App struct {
	Host    string
	Port    int
	Name    string
	RunMode string
}

func (app *App) IsRelease() bool {
	return app.RunMode == GIN_MODE_RELEASE
}

func (app *App) IsDEV() bool {
	return app.RunMode == GIN_MODE_DEV
}
