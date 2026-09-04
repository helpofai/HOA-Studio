Object.defineProperties(exports, {
	__esModule: { value: true },
	[Symbol.toStringTag]: { value: "Module" }
});
//#region \0rolldown/runtime.js
var __create = Object.create;
var __defProp = Object.defineProperty;
var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
var __getOwnPropNames = Object.getOwnPropertyNames;
var __getProtoOf = Object.getPrototypeOf;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __copyProps = (to, from, except, desc) => {
	if (from && typeof from === "object" || typeof from === "function") for (var keys = __getOwnPropNames(from), i = 0, n = keys.length, key; i < n; i++) {
		key = keys[i];
		if (!__hasOwnProp.call(to, key) && key !== except) __defProp(to, key, {
			get: ((k) => from[k]).bind(null, key),
			enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable
		});
	}
	return to;
};
var __toESM = (mod, isNodeMode, target) => (target = mod != null ? __create(__getProtoOf(mod)) : {}, __copyProps(isNodeMode || !mod || !mod.__esModule || !__hasOwnProp.call(mod, "default") ? __defProp(target, "default", {
	value: mod,
	enumerable: true
}) : target, mod));
//#endregion
let _tiptap_extension_code_block = require("@tiptap/extension-code-block");
let _tiptap_core = require("@tiptap/core");
let _tiptap_pm_state = require("@tiptap/pm/state");
let _tiptap_pm_view = require("@tiptap/pm/view");
let highlight_js_lib_core = require("highlight.js/lib/core");
highlight_js_lib_core = __toESM(highlight_js_lib_core, 1);
//#region src/lowlight-plugin.ts
function parseNodes(nodes, className = []) {
	return nodes.flatMap((node) => {
		const classes = [...className, ...node.properties ? node.properties.className : []];
		if (node.children) return parseNodes(node.children, classes);
		return {
			text: node.value,
			classes
		};
	});
}
function getHighlightNodes(result) {
	return result.value || result.children || [];
}
function registered(aliasOrLanguage) {
	return Boolean(highlight_js_lib_core.default.getLanguage(aliasOrLanguage));
}
function getDecorations({ doc, name, lowlight, defaultLanguage }) {
	const decorations = [];
	(0, _tiptap_core.findChildren)(doc, (node) => node.type.name === name).forEach((block) => {
		var _lowlight$registered;
		let from = block.pos + 1;
		const language = block.node.attrs.language || defaultLanguage;
		const languages = lowlight.listLanguages();
		parseNodes(language && (languages.includes(language) || registered(language) || ((_lowlight$registered = lowlight.registered) === null || _lowlight$registered === void 0 ? void 0 : _lowlight$registered.call(lowlight, language))) ? getHighlightNodes(lowlight.highlight(language, block.node.textContent)) : getHighlightNodes(lowlight.highlightAuto(block.node.textContent))).forEach((node) => {
			const to = from + node.text.length;
			if (node.classes.length) {
				const decoration = _tiptap_pm_view.Decoration.inline(from, to, { class: node.classes.join(" ") });
				decorations.push(decoration);
			}
			from = to;
		});
	});
	return _tiptap_pm_view.DecorationSet.create(doc, decorations);
}
function isFunction(param) {
	return typeof param === "function";
}
function LowlightPlugin({ name, lowlight, defaultLanguage }) {
	if (![
		"highlight",
		"highlightAuto",
		"listLanguages"
	].every((api) => isFunction(lowlight[api]))) throw Error("You should provide an instance of lowlight to use the code-block-lowlight extension");
	const lowlightPlugin = new _tiptap_pm_state.Plugin({
		key: new _tiptap_pm_state.PluginKey("lowlight"),
		state: {
			init: (_, { doc }) => getDecorations({
				doc,
				name,
				lowlight,
				defaultLanguage
			}),
			apply: (transaction, decorationSet, oldState, newState) => {
				const oldNodeName = oldState.selection.$head.parent.type.name;
				const newNodeName = newState.selection.$head.parent.type.name;
				const oldNodes = (0, _tiptap_core.findChildren)(oldState.doc, (node) => node.type.name === name);
				const newNodes = (0, _tiptap_core.findChildren)(newState.doc, (node) => node.type.name === name);
				if (transaction.docChanged && ([oldNodeName, newNodeName].includes(name) || newNodes.length !== oldNodes.length || transaction.steps.some((step) => {
					return step.from !== void 0 && step.to !== void 0 && oldNodes.some((node) => {
						return node.pos >= step.from && node.pos + node.node.nodeSize <= step.to;
					});
				}))) return getDecorations({
					doc: transaction.doc,
					name,
					lowlight,
					defaultLanguage
				});
				return decorationSet.map(transaction.mapping, transaction.doc);
			}
		},
		props: { decorations(state) {
			return lowlightPlugin.getState(state);
		} }
	});
	return lowlightPlugin;
}
//#endregion
//#region src/code-block-lowlight.ts
/**
* This extension allows you to highlight code blocks with lowlight.
* @see https://tiptap.dev/api/nodes/code-block-lowlight
*/
const CodeBlockLowlight = _tiptap_extension_code_block.CodeBlock.extend({
	addOptions() {
		var _this$parent;
		return {
			...(_this$parent = this.parent) === null || _this$parent === void 0 ? void 0 : _this$parent.call(this),
			lowlight: {},
			languageClassPrefix: "language-",
			exitOnTripleEnter: true,
			exitOnArrowDown: true,
			exitOnArrowUp: true,
			defaultLanguage: null,
			enableTabIndentation: false,
			tabSize: 4,
			HTMLAttributes: {}
		};
	},
	addProseMirrorPlugins() {
		var _this$parent2;
		return [...((_this$parent2 = this.parent) === null || _this$parent2 === void 0 ? void 0 : _this$parent2.call(this)) || [], LowlightPlugin({
			name: this.name,
			lowlight: this.options.lowlight,
			defaultLanguage: this.options.defaultLanguage
		})];
	}
});
//#endregion
//#region src/index.ts
var src_default = CodeBlockLowlight;
//#endregion
exports.CodeBlockLowlight = CodeBlockLowlight;
exports.default = src_default;

//# sourceMappingURL=index.cjs.map