import { Mark, mergeAttributes } from "@tiptap/core";
//#region src/superscript.ts
/**
* This extension allows you to create superscript text.
* @see https://www.tiptap.dev/api/marks/superscript
*/
const Superscript = Mark.create({
	name: "superscript",
	addOptions() {
		return { HTMLAttributes: {} };
	},
	parseHTML() {
		return [{ tag: "sup" }, {
			style: "vertical-align",
			getAttrs(value) {
				if (value !== "super") return false;
				return null;
			}
		}];
	},
	renderHTML({ HTMLAttributes }) {
		return [
			"sup",
			mergeAttributes(this.options.HTMLAttributes, HTMLAttributes),
			0
		];
	},
	addCommands() {
		return {
			setSuperscript: () => ({ commands }) => {
				return commands.setMark(this.name);
			},
			toggleSuperscript: () => ({ commands }) => {
				return commands.toggleMark(this.name);
			},
			unsetSuperscript: () => ({ commands }) => {
				return commands.unsetMark(this.name);
			}
		};
	},
	addKeyboardShortcuts() {
		return { "Mod-.": () => this.editor.commands.toggleSuperscript() };
	}
});
//#endregion
//#region src/index.ts
var src_default = Superscript;
//#endregion
export { Superscript, src_default as default };

//# sourceMappingURL=index.js.map