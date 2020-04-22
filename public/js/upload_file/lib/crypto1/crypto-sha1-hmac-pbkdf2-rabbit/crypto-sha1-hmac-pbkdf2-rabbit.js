/*
 * Crypto-JS v1.1.0
 * http://code.google.com/p/crypto-js/
 * Copyright (c) 2009, Jeff Mott. All rights reserved.
 * http://code.google.com/p/crypto-js/wiki/License
 */
(function () {
    var b = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    window.Crypto = {};
    var a = Crypto.util = {
        rotl: function (d, c) {
            return (d << c) | (d >>> (32 - c))
        }, rotr: function (d, c) {
            return (d << (32 - c)) | (d >>> c)
        }, endian: function (d) {
            if (d.constructor == Number) {
                return a.rotl(d, 8) & 16711935 | a.rotl(d, 24) & 4278255360
            }
            for (var c = 0; c < d.length; c++) {
                d[c] = a.endian(d[c])
            }
            return d
        }, randomBytes: function (d) {
            for (var c = []; d > 0; d--) {
                c.push(Math.floor(Math.random() * 256))
            }
            return c
        }, stringToBytes: function (e) {
            var c = [];
            for (var d = 0; d < e.length; d++) {
                c.push(e.charCodeAt(d))
            }
            return c
        }, bytesToString: function (c) {
            var e = [];
            for (var d = 0; d < c.length; d++) {
                e.push(String.fromCharCode(c[d]))
            }
            return e.join("")
        }, stringToWords: function (f) {
            var e = [];
            for (var g = 0, d = 0; g < f.length; g++, d += 8) {
                e[d >>> 5] |= f.charCodeAt(g) << (24 - d % 32)
            }
            return e
        }, bytesToWords: function (d) {
            var f = [];
            for (var e = 0, c = 0; e < d.length; e++, c += 8) {
                f[c >>> 5] |= d[e] << (24 - c % 32)
            }
            return f
        }, wordsToBytes: function (e) {
            var d = [];
            for (var c = 0; c < e.length * 32; c += 8) {
                d.push((e[c >>> 5] >>> (24 - c % 32)) & 255)
            }
            return d
        }, bytesToHex: function (c) {
            var e = [];
            for (var d = 0; d < c.length; d++) {
                e.push((c[d] >>> 4).toString(16));
                e.push((c[d] & 15).toString(16))
            }
            return e.join("")
        }, hexToBytes: function (e) {
            var d = [];
            for (var f = 0; f < e.length; f += 2) {
                d.push(parseInt(e.substr(f, 2), 16))
            }
            return d
        }, bytesToBase64: function (d) {
            if (typeof btoa == "function") {
                return btoa(a.bytesToString(d))
            }
            var c = [], f;
            for (var e = 0; e < d.length; e++) {
                switch (e % 3) {
                    case 0:
                        c.push(b.charAt(d[e] >>> 2));
                        f = (d[e] & 3) << 4;
                        break;
                    case 1:
                        c.push(b.charAt(f | (d[e] >>> 4)));
                        f = (d[e] & 15) << 2;
                        break;
                    case 2:
                        c.push(b.charAt(f | (d[e] >>> 6)));
                        c.push(b.charAt(d[e] & 63));
                        f = -1
                }
            }
            if (f != undefined && f != -1) {
                c.push(b.charAt(f))
            }
            while (c.length % 4 != 0) {
                c.push("=")
            }
            return c.join("")
        }, base64ToBytes: function (d) {
            if (typeof atob == "function") {
                return a.stringToBytes(atob(d))
            }
            d = d.replace(/[^A-Z0-9+\/]/ig, "");
            var c = [];
            for (var e = 0; e < d.length; e++) {
                switch (e % 4) {
                    case 1:
                        c.push((b.indexOf(d.charAt(e - 1)) << 2) | (b.indexOf(d.charAt(e)) >>> 4));
                        break;
                    case 2:
                        c.push(((b.indexOf(d.charAt(e - 1)) & 15) << 4) | (b.indexOf(d.charAt(e)) >>> 2));
                        break;
                    case 3:
                        c.push(((b.indexOf(d.charAt(e - 1)) & 3) << 6) | (b.indexOf(d.charAt(e))));
                        break
                }
            }
            return c
        }
    };
    Crypto.mode = {}
})();
(function () {
    var a = Crypto.util;
    var b = Crypto.SHA1 = function (e, c) {
        var d = a.wordsToBytes(b._sha1(e));
        return c && c.asBytes ? d : c && c.asString ? a.bytesToString(d) : a.bytesToHex(d)
    };
    b._sha1 = function (k) {
        var u = a.stringToWords(k), v = k.length * 8, o = [], q = 1732584193, p = -271733879, h = -1732584194, g = 271733878, f = -1009589776;
        u[v >> 5] |= 128 << (24 - v % 32);
        u[((v + 64 >>> 9) << 4) + 15] = v;
        for (var y = 0; y < u.length; y += 16) {
            var D = q, C = p, B = h, A = g, z = f;
            for (var x = 0; x < 80; x++) {
                if (x < 16) {
                    o[x] = u[y + x]
                } else {
                    var s = o[x - 3] ^ o[x - 8] ^ o[x - 14] ^ o[x - 16];
                    o[x] = (s << 1) | (s >>> 31)
                }
                var r = ((q << 5) | (q >>> 27)) + f + (o[x] >>> 0) + (x < 20 ? (p & h | ~p & g) + 1518500249 : x < 40 ? (p ^ h ^ g) + 1859775393 : x < 60 ? (p & h | p & g | h & g) - 1894007588 : (p ^ h ^ g) - 899497514);
                f = g;
                g = h;
                h = (p << 30) | (p >>> 2);
                p = q;
                q = r
            }
            q += D;
            p += C;
            h += B;
            g += A;
            f += z
        }
        return [q, p, h, g, f]
    };
    b._blocksize = 16
})();
(function () {
    var a = Crypto.util;
    Crypto.HMAC = function (g, h, f, d) {
        f = f.length > g._blocksize * 4 ? g(f, {asBytes: true}) : a.stringToBytes(f);
        var c = f, j = f.slice(0);
        for (var e = 0; e < g._blocksize * 4; e++) {
            c[e] ^= 92;
            j[e] ^= 54
        }
        var b = g(a.bytesToString(c) + g(a.bytesToString(j) + h, {asString: true}), {asBytes: true});
        return d && d.asBytes ? b : d && d.asString ? a.bytesToString(b) : a.bytesToHex(b)
    }
})();
(function () {
    var a = Crypto.util;
    Crypto.PBKDF2 = function (m, k, b, p) {
        var o = p && p.hasher || Crypto.SHA1, e = p && p.iterations || 1;

        function l(i, j) {
            return Crypto.HMAC(o, j, i, {asBytes: true})
        }

        var d = [], c = 1;
        while (d.length < b) {
            var f = l(m, k + a.bytesToString(a.wordsToBytes([c])));
            for (var n = f, h = 1; h < e; h++) {
                n = l(m, a.bytesToString(n));
                for (var g = 0; g < f.length; g++) {
                    f[g] ^= n[g]
                }
            }
            d = d.concat(f);
            c++
        }
        d.length = b;
        return p && p.asBytes ? d : p && p.asString ? a.bytesToString(d) : a.bytesToHex(d)
    }
})();
(function () {
    var e = Crypto.util;
    var d = [], g = [], a;
    var f = Crypto.Rabbit = {
        encrypt: function (j, i) {
            var b = e.stringToBytes(j), h = e.randomBytes(8), c = Crypto.PBKDF2(i, e.bytesToString(h), 16, {asBytes: true});
            f._rabbit(b, c, e.bytesToWords(h));
            return e.bytesToBase64(h.concat(b))
        }, decrypt: function (j, i) {
            var l = e.base64ToBytes(j), h = l.splice(0, 8), b = Crypto.PBKDF2(i, e.bytesToString(h), 16, {asBytes: true});
            f._rabbit(l, b, e.bytesToWords(h));
            return e.bytesToString(l)
        }, _rabbit: function (h, l, o) {
            f._keysetup(l);
            if (o) {
                f._ivsetup(o)
            }
            for (var q = [], p = 0; p < h.length; p++) {
                if (p % 16 == 0) {
                    f._nextstate();
                    q[0] = d[0] ^ (d[5] >>> 16) ^ (d[3] << 16);
                    q[1] = d[2] ^ (d[7] >>> 16) ^ (d[5] << 16);
                    q[2] = d[4] ^ (d[1] >>> 16) ^ (d[7] << 16);
                    q[3] = d[6] ^ (d[3] >>> 16) ^ (d[1] << 16);
                    for (var n = 0; n < 4; n++) {
                        q[n] = ((q[n] << 8) | (q[n] >>> 24)) & 16711935 | ((q[n] << 24) | (q[n] >>> 8)) & 4278255360
                    }
                    for (var c = 120; c >= 0; c -= 8) {
                        q[c / 8] = (q[c >>> 5] >>> (24 - c % 32)) & 255
                    }
                }
                h[p] ^= q[p % 16]
            }
        }, _keysetup: function (b) {
            d[0] = b[0];
            d[2] = b[1];
            d[4] = b[2];
            d[6] = b[3];
            d[1] = (b[3] << 16) | (b[2] >>> 16);
            d[3] = (b[0] << 16) | (b[3] >>> 16);
            d[5] = (b[1] << 16) | (b[0] >>> 16);
            d[7] = (b[2] << 16) | (b[1] >>> 16);
            g[0] = e.rotl(b[2], 16);
            g[2] = e.rotl(b[3], 16);
            g[4] = e.rotl(b[0], 16);
            g[6] = e.rotl(b[1], 16);
            g[1] = (b[0] & 4294901760) | (b[1] & 65535);
            g[3] = (b[1] & 4294901760) | (b[2] & 65535);
            g[5] = (b[2] & 4294901760) | (b[3] & 65535);
            g[7] = (b[3] & 4294901760) | (b[0] & 65535);
            a = 0;
            for (var c = 0; c < 4; c++) {
                f._nextstate()
            }
            for (var c = 0; c < 8; c++) {
                g[c] ^= d[(c + 4) & 7]
            }
        }, _ivsetup: function (b) {
            var l = e.endian(b[0]), j = e.endian(b[1]), k = (l >>> 16) | (j & 4294901760), h = (j << 16) | (l & 65535);
            g[0] ^= l;
            g[1] ^= k;
            g[2] ^= j;
            g[3] ^= h;
            g[4] ^= l;
            g[5] ^= k;
            g[6] ^= j;
            g[7] ^= h;
            for (var c = 0; c < 4; c++) {
                f._nextstate()
            }
        }, _nextstate: function () {
            for (var c = [], h = 0; h < 8; h++) {
                c[h] = g[h]
            }
            g[0] = (g[0] + 1295307597 + a) >>> 0;
            g[1] = (g[1] + 3545052371 + ((g[0] >>> 0) < (c[0] >>> 0) ? 1 : 0)) >>> 0;
            g[2] = (g[2] + 886263092 + ((g[1] >>> 0) < (c[1] >>> 0) ? 1 : 0)) >>> 0;
            g[3] = (g[3] + 1295307597 + ((g[2] >>> 0) < (c[2] >>> 0) ? 1 : 0)) >>> 0;
            g[4] = (g[4] + 3545052371 + ((g[3] >>> 0) < (c[3] >>> 0) ? 1 : 0)) >>> 0;
            g[5] = (g[5] + 886263092 + ((g[4] >>> 0) < (c[4] >>> 0) ? 1 : 0)) >>> 0;
            g[6] = (g[6] + 1295307597 + ((g[5] >>> 0) < (c[5] >>> 0) ? 1 : 0)) >>> 0;
            g[7] = (g[7] + 3545052371 + ((g[6] >>> 0) < (c[6] >>> 0) ? 1 : 0)) >>> 0;
            a = (g[7] >>> 0) < (c[7] >>> 0) ? 1 : 0;
            for (var j = [], h = 0; h < 8; h++) {
                var l = (d[h] + g[h]) >>> 0;
                var n = l & 65535, k = l >>> 16;
                var b = ((((n * n) >>> 17) + n * k) >>> 15) + k * k, m = (((l & 4294901760) * l) >>> 0) + (((l & 65535) * l) >>> 0) >>> 0;
                j[h] = b ^ m
            }
            d[0] = j[0] + ((j[7] << 16) | (j[7] >>> 16)) + ((j[6] << 16) | (j[6] >>> 16));
            d[1] = j[1] + ((j[0] << 8) | (j[0] >>> 24)) + j[7];
            d[2] = j[2] + ((j[1] << 16) | (j[1] >>> 16)) + ((j[0] << 16) | (j[0] >>> 16));
            d[3] = j[3] + ((j[2] << 8) | (j[2] >>> 24)) + j[1];
            d[4] = j[4] + ((j[3] << 16) | (j[3] >>> 16)) + ((j[2] << 16) | (j[2] >>> 16));
            d[5] = j[5] + ((j[4] << 8) | (j[4] >>> 24)) + j[3];
            d[6] = j[6] + ((j[5] << 16) | (j[5] >>> 16)) + ((j[4] << 16) | (j[4] >>> 16));
            d[7] = j[7] + ((j[6] << 8) | (j[6] >>> 24)) + j[5]
        }
    }
})();