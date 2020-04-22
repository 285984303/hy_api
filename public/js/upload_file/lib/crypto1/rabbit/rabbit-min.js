/*
 * Crypto-JS v1.1.0
 * http://code.google.com/p/crypto-js/
 * Copyright (c) 2009, Jeff Mott. All rights reserved.
 * http://code.google.com/p/crypto-js/wiki/License
 */
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