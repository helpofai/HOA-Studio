import { Mark, mergeAttributes } from "@tiptap/core";
//#region src/subscript.ts
/**
* This extension allows you to create subscript text.
* @see https://www.tiptap.dev/api/marks/subscript
*/
const Subscript = Mark.create({
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
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
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
export { Subscript, src_default as default };

//# sourceMappingURL=index.js.map