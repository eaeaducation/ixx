jQuery.fn.TableCSVExport = function (k) {
    var k = jQuery.extend({
        separator: ",",
        header: [],
        columns: [],
        extraHeader: "",
        extraData: [],
        insertBefore: "",
        delivery: "popup",
        emptyValue: "",
        showHiddenRows: false,
        rowFilter: "",
        filename: "download.csv"
    }, k);
    var g = [];
    var b = this;
    var e = k.columns.length == 0 ? true : false;
    var m = [];
    var c = 0;
    var r = null;
    var j = k.header.length;
    var s = [];
    if (j > 0) {
        if (e) {
            for (var t = 0; t < j; t++) {
                if (k.header[t] == k.insertBefore) {
                    s[s.length] = n(k.extraHeader);
                    r = t
                }
                s[s.length] = n(k.header[t])
            }
        } else {
            if (!e) {
                for (var p = 0; p < j; p++) {
                    for (var t = 0; t < k.columns.length; t++) {
                        if (k.columns[t] == k.header[p]) {
                            if (k.columns[t] == k.insertBefore) {
                                s[s.length] = n(k.extraHeader);
                                r = p
                            }
                            s[s.length] = n(k.header[p]);
                            m[c] = p;
                            c++
                        }
                    }
                }
            }
        }
    } else {
        q(b).find("th").each(function () {
            if ((jQuery(this).css("display") != "none" || k.showHiddenRows) && !jQuery(this).hasClass("hidden-print")) {
                s[s.length] = n(jQuery(this).html())
            }
        })
    }
    d(s);
    if (e) {
        var f = 0;
        h(b).each(function () {
            var o = [];
            var i = 0;
            q(this).find("td").each(function () {
                if (i == r) {
                    o[o.length] = jQuery.trim(k.extraData[f - 1])
                }
                if ((jQuery(this).css("display") != "none" || k.showHiddenRows) && !jQuery(this).hasClass("hidden-print")) {
                    if (jQuery.trim(jQuery(this).html()) == "") {
                        o[o.length] = n(k.emptyValue)
                    } else {
                        o[o.length] = jQuery.trim(n(jQuery(this).html()))
                    }
                }
                i++
            });
            d(o);
            f++
        })
    } else {
        var f = 0;
        h(b).each(function () {
            var o = [];
            var u = 0;
            var i = 0;
            q(this).find("td").each(function () {
                if ((u in m) && (i == r)) {
                    o[o.length] = jQuery.trim(n(k.extraData[f - 1]))
                }
                if ((jQuery(this).css("display") != "none" || k.showHiddenRows) && (u in m)) {
                    o[o.length] = jQuery.trim(n(jQuery(this).html()))
                }
                u++;
                i++
            });
            d(o);
            f++
        })
    }

    function h(i) {
        return jQuery(i).find("tr" + k.rowFilter)
    }

    function q(i) {
        if (k.showHiddenRows) {
            return jQuery(i)
        } else {
            return jQuery(i).filter(":visible")
        }
    }

    if ((k.delivery == "popup") || (k.delivery == "download")) {
        var l = g.join("\n");
        return a(l)
    } else {
        var l = g.join("\n");
        return l
    }

    function d(u) {
        var o = u.join("");
        if (u.length > 0 && o != "") {
            var i = u.join(k.separator);
            g[g.length] = jQuery.trim(i)
        }
    }

    function n(o) {
        var i = new RegExp(/["]/g);
        var u = o.replace(i, '""');
        u = u.replace("<br>", " ");
        if (!(u != null && typeof u === "object")) {
            u = "<span>" + u + "</span>"
        }
        u = $(u).text().trim();
        if (u == "") {
            return ""
        }
        u = u.replace("\n", '');
        u = u.replace(/\s/g, '');
        if (/^\d{10,}/.test(u)) {
            return '="' + u + '"'
        }
        return '"' + u + '"'
    }

    function a(w) {
        if (k.delivery == "download") {
            var i = new Blob(["\ufeff" + w], {type: "text/csv;charset=utf-8;"});
            if (navigator.msSaveBlob) {
                navigator.msSaveBlob(i, k.filename)
            } else {
                var v = document.createElement("a");
                var u = URL.createObjectURL(i);
                var o = navigator.userAgent.indexOf("Safari") != -1 && navigator.userAgent.indexOf("Chrome") == -1;
                if (o) {
                    v.setAttribute("target", "_blank")
                }
                v.setAttribute("href", u);
                v.setAttribute("download", k.filename);
                v.style = "visibility:hidden";
                document.body.appendChild(v);
                v.click();
                document.body.removeChild(v)
            }
            return true
        } else {
            var x = window.open("", "csv", "height=400,width=600");
            x.document.write("<html><head><title>CSV</title>");
            x.document.write("</head><body >");
            x.document.write('<textArea cols=70 rows=15 wrap="off" >');
            x.document.write(w);
            x.document.write("</textArea>");
            x.document.write("</body></html>");
            x.document.close();
            return true
        }
    }
};