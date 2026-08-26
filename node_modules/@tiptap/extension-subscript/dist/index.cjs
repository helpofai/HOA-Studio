Object.defineProperties(exports, {
	__esModule: { value: true },
	[Symbol.toStringTag]: { value: "Module" }
});
let _tiptap_core = require("@tiptap/core");
//#region src/subscript.ts
/**
* This extension allows you to create subscript text.
* @see https://www.tiptap.dev/api/marks/subscript
*/
const Subscript = _tiptap_core.Mark.create({
	name: "subscript",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	parseHTML() {
		return [{ tag: "sub" }, {
			style: "vertical-align",
			getAttrs(value) {
				if (value !== "sub") return false;
				return null;
			}
		}];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"sub",
			(0, _tiptap_core.mergeAttributes)(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	addCommands() {
		return {
			setSubscript: () => ({ commands }) => {
				return commands.setMark(this.name);
			},
			toggleSubscript: () => ({ commands }) => {
				return commands.toggleMark(this.name);
			},
			unsetSubscript: () => ({ commands }) => {
				return commands.unsetMark(this.name);
			}
		};
	},
	addKeyboardShortcuts() {
		return { "Mod-,": () => this.editor.commands.toggleSubscript() };
	}
});
//#endregion
//#region src/index.ts
var src_default = Subscript;
//#endregion
exports.Subscript = Subscript;
exports.default = src_default;

//# sourceMappingURL=index.cjs.map