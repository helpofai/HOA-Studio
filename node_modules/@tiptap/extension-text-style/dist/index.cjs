Object.defineProperty(exports, Symbol.toStringTag, { value: "Module" });
let _tiptap_core = require("@tiptap/core");
//#region src/text-style/index.ts
const MAX_FIND_CHILD_SPAN_DEPTH = 20;
/**
* Returns all next child spans, either direct children or nested deeper
* but won't traverse deeper into child spans found, will only go MAX_FIND_CHILD_SPAN_DEPTH levels deep (default: 20)
*/
const findChildSpans = (element, depth = 0) => {
	const childSpans = [];
	if (!element.children.length || depth > MAX_FIND_CHILD_SPAN_DEPTH) return childSpans;
	Array.from(element.children).forEach((child) => {
		if (child.tagName === "SPAN") childSpans.push(child);
		else if (child.children.length) childSpans.push(...findChildSpans(child, depth + 1));
	});
	return childSpans;
};
const mergeNestedSpanStyles = (element) => {
	if (!element.children.length) return;
	const childSpans = findChildSpans(element);
	if (!childSpans) return;
	childSpans.forEach((childSpan) => {
		var _childSpan$parentElem;
		const childStyle = childSpan.getAttribute("style");
		const closestParentSpanStyleOfChild = (_childSpan$parentElem = childSpan.parentElement) === null || _childSpan$parentElem === void 0 || (_childSpan$parentElem = _childSpan$parentElem.closest("span")) === null || _childSpan$parentElem === void 0 ? void 0 : _childSpan$parentElem.getAttribute("style");
		childSpan.setAttribute("style", `${closestParentSpanStyleOfChild};${childStyle}`);
	});
};
/**
* This extension allows you to create text styles. It is required by default
* for the `text-color` and `font-family` extensions.
* @see https://www.tiptap.dev/api/marks/text-style
*/
const TextStyle = _tiptap_core.Mark.create({
	name: "textStyle",
	priority: 101,
	addOptions() {
		return {
			HTMLAttributes: {},
			mergeNestedSpanStyles: true
		};
	},
	parseHTML() {
		return [{
			tag: "span",
			consuming: false,
			getAttrs: (element) => {
				if (!element.hasAttribute("style")) return false;
				if (this.options.mergeNestedSpanStyles) mergeNestedSpanStyles(element);
				return {};
			}
		}];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"span",
			(0, _tiptap_core.mergeAttributes)(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	addCommands() {
		return {
			toggleTextStyle: (attributes) => ({ commands }) => {
				return commands.toggleMark(this.name, attributes);
			},
			removeEmptyTextStyle: () => ({ tr }) => {
				const { selection } = tr;
				tr.doc.nodesBetween(selection.from, selection.to, (node, pos) => {
					if (!node.isInline) return true;
					if (!node.marks.filter((mark) => mark.type === this.type).some((mark) => Object.values(mark.attrs).some((value) => !!value))) tr.removeMark(pos, pos + node.nodeSize, this.type);
				});
				return true;
			}
		};
	}
});
//#endregion
//#region src/background-color/background-color.ts
/**
* This extension allows you to color your text.
* @see https://tiptap.dev/api/extensions/background-color
*/
const BackgroundColor = _tiptap_core.Extension.create({
	name: "backgroundColor",
	addOptions() {
		return { types: ["textStyle"] };
	},
	addGlobalAttributes() {
		return [{
			types: this.options.types,
			attributes: { backgroundColor: {
				default: null,
				parseHTML: (element) => {
					var _getStyleProperty;
					const value = (_getStyleProperty = (0, _tiptap_core.getStyleProperty)(element, "background-color")) !== null && _getStyleProperty !== void 0 ? _getStyleProperty : element.style.backgroundColor;
					return value === null || value === void 0 ? void 0 : value.replace(/['"]+/g, "");
				},
				renderHTML: (attributes) => {
					if (!attributes.backgroundColor) return {};
					return { style: `background-color: ${attributes.backgroundColor}` };
				}
			} }
		}];
	},
	addCommands() {
		return {
			setBackgroundColor: (backgroundColor) => ({ chain }) => {
				return chain().setMark("textStyle", { backgroundColor }).run();
			},
			unsetBackgroundColor: () => ({ chain }) => {
				return chain().setMark("textStyle", { backgroundColor: null }).removeEmptyTextStyle().run();
			}
		};
	}
});
//#endregion
//#region src/color/color.ts
/**
* This extension allows you to color your text.
* @see https://tiptap.dev/api/extensions/color
*/
const Color = _tiptap_core.Extension.create({
	name: "color",
	addOptions() {
		return { types: ["textStyle"] };
	},
	addGlobalAttributes() {
		return [{
			types: this.options.types,
			attributes: { color: {
				default: null,
				parseHTML: (element) => {
					var _getStyleProperty;
					const value = (_getStyleProperty = (0, _tiptap_core.getStyleProperty)(element, "color")) !== null && _getStyleProperty !== void 0 ? _getStyleProperty : element.style.color;
					return value === null || value === void 0 ? void 0 : value.replace(/['"]+/g, "");
				},
				renderHTML: (attributes) => {
					if (!attributes.color) return {};
					return { style: `color: ${attributes.color}` };
				}
			} }
		}];
	},
	addCommands() {
		return {
			setColor: (color) => ({ chain }) => {
				return chain().setMark("textStyle", { color }).run();
			},
			unsetColor: () => ({ chain }) => {
				return chain().setMark("textStyle", { color: null }).removeEmptyTextStyle().run();
			}
		};
	}
});
//#endregion
//#region src/font-family/font-family.ts
/**
* This extension allows you to set a font family for text.
* @see https://www.tiptap.dev/api/extensions/font-family
*/
const FontFamily = _tiptap_core.Extension.create({
	name: "fontFamily",
	addOptions() {
		return { types: ["textStyle"] };
	},
	addGlobalAttributes() {
		return [{
			types: this.options.types,
			attributes: { fontFamily: {
				default: null,
				parseHTML: (element) => {
					var _getStyleProperty;
					return (_getStyleProperty = (0, _tiptap_core.getStyleProperty)(element, "font-family")) !== null && _getStyleProperty !== void 0 ? _getStyleProperty : element.style.fontFamily;
				},
				renderHTML: (attributes) => {
					if (!attributes.fontFamily) return {};
					return { style: `font-family: ${attributes.fontFamily}` };
				}
			} }
		}];
	},
	addCommands() {
		return {
			setFontFamily: (fontFamily) => ({ chain }) => {
				return chain().setMark("textStyle", { fontFamily }).run();
			},
			unsetFontFamily: () => ({ chain }) => {
				return chain().setMark("textStyle", { fontFamily: null }).removeEmptyTextStyle().run();
			}
		};
	}
});
//#endregion
//#region src/font-size/font-size.ts
/**
* This extension allows you to set a font size for text.
* @see https://www.tiptap.dev/api/extensions/font-size
*/
const FontSize = _tiptap_core.Extension.create({
	name: "fontSize",
	addOptions() {
		return { types: ["textStyle"] };
	},
	addGlobalAttributes() {
		return [{
			types: this.options.types,
			attributes: { fontSize: {
				default: null,
				parseHTML: (element) => {
					var _getStyleProperty;
					return (_getStyleProperty = (0, _tiptap_core.getStyleProperty)(element, "font-size")) !== null && _getStyleProperty !== void 0 ? _getStyleProperty : element.style.fontSize;
				},
				renderHTML: (attributes) => {
					if (!attributes.fontSize) return {};
					return { style: `font-size: ${attributes.fontSize}` };
				}
			} }
		}];
	},
	addCommands() {
		return {
			setFontSize: (fontSize) => ({ chain }) => {
				return chain().setMark("textStyle", { fontSize }).run();
			},
			unsetFontSize: () => ({ chain }) => {
				return chain().setMark("textStyle", { fontSize: null }).removeEmptyTextStyle().run();
			}
		};
	}
});
//#endregion
//#region src/line-height/line-height.ts
/**
* This extension allows you to set the line-height for text.
* @see https://www.tiptap.dev/api/extensions/line-height
*/
const LineHeight = _tiptap_core.Extension.create({
	name: "lineHeight",
	addOptions() {
		return { types: ["textStyle"] };
	},
	addGlobalAttributes() {
		return [{
			types: this.options.types,
			attributes: { lineHeight: {
				default: null,
				parseHTML: (element) => {
					var _getStyleProperty;
					return (_getStyleProperty = (0, _tiptap_core.getStyleProperty)(element, "line-height")) !== null && _getStyleProperty !== void 0 ? _getStyleProperty : element.style.lineHeight;
				},
				renderHTML: (attributes) => {
					if (!attributes.lineHeight) return {};
					return { style: `line-height: ${attributes.lineHeight}` };
				}
			} }
		}];
	},
	addCommands() {
		return {
			setLineHeight: (lineHeight) => ({ chain }) => {
				return chain().setMark("textStyle", { lineHeight }).run();
			},
			unsetLineHeight: () => ({ chain }) => {
				return chain().setMark("textStyle", { lineHeight: null }).removeEmptyTextStyle().run();
			}
		};
	}
});
//#endregion
//#region src/text-style-kit/index.ts
/**
* The table kit is a collection of table editor extensions.
*
* It’s a good starting point for building your own table in Tiptap.
*/
const TextStyleKit = _tiptap_core.Extension.create({
	name: "textStyleKit",
	addExtensions() {
		const extensions = [];
		if (this.options.backgroundColor !== false) extensions.push(BackgroundColor.configure(this.options.backgroundColor));
		if (this.options.color !== false) extensions.push(Color.configure(this.options.color));
		if (this.options.fontFamily !== false) extensions.push(FontFamily.configure(this.options.fontFamily));
		if (this.options.fontSize !== false) extensions.push(FontSize.configure(this.options.fontSize));
		if (this.options.lineHeight !== false) extensions.push(LineHeight.configure(this.options.lineHeight));
		if (this.options.textStyle !== false) extensions.push(TextStyle.configure(this.options.textStyle));
		return extensions;
	}
});
//#endregion
exports.BackgroundColor = BackgroundColor;
exports.Color = Color;
exports.FontFamily = FontFamily;
exports.FontSize = FontSize;
exports.LineHeight = LineHeight;
exports.TextStyle = TextStyle;
exports.TextStyleKit = TextStyleKit;

//# sourceMappingURL=index.cjs.map