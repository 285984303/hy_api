define("echarts/chart/funnel", ["require", "./base", "zrender/shape/Text", "zrender/shape/Line", "zrender/shape/Polygon", "../config", "../util/ecData", "../util/number", "zrender/tool/util", "zrender/tool/color", "zrender/tool/area", "../chart"], function (e) {
    function t(e, t, n, a, o) {
        i.call(this, e, t, n, a, o), this.refresh(a)
    }

    var i = e("./base"), n = e("zrender/shape/Text"), a = e("zrender/shape/Line"), o = e("zrender/shape/Polygon"), r = e("../config");
    r.funnel = {
        zlevel: 0,
        z: 2,
        clickable: !0,
        legendHoverLink: !0,
        x: 80,
        y: 60,
        x2: 80,
        y2: 60,
        min: 0,
        max: 100,
        minSize: "0%",
        maxSize: "100%",
        sort: "descending",
        gap: 0,
        funnelAlign: "center",
        itemStyle: {
            normal: {
                borderColor: "#fff",
                borderWidth: 1,
                label: {show: !0, position: "outer"},
                labelLine: {show: !0, length: 10, lineStyle: {width: 1, type: "solid"}}
            }, emphasis: {borderColor: "rgba(0,0,0,0)", borderWidth: 1, label: {show: !0}, labelLine: {show: !0}}
        }
    };
    var s = e("../util/ecData"), l = e("../util/number"), h = e("zrender/tool/util"), m = e("zrender/tool/color"), V = e("zrender/tool/area");
    return t.prototype = {
        type: r.CHART_TYPE_FUNNEL, _buildShape: function () {
            var e = this.series, t = this.component.legend;
            this._paramsMap = {}, this._selected = {}, this.selectedMap = {};
            for (var i, n = 0, a = e.length; a > n; n++)if (e[n].type === r.CHART_TYPE_FUNNEL) {
                if (e[n] = this.reformOption(e[n]), this.legendHoverLink = e[n].legendHoverLink || this.legendHoverLink, i = e[n].name || "", this.selectedMap[i] = t ? t.isSelected(i) : !0, !this.selectedMap[i])continue;
                this._buildSingleFunnel(n), this.buildMark(n)
            }
            this.addShapeList()
        }, _buildSingleFunnel: function (e) {
            var t = this.component.legend, i = this.series[e], n = this._mapData(e), a = this._getLocation(e);
            this._paramsMap[e] = {location: a, data: n};
            for (var o, r = 0, s = [], h = 0, m = n.length; m > h; h++)o = n[h].name, this.selectedMap[o] = t ? t.isSelected(o) : !0, this.selectedMap[o] && !isNaN(n[h].value) && (s.push(n[h]), r++);
            if (0 !== r) {
                for (var V, U, d, p, c = this._buildFunnelCase(e), u = i.funnelAlign, y = i.gap, g = r > 1 ? (a.height - (r - 1) * y) / r : a.height, b = a.y, f = "descending" === i.sort ? this._getItemWidth(e, s[0].value) : l.parsePercent(i.minSize, a.width), k = "descending" === i.sort ? 1 : 0, _ = a.centerX, x = [], h = 0, m = s.length; m > h; h++)if (o = s[h].name, this.selectedMap[o] && !isNaN(s[h].value)) {
                    switch (V = m - 2 >= h ? this._getItemWidth(e, s[h + k].value) : "descending" === i.sort ? l.parsePercent(i.minSize, a.width) : l.parsePercent(i.maxSize, a.width), u) {
                        case"left":
                            U = a.x;
                            break;
                        case"right":
                            U = a.x + a.width - f;
                            break;
                        default:
                            U = _ - f / 2
                    }
                    d = this._buildItem(e, s[h]._index, t ? t.getColor(o) : this.zr.getColor(s[h]._index), U, b, f, V, g, u), b += g + y, p = d.style.pointList, x.unshift([p[0][0] - 10, p[0][1]]), x.push([p[1][0] + 10, p[1][1]]), 0 === h && (0 === f ? (p = x.pop(), "center" == u && (x[0][0] += 10), "right" == u && (x[0][0] = p[0]), x[0][1] -= "center" == u ? 10 : 15, 1 == m && (p = d.style.pointList)) : (x[x.length - 1][1] -= 5, x[0][1] -= 5)), f = V
                }
                c && (x.unshift([p[3][0] - 10, p[3][1]]), x.push([p[2][0] + 10, p[2][1]]), 0 === f ? (p = x.pop(), "center" == u && (x[0][0] += 10), "right" == u && (x[0][0] = p[0]), x[0][1] += "center" == u ? 10 : 15) : (x[x.length - 1][1] += 5, x[0][1] += 5), c.style.pointList = x)
            }
        }, _buildFunnelCase: function (e) {
            var t = this.series[e];
            if (this.deepQuery([t, this.option], "calculable")) {
                var i = this._paramsMap[e].location, n = 10, a = {
                    hoverable: !1,
                    style: {
                        pointListd: [[i.x - n, i.y - n], [i.x + i.width + n, i.y - n], [i.x + i.width + n, i.y + i.height + n], [i.x - n, i.y + i.height + n]],
                        brushType: "stroke",
                        lineWidth: 1,
                        strokeColor: t.calculableHolderColor || this.ecTheme.calculableHolderColor || r.calculableHolderColor
                    }
                };
                return s.pack(a, t, e, void 0, -1), this.setCalculable(a), a = new o(a), this.shapeList.push(a), a
            }
        }, _getLocation: function (e) {
            var t = this.series[e], i = this.zr.getWidth(), n = this.zr.getHeight(), a = this.parsePercent(t.x, i), o = this.parsePercent(t.y, n), r = null == t.width ? i - a - this.parsePercent(t.x2, i) : this.parsePercent(t.width, i);
            return {
                x: a,
                y: o,
                width: r,
                height: null == t.height ? n - o - this.parsePercent(t.y2, n) : this.parsePercent(t.height, n),
                centerX: a + r / 2
            }
        }, _mapData: function (e) {
            function t(e, t) {
                return "-" === e.value ? 1 : "-" === t.value ? -1 : t.value - e.value
            }

            function i(e, i) {
                return -t(e, i)
            }

            for (var n = this.series[e], a = h.clone(n.data), o = 0, r = a.length; r > o; o++)a[o]._index = o;
            return "none" != n.sort && a.sort("descending" === n.sort ? t : i), a
        }, _buildItem: function (e, t, i, n, a, o, r, l, h) {
            var m = this.series, V = m[e], U = V.data[t], d = this.getPolygon(e, t, i, n, a, o, r, l, h);
            s.pack(d, m[e], e, m[e].data[t], t, m[e].data[t].name), this.shapeList.push(d);
            var p = this.getLabel(e, t, i, n, a, o, r, l, h);
            s.pack(p, m[e], e, m[e].data[t], t, m[e].data[t].name), this.shapeList.push(p), this._needLabel(V, U, !1) || (p.invisible = !0);
            var c = this.getLabelLine(e, t, i, n, a, o, r, l, h);
            this.shapeList.push(c), this._needLabelLine(V, U, !1) || (c.invisible = !0);
            var u = [], y = [];
            return this._needLabelLine(V, U, !0) && (u.push(c.id), y.push(c.id)), this._needLabel(V, U, !0) && (u.push(p.id), y.push(d.id)), d.hoverConnect = u, p.hoverConnect = y, d
        }, _getItemWidth: function (e, t) {
            var i = this.series[e], n = this._paramsMap[e].location, a = i.min, o = i.max, r = l.parsePercent(i.minSize, n.width), s = l.parsePercent(i.maxSize, n.width);
            return (t - a) * (s - r) / (o - a) + r
        }, getPolygon: function (e, t, i, n, a, r, s, l, h) {
            var V, U = this.series[e], d = U.data[t], p = [d, U], c = this.deepMerge(p, "itemStyle.normal") || {}, u = this.deepMerge(p, "itemStyle.emphasis") || {}, y = this.getItemStyleColor(c.color, e, t, d) || i, g = this.getItemStyleColor(u.color, e, t, d) || ("string" == typeof y ? m.lift(y, -.2) : y);
            switch (h) {
                case"left":
                    V = n;
                    break;
                case"right":
                    V = n + (r - s);
                    break;
                default:
                    V = n + (r - s) / 2
            }
            var b = {
                zlevel: U.zlevel,
                z: U.z,
                clickable: this.deepQuery(p, "clickable"),
                style: {
                    pointList: [[n, a], [n + r, a], [V + s, a + l], [V, a + l]],
                    brushType: "both",
                    color: y,
                    lineWidth: c.borderWidth,
                    strokeColor: c.borderColor
                },
                highlightStyle: {color: g, lineWidth: u.borderWidth, strokeColor: u.borderColor}
            };
            return this.deepQuery([d, U, this.option], "calculable") && (this.setCalculable(b), b.draggable = !0), new o(b)
        }, getLabel: function (e, t, i, a, o, r, s, l, U) {
            var d, p = this.series[e], c = p.data[t], u = this._paramsMap[e].location, y = h.merge(h.clone(c.itemStyle) || {}, p.itemStyle), g = "normal", b = y[g].label, f = b.textStyle || {}, k = y[g].labelLine.length, _ = this.getLabelText(e, t, g), x = this.getFont(f), L = i;
            b.position = b.position || y.normal.label.position, "inner" === b.position || "inside" === b.position || "center" === b.position ? (d = U, L = Math.max(r, s) / 2 > V.getTextWidth(_, x) ? "#fff" : m.reverse(i)) : d = "left" === b.position ? "right" : "left";
            var W = {
                zlevel: p.zlevel,
                z: p.z + 1,
                style: {
                    x: this._getLabelPoint(b.position, a, u, r, s, k, U),
                    y: o + l / 2,
                    color: f.color || L,
                    text: _,
                    textAlign: f.align || d,
                    textBaseline: f.baseline || "middle",
                    textFont: x
                }
            };
            return g = "emphasis", b = y[g].label || b, f = b.textStyle || f, k = y[g].labelLine.length || k, b.position = b.position || y.normal.label.position, _ = this.getLabelText(e, t, g), x = this.getFont(f), L = i, "inner" === b.position || "inside" === b.position || "center" === b.position ? (d = U, L = Math.max(r, s) / 2 > V.getTextWidth(_, x) ? "#fff" : m.reverse(i)) : d = "left" === b.position ? "right" : "left", W.highlightStyle = {
                x: this._getLabelPoint(b.position, a, u, r, s, k, U),
                color: f.color || L,
                text: _,
                textAlign: f.align || d,
                textFont: x,
                brushType: "fill"
            }, new n(W)
        }, getLabelText: function (e, t, i) {
            var n = this.series, a = n[e], o = a.data[t], r = this.deepQuery([o, a], "itemStyle." + i + ".label.formatter");
            return r ? "function" == typeof r ? r.call(this.myChart, {
                seriesIndex: e,
                seriesName: a.name || "",
                series: a,
                dataIndex: t,
                data: o,
                name: o.name,
                value: o.value
            }) : "string" == typeof r ? r = r.replace("{a}", "{a0}").replace("{b}", "{b0}").replace("{c}", "{c0}").replace("{a0}", a.name).replace("{b0}", o.name).replace("{c0}", o.value) : void 0 : o.name
        }, getLabelLine: function (e, t, i, n, o, r, s, l, m) {
            var V = this.series[e], U = V.data[t], d = this._paramsMap[e].location, p = h.merge(h.clone(U.itemStyle) || {}, V.itemStyle), c = "normal", u = p[c].labelLine, y = p[c].labelLine.length, g = u.lineStyle || {}, b = p[c].label;
            b.position = b.position || p.normal.label.position;
            var f = {
                zlevel: V.zlevel,
                z: V.z + 1,
                hoverable: !1,
                style: {
                    xStart: this._getLabelLineStartPoint(n, d, r, s, m),
                    yStart: o + l / 2,
                    xEnd: this._getLabelPoint(b.position, n, d, r, s, y, m),
                    yEnd: o + l / 2,
                    strokeColor: g.color || i,
                    lineType: g.type,
                    lineWidth: g.width
                }
            };
            return c = "emphasis", u = p[c].labelLine || u, y = p[c].labelLine.length || y, g = u.lineStyle || g, b = p[c].label || b, b.position = b.position, f.highlightStyle = {
                xEnd: this._getLabelPoint(b.position, n, d, r, s, y, m),
                strokeColor: g.color || i,
                lineType: g.type,
                lineWidth: g.width
            }, new a(f)
        }, _getLabelPoint: function (e, t, i, n, a, o, r) {
            switch (e = "inner" === e || "inside" === e ? "center" : e) {
                case"center":
                    return "center" == r ? t + n / 2 : "left" == r ? t + 10 : t + n - 10;
                case"left":
                    return "auto" === o ? i.x - 10 : "center" == r ? i.centerX - Math.max(n, a) / 2 - o : "right" == r ? t - (a > n ? a - n : 0) - o : i.x - o;
                default:
                    return "auto" === o ? i.x + i.width + 10 : "center" == r ? i.centerX + Math.max(n, a) / 2 + o : "right" == r ? i.x + i.width + o : t + Math.max(n, a) + o
            }
        }, _getLabelLineStartPoint: function (e, t, i, n, a) {
            return "center" == a ? t.centerX : n > i ? e + Math.min(i, n) / 2 : e + Math.max(i, n) / 2
        }, _needLabel: function (e, t, i) {
            return this.deepQuery([t, e], "itemStyle." + (i ? "emphasis" : "normal") + ".label.show")
        }, _needLabelLine: function (e, t, i) {
            return this.deepQuery([t, e], "itemStyle." + (i ? "emphasis" : "normal") + ".labelLine.show")
        }, refresh: function (e) {
            e && (this.option = e, this.series = e.series), this.backupShapeList(), this._buildShape()
        }
    }, h.inherits(t, i), e("../chart").define("funnel", t), t
});