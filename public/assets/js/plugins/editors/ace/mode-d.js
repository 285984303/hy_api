define("ace/mode/doc_comment_highlight_rules", ["require", "exports", "module", "ace/lib/oop", "ace/mode/text_highlight_rules"], function (e, t, n) {
    "use strict";
    var r = e("../lib/oop"), i = e("./text_highlight_rules").TextHighlightRules, s = function () {
        this.$rules = {
            start: [{
                token: "comment.doc.tag",
                regex: "@[\\w\\d_]+"
            }, s.getTagRule(), {defaultToken: "comment.doc", caseInsensitive: !0}]
        }
    };
    r.inherits(s, i), s.getTagRule = function (e) {
        return {token: "comment.doc.tag.storage.type", regex: "\\b(?:TODO|FIXME|XXX|HACK)\\b"}
    }, s.getStartRule = function (e) {
        return {token: "comment.doc", regex: "\\/\\*(?=\\*)", next: e}
    }, s.getEndRule = function (e) {
        return {token: "comment.doc", regex: "\\*\\/", next: e}
    }, t.DocCommentHighlightRules = s
}), define("ace/mode/d_highlight_rules", ["require", "exports", "module", "ace/lib/oop", "ace/mode/doc_comment_highlight_rules", "ace/mode/text_highlight_rules"], function (e, t, n) {
    "use strict";
    var r = e("../lib/oop"), i = e("./doc_comment_highlight_rules").DocCommentHighlightRules, s = e("./text_highlight_rules").TextHighlightRules, o = function () {
        var e = "this|super|import|module|body|mixin|__traits|invariant|alias|asm|delete|typeof|typeid|sizeof|cast|new|in|is|typedef|__vector|__parameters", t = "break|case|continue|default|do|else|for|foreach|foreach_reverse|goto|if|return|switch|while|catch|try|throw|finally|version|assert|unittest|with", n = "auto|bool|char|dchar|wchar|byte|ubyte|float|double|real|cfloat|creal|cdouble|cent|ifloat|ireal|idouble|int|long|short|void|uint|ulong|ushort|ucent|function|delegate|string|wstring|dstring|size_t|ptrdiff_t|hash_t|Object", r = "abstract|align|debug|deprecated|export|extern|const|final|in|inout|out|ref|immutable|lazy|nothrow|override|package|pragma|private|protected|public|pure|scope|shared|__gshared|synchronized|static|volatile", s = "class|struct|union|template|interface|enum|macro", o = {
            token: "constant.language.escape",
            regex: "\\\\(?:(?:x[0-9A-F]{2})|(?:[0-7]{1,3})|(?:['\"\\?0abfnrtv\\\\])|(?:u[0-9a-fA-F]{4})|(?:U[0-9a-fA-F]{8}))"
        }, u = "null|true|false|__DATE__|__EOF__|__TIME__|__TIMESTAMP__|__VENDOR__|__VERSION__|__FILE__|__MODULE__|__LINE__|__FUNCTION__|__PRETTY_FUNCTION__", a = "/|/\\=|&|&\\=|&&|\\|\\|\\=|\\|\\||\\-|\\-\\=|\\-\\-|\\+|\\+\\=|\\+\\+|\\<|\\<\\=|\\<\\<|\\<\\<\\=|\\<\\>|\\<\\>\\=|\\>|\\>\\=|\\>\\>\\=|\\>\\>\\>\\=|\\>\\>|\\>\\>\\>|\\!|\\!\\=|\\!\\<\\>|\\!\\<\\>\\=|\\!\\<|\\!\\<\\=|\\!\\>|\\!\\>\\=|\\?|\\$|\\=|\\=\\=|\\*|\\*\\=|%|%\\=|\\^|\\^\\=|\\^\\^|\\^\\^\\=|~|~\\=|\\=\\>|#", f = this.$keywords = this.createKeywordMapper({
            "keyword.modifier": r,
            "keyword.control": t,
            "keyword.type": n,
            keyword: e,
            "keyword.storage": s,
            punctation: "\\.|\\,|;|\\.\\.|\\.\\.\\.",
            "keyword.operator": a,
            "constant.language": u
        }, "identifier"), l = "[a-zA-Z_\u00a1-\uffff][a-zA-Z\\d_\u00a1-\uffff]*\\b";
        this.$rules = {
            start: [{token: "comment", regex: "\\/\\/.*$"}, i.getStartRule("doc-start"), {
                token: "comment",
                regex: "\\/\\*",
                next: "star-comment"
            }, {token: "comment.shebang", regex: "^s*#!.*"}, {
                token: "comment",
                regex: "\\/\\+",
                next: "plus-comment"
            }, {
                onMatch: function (e, t, n) {
                    return n.unshift(this.next, e.substr(2)), "string"
                }, regex: 'q"(?:[\\[\\(\\{\\<]+)', next: "operator-heredoc-string"
            }, {
                onMatch: function (e, t, n) {
                    return n.unshift(this.next, e.substr(2)), "string"
                }, regex: 'q"(?:[a-zA-Z_]+)$', next: "identifier-heredoc-string"
            }, {token: "string", regex: '[xr]?"', next: "quote-string"}, {
                token: "string",
                regex: "[xr]?`",
                next: "backtick-string"
            }, {
                token: "string",
                regex: "[xr]?['](?:(?:\\\\.)|(?:[^'\\\\]))*?['][cdw]?"
            }, {
                token: ["keyword", "text", "paren.lparen"],
                regex: /(asm)(\s*)({)/,
                next: "d-asm"
            }, {
                token: ["keyword", "text", "paren.lparen", "constant.language"],
                regex: "(__traits)(\\s*)(\\()(" + l + ")"
            }, {
                token: ["keyword", "text", "variable.module"],
                regex: "(import|module)(\\s+)((?:" + l + "\\.?)*)"
            }, {
                token: ["keyword.storage", "text", "entity.name.type"],
                regex: "(" + s + ")(\\s*)(" + l + ")"
            }, {
                token: ["keyword", "text", "variable.storage", "text"],
                regex: "(alias|typedef)(\\s*)(" + l + ")(\\s*)"
            }, {token: "constant.numeric", regex: "0[xX][0-9a-fA-F_]+(l|ul|u|f|F|L|U|UL)?\\b"}, {
                token: "constant.numeric",
                regex: "[+-]?\\d[\\d_]*(?:(?:\\.[\\d_]*)?(?:[eE][+-]?[\\d_]+)?)?(l|ul|u|f|F|L|U|UL)?\\b"
            }, {token: "entity.other.attribute-name", regex: "@" + l}, {
                token: f,
                regex: "[a-zA-Z_][a-zA-Z0-9_]*\\b"
            }, {token: "keyword.operator", regex: a}, {
                token: "punctuation.operator",
                regex: "\\?|\\:|\\,|\\;|\\.|\\:"
            }, {token: "paren.lparen", regex: "[[({]"}, {token: "paren.rparen", regex: "[\\])}]"}, {
                token: "text",
                regex: "\\s+"
            }],
            "star-comment": [{token: "comment", regex: "\\*\\/", next: "start"}, {defaultToken: "comment"}],
            "plus-comment": [{token: "comment", regex: "\\+\\/", next: "start"}, {defaultToken: "comment"}],
            "quote-string": [o, {token: "string", regex: '"[cdw]?', next: "start"}, {defaultToken: "string"}],
            "backtick-string": [o, {token: "string", regex: "`[cdw]?", next: "start"}, {defaultToken: "string"}],
            "operator-heredoc-string": [{
                onMatch: function (e, t, n) {
                    e = e.substring(e.length - 2, e.length - 1);
                    var r = {">": "<", "]": "[", ")": "(", "}": "{"};
                    return Object.keys(r).indexOf(e) != -1 && (e = r[e]), e != n[1] ? "string" : (n.shift(), n.shift(), "string")
                }, regex: '(?:[\\]\\)}>]+)"', next: "start"
            }, {token: "string", regex: "[^\\]\\)}>]+"}],
            "identifier-heredoc-string": [{
                onMatch: function (e, t, n) {
                    return e = e.substring(0, e.length - 1), e != n[1] ? "string" : (n.shift(), n.shift(), "string")
                }, regex: '^(?:[A-Za-z_][a-zA-Z0-9]+)"', next: "start"
            }, {token: "string", regex: "[^\\]\\)}>]+"}],
            "d-asm": [{token: "paren.rparen", regex: "\\}", next: "start"}, {
                token: "keyword.instruction",
                regex: "[a-zA-Z]+",
                next: "d-asm-instruction"
            }, {token: "text", regex: "\\s+"}],
            "d-asm-instruction": [{
                token: "constant.language",
                regex: /AL|AH|AX|EAX|BL|BH|BX|EBX|CL|CH|CX|ECX|DL|DH|DX|EDX|BP|EBP|SP|ESP|DI|EDI|SI|ESI/i
            }, {token: "identifier", regex: "[a-zA-Z]+"}, {token: "string", regex: '".*"'}, {
                token: "comment",
                regex: "//.*$"
            }, {token: "constant.numeric", regex: "[0-9.xA-F]+"}, {
                token: "punctuation.operator",
                regex: "\\,"
            }, {token: "punctuation.operator", regex: ";", next: "d-asm"}, {token: "text", regex: "\\s+"}]
        }, this.embedRules(i, "doc-", [i.getEndRule("start")])
    };
    o.metaData = {
        comment: "D language",
        fileTypes: ["d", "di"],
        firstLineMatch: "^#!.*\\b[glr]?dmd\\b.",
        foldingStartMarker: "(?x)/\\*\\*(?!\\*)|^(?![^{]*?//|[^{]*?/\\*(?!.*?\\*/.*?\\{)).*?\\{\\s*($|//|/\\*(?!.*?\\*/.*\\S))",
        foldingStopMarker: "(?<!\\*)\\*\\*/|^\\s*\\}",
        keyEquivalent: "^~D",
        name: "D",
        scopeName: "source.d"
    }, r.inherits(o, s), t.DHighlightRules = o
}), define("ace/mode/folding/cstyle", ["require", "exports", "module", "ace/lib/oop", "ace/range", "ace/mode/folding/fold_mode"], function (e, t, n) {
    "use strict";
    var r = e("../../lib/oop"), i = e("../../range").Range, s = e("./fold_mode").FoldMode, o = t.FoldMode = function (e) {
        e && (this.foldingStartMarker = new RegExp(this.foldingStartMarker.source.replace(/\|[^|]*?$/, "|" + e.start)), this.foldingStopMarker = new RegExp(this.foldingStopMarker.source.replace(/\|[^|]*?$/, "|" + e.end)))
    };
    r.inherits(o, s), function () {
        this.foldingStartMarker = /(\{|\[)[^\}\]]*$|^\s*(\/\*)/, this.foldingStopMarker = /^[^\[\{]*(\}|\])|^[\s\*]*(\*\/)/, this.getFoldWidgetRange = function (e, t, n, r) {
            var i = e.getLine(n), s = i.match(this.foldingStartMarker);
            if (s) {
                var o = s.index;
                if (s[1])return this.openingBracketBlock(e, s[1], n, o);
                var u = e.getCommentFoldRange(n, o + s[0].length, 1);
                return u && !u.isMultiLine() && (r ? u = this.getSectionRange(e, n) : t != "all" && (u = null)), u
            }
            if (t === "markbegin")return;
            var s = i.match(this.foldingStopMarker);
            if (s) {
                var o = s.index + s[0].length;
                return s[1] ? this.closingBracketBlock(e, s[1], n, o) : e.getCommentFoldRange(n, o, -1)
            }
        }, this.getSectionRange = function (e, t) {
            var n = e.getLine(t), r = n.search(/\S/), s = t, o = n.length;
            t += 1;
            var u = t, a = e.getLength();
            while (++t < a) {
                n = e.getLine(t);
                var f = n.search(/\S/);
                if (f === -1)continue;
                if (r > f)break;
                var l = this.getFoldWidgetRange(e, "all", t);
                if (l) {
                    if (l.start.row <= s)break;
                    if (l.isMultiLine())t = l.end.row; else if (r == f)break
                }
                u = t
            }
            return new i(s, o, u, e.getLine(u).length)
        }
    }.call(o.prototype)
}), define("ace/mode/d", ["require", "exports", "module", "ace/lib/oop", "ace/mode/text", "ace/mode/d_highlight_rules", "ace/mode/folding/cstyle"], function (e, t, n) {
    "use strict";
    var r = e("../lib/oop"), i = e("./text").Mode, s = e("./d_highlight_rules").DHighlightRules, o = e("./folding/cstyle").FoldMode, u = function () {
        this.HighlightRules = s, this.foldingRules = new o
    };
    r.inherits(u, i), function () {
        this.lineCommentStart = "//", this.blockComment = {start: "/*", end: "*/"}, this.$id = "ace/mode/d"
    }.call(u.prototype), t.Mode = u
})