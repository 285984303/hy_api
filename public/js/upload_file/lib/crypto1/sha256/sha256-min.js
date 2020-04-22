/*
 * Crypto-JS v1.1.0
 * http://code.google.com/p/crypto-js/
 * Copyright (c) 2009, Jeff Mott. All rights reserved.
 * http://code.google.com/p/crypto-js/wiki/License
 */
(function () {
    var b = Crypto.util;
    var a = [1116352408, 1899447441, 3049323471, 3921009573, 961987163, 1508970993, 2453635748, 2870763221, 3624381080, 310598401, 607225278, 1426881987, 1925078388, 2162078206, 2614888103, 3248222580, 3835390401, 4022224774, 264347078, 604807628, 770255983, 1249150122, 1555081692, 1996064986, 2554220882, 2821834349, 2952996808, 3210313671, 3336571891, 3584528711, 113926993, 338241895, 666307205, 773529912, 1294757372, 1396182291, 1695183700, 1986661051, 2177026350, 2456956037, 2730485921, 2820302411, 3259730800, 3345764771, 3516065817, 3600352804, 4094571909, 275423344, 430227734, 506948616, 659060556, 883997877, 958139571, 1322822218, 1537002063, 1747873779, 1955562222, 2024104815, 2227730452, 2361852424, 2428436474, 2756734187, 3204031479, 3329325298];
    var c = Crypto.SHA256 = function (f, d) {
        var e = b.wordsToBytes(c._sha256(f));
        return d && d.asBytes ? e : d && d.asString ? b.bytesToString(e) : b.bytesToHex(e)
    };
    c._sha256 = function (q) {
        var y = b.stringToWords(q), z = q.length * 8, r = [1779033703, 3144134277, 1013904242, 2773480762, 1359893119, 2600822924, 528734635, 1541459225], s = [], K, J, I, G, F, E, D, C, B, A, p, o;
        y[z >> 5] |= 128 << (24 - z % 32);
        y[((z + 64 >> 9) << 4) + 15] = z;
        for (var B = 0; B < y.length; B += 16) {
            K = r[0];
            J = r[1];
            I = r[2];
            G = r[3];
            F = r[4];
            E = r[5];
            D = r[6];
            C = r[7];
            for (var A = 0; A < 64; A++) {
                if (A < 16) {
                    s[A] = y[A + B]
                } else {
                    var n = s[A - 15], u = s[A - 2], M = ((n << 25) | (n >>> 7)) ^ ((n << 14) | (n >>> 18)) ^ (n >>> 3), L = ((u << 15) | (u >>> 17)) ^ ((u << 13) | (u >>> 19)) ^ (u >>> 10);
                    s[A] = M + (s[A - 7] >>> 0) + L + (s[A - 16] >>> 0)
                }
                var t = F & E ^ ~F & D, k = K & J ^ K & I ^ J & I, x = ((K << 30) | (K >>> 2)) ^ ((K << 19) | (K >>> 13)) ^ ((K << 10) | (K >>> 22)), v = ((F << 26) | (F >>> 6)) ^ ((F << 21) | (F >>> 11)) ^ ((F << 7) | (F >>> 25));
                p = (C >>> 0) + v + t + (a[A]) + (s[A] >>> 0);
                o = x + k;
                C = D;
                D = E;
                E = F;
                F = G + p;
                G = I;
                I = J;
                J = K;
                K = p + o
            }
            r[0] += K;
            r[1] += J;
            r[2] += I;
            r[3] += G;
            r[4] += F;
            r[5] += E;
            r[6] += D;
            r[7] += C
        }
        return r
    };
    c._blocksize = 16
})();