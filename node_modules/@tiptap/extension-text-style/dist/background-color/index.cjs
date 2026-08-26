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
//#region src/background-color/index.ts
var background_color_default = BackgroundColor;
//#endregion
exports.BackgroundColor = BackgroundColor;
exports.default = background_color_default;

//# sourceMappingURL=index.cjs.map