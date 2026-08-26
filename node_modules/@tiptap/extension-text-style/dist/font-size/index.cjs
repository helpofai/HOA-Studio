Object.defineProperties(exports, {
	__esModule: { value: true },
	[Symbol.toStringTag]: { value: "Module" }
});
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
_tiptap_core.Mark.create({
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
//#region src/font-size/index.ts
var font_size_default = FontSize;
//#endregion
exports.FontSize = FontSize;
exports.default = font_size_default;

//# sourceMappingURL=index.cjs.map