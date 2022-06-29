package query

type SiteInfo struct {
	Domain string `json:"domain"`
	Path   string `json:"path"`
	SiteUv int64  `json:"site-uv"`
	SitePv int64  `json:"site-pv"`
	PathUv int64  `json:"path-uv"`
	PathPv int64  `json:"path-pv"`
}
