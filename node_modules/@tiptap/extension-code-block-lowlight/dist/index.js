import { CodeBlock } from "@tiptap/extension-code-block";
import { findChildren } from "@tiptap/core";
import { Plugin, PluginKey } from "@tiptap/pm/state";
import { Decoration, DecorationSet } from "@tiptap/pm/view";
import highlight from "highlight.js/lib/core";
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
	return Boolean(highlight.getLanguage(aliasOrLanguage));
}
function getDecorations({ doc, name, lowlight, defaultLanguage }) {
	const decorations = [];
	findChildren(doc, (node) => node.type.name === name).forEach((block) => {
		var _lowlight$registered;
		let from = block.pos + 1;
		const language = block.node.attrs.language || defaultLanguage;
		const languages = lowlight.listLanguages();
		parseNodes(language && (languages.includes(language) || registered(language) || ((_lowlight$registered = lowlight.registered) === null || _lowlight$registered === void 0 ? void 0 : _lowlight$registered.call(lowlight, language))) ? getHighlightNodes(lowlight.highlight(language, block.node.textContent)) : getHighlightNodes(lowlight.highlightAuto(block.node.textContent))).forEach((node) => {
			const to = from + node.text.length;
			if (node.classes.length) {
				const decoration = Decoration.inline(from, to, { class: node.classes.join(" ") });
				decorations.push(decoration);
			}
			from = to;
		});
	});
	return DecorationSet.create(doc, decorations);
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
	const lowlightPlugin = new Plugin({
		key: new PluginKey("lowlight"),
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
				const oldNodes = findChildren(oldState.doc, (node) => node.type.name === name);
				const newNodes = findChildren(newState.doc, (node) => node.type.name === name);
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
const CodeBlockLowlight = CodeBlock.extend({
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
export { CodeBlockLowlight, src_default as default };

//# sourceMappingURL=index.js.map